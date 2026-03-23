<?php
/**
 * SAP Business One Service Layer API Client (Lite)
 * 
 * Provides HTTP communication with SAP B1 Service Layer REST API.
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SAPWC_Lite_API_Client
{
    private $base_url;
    private $session_id;
    private $route_id;
    private $conn = ['ssl' => false];
    private $last_login_response;
    private $company_db;
    private $is_logged_in = false;

    /** @var array Stored credentials for 401 retry */
    private $credentials = [];

    /** @var bool Prevent infinite retry loops */
    private $is_retrying = false;

    /** @var array Shared instances */
    private static $instances = [];

    public function __construct($url)
    {
        $this->base_url = untrailingslashit($url) . '/b1s/v1';
    }

    /**
     * Get a shared client instance
     */
    public static function get_instance($url = '')
    {
        if (empty($url)) {
            $conn = sapwc_lite_get_connection();
            if (!$conn) {
                return new self('');
            }
            $url = $conn['url'];
        }

        $key = md5($url);
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($url);
        }
        return self::$instances[$key];
    }

    /**
     * Check if logged in
     */
    public function is_logged_in()
    {
        return $this->is_logged_in && !empty($this->session_id);
    }

    public function get_cookie_header()
    {
        return 'B1SESSION=' . $this->session_id . '; ' . $this->route_id;
    }

    /**
     * Login to SAP Service Layer
     */
    public function login($user, $pass, $db, $ssl = false)
    {
        $this->credentials = compact('user', 'pass', 'db', 'ssl');

        if ($this->is_logged_in && $this->company_db === $db) {
            return ['success' => true, 'reused' => true];
        }

        $this->conn['ssl'] = $ssl;
        $endpoint = $this->base_url . '/Login';

        $body = wp_json_encode([
            'UserName'  => $user,
            'Password'  => $pass,
            'CompanyDB' => $db
        ]);

        $response = wp_remote_post($endpoint, [
            'headers'   => ['Content-Type' => 'application/json'],
            'body'      => $body,
            'timeout'   => 20,
            'sslverify' => $ssl
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 200 && isset($data['SessionId'])) {
            $this->session_id = $data['SessionId'];
            $this->last_login_response = $data;
            $this->company_db = $db;
            $this->is_logged_in = true;

            // Extract ROUTEID from cookies
            $cookies = wp_remote_retrieve_header($response, 'set-cookie');
            if (is_array($cookies)) {
                foreach ($cookies as $cookie) {
                    if (strpos($cookie, 'ROUTEID=') !== false) {
                        $parts = explode(';', $cookie);
                        foreach ($parts as $part) {
                            if (strpos($part, 'ROUTEID=') !== false) {
                                $this->route_id = trim($part);
                                break 2;
                            }
                        }
                    }
                }
            }

            return ['success' => true];
        }

        return ['success' => false, 'message' => $data['error']['message']['value'] ?? 'Invalid response'];
    }

    /**
     * Generic HTTP request
     */
    public function request($method, $relative_path, $body = null, $extra_args = [])
    {
        $url = $this->base_url . $relative_path;

        $args = array_merge([
            'method'    => strtoupper($method),
            'headers'   => [
                'Content-Type' => 'application/json',
                'Cookie'       => $this->get_cookie_header(),
                'Accept'       => 'application/json',
            ],
            'timeout'   => 30,
            'sslverify' => !empty($this->conn['ssl']),
        ], $extra_args);

        if ($body !== null) {
            $args['body'] = is_string($body) ? $body : wp_json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $raw_body = wp_remote_retrieve_body($response);

        // Auto-retry on 401
        if ($http_code === 401 && !$this->is_retrying && !empty($this->credentials)) {
            $this->is_retrying = true;
            $this->is_logged_in = false;
            $this->session_id = null;

            $relogin = $this->login(
                $this->credentials['user'],
                $this->credentials['pass'],
                $this->credentials['db'],
                $this->credentials['ssl'] ?? false
            );

            if ($relogin['success']) {
                $retry_result = $this->request($method, $relative_path, $body, $extra_args);
                $this->is_retrying = false;
                return $retry_result;
            }

            $this->is_retrying = false;
        }

        if ($http_code === 204) {
            return ['success' => true, 'http_code' => 204];
        }

        $decoded = json_decode($raw_body, true);
        if (is_array($decoded)) {
            $decoded['http_code'] = $http_code;
        }

        return $decoded ?? ['raw' => $raw_body, 'http_code' => $http_code];
    }

    /**
     * GET request
     */
    public function get($relative_path, $extra_args = [])
    {
        return $this->request('GET', $relative_path, null, $extra_args);
    }

    /**
     * POST request
     */
    public function post($relative_path, $body = null, $extra_args = [])
    {
        return $this->request('POST', $relative_path, $body, $extra_args);
    }

    /**
     * Logout
     */
    public function logout()
    {
        if ($this->session_id) {
            wp_remote_post($this->base_url . '/Logout', [
                'headers'   => ['Content-Type' => 'application/json', 'Cookie' => $this->get_cookie_header()],
                'timeout'   => 20,
                'sslverify' => !empty($this->conn['ssl'])
            ]);

            $this->is_logged_in = false;
            $this->session_id = null;
        }
    }

    /**
     * Get version info from login response
     */
    public function get_version_info()
    {
        if (isset($this->last_login_response['Version'])) {
            return [
                'ServiceLayerVersion' => $this->last_login_response['Version'],
                'CompanyDB'           => $this->company_db ?? '-',
            ];
        }
        return null;
    }

    /**
     * Get price lists from SAP
     */
    public function get_price_lists()
    {
        $response = $this->get('/PriceLists?$select=PriceListNo,PriceListName');
        if (!isset($response['value'])) {
            return [];
        }

        return array_map(function ($item) {
            return [
                'id'   => $item['PriceListNo'],
                'name' => $item['PriceListName']
            ];
        }, $response['value']);
    }

    /**
     * Get warehouses from SAP
     */
    public function get_warehouses()
    {
        $response = $this->get('/Warehouses?$select=WarehouseCode,WarehouseName');
        if (!isset($response['value'])) {
            return [];
        }

        return array_map(function ($item) {
            return [
                'code' => $item['WarehouseCode'],
                'name' => $item['WarehouseName']
            ];
        }, $response['value']);
    }
}
