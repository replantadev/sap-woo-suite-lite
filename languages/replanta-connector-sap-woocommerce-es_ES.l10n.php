<?php
/**
 * Spanish (Spain) translations for Replanta Connector with SAP for WooCommerce.
 *
 * WordPress 6.5+ PHP translation file format (.l10n.php).
 *
 * @package SAPWC_Lite
 * @since   1.2.19
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'domain'       => 'replanta-connector-sap-woocommerce',
    'plural-forms' => 'nplurals=2; plural=(n != 1);',
    'messages'     => array(

        /* ──────────────────────────────────────
         * Dashboard  (class-dashboard-page.php)
         * ────────────────────────────────────── */
        'Dashboard'
            => 'Panel de Control',
        'Your inventory stays in sync with SAP Business One.'
            => 'Tu inventario se mantiene sincronizado con SAP Business One.',
        'Connected & syncing'
            => 'Conectado y sincronizando',
        'Setup needed'
            => 'Configuracion pendiente',
        'In stock'
            => 'En stock',
        'Out of stock'
            => 'Sin stock',
        'Unmanaged'
            => 'Sin gestionar',
        'Sync Health'
            => 'Estado del Sync',
        'Running smoothly'
            => 'Funcionando correctamente',
        'Almost ready'
            => 'Casi listo',
        'Complete setup to start'
            => 'Completa la configuracion',
        'Products Managed'
            => 'Productos Gestionados',
        'of %s published'
            => 'de %s publicados',
        'Last Sync'
            => 'Ultima sincronizacion',
        'Next: %s'
            => 'Siguiente: %s',
        'Schedule sync in Settings'
            => 'Programa el sync en Ajustes',
        'Events (7d)'
            => 'Eventos (7d)',
        'errors'
            => 'errores',
        'Orders synced'
            => 'Pedidos sincronizados',
        'Available in PRO'
            => 'Disponible en PRO',
        'Sync Activity - 14 days'
            => 'Actividad de Sync - 14 dias',
        'Stock Distribution'
            => 'Distribucion de Stock',
        'Current Configuration'
            => 'Configuracion Actual',
        'SAP Connection'
            => 'Conexion SAP',
        'Configured'
            => 'Configurada',
        'Not configured'
            => 'Sin configurar',
        'Stock sync'
            => 'Sync stock',
        'Active'
            => 'Activo',
        'Disabled'
            => 'Desactivado',
        'Price sync'
            => 'Sync precios',
        'SAP Price List'
            => 'Tarifa SAP',
        'Not selected'
            => 'Sin seleccionar',
        'Warehouses'
            => 'Almacenes',
        'SKU Readiness'
            => 'Compatibilidad SKU',
        '%1$d of %2$d products'
            => '%1$d de %2$d productos',
        'No products with SKU'
            => 'Sin productos con SKU',
        'Interval'
            => 'Intervalo',
        'Hourly'
            => 'Cada hora',
        'Twice daily'
            => '2 veces al dia',
        'Daily'
            => 'Diario',
        'Lite Version'
            => 'Version Lite',
        'Recent Activity'
            => 'Actividad Reciente',
        'View all'
            => 'Ver todo',
        'No activity recorded yet.'
            => 'Sin actividad registrada aun.',
        'Your entire store, connected to SAP'
            => 'Tu tienda completa, conectada a SAP',
        'SAP Woo Suite PRO syncs orders, customers and products with SAP Business One. Set it up once and let it run -- no manual data entry.'
            => 'SAP Woo Suite PRO sincroniza pedidos, clientes y productos con SAP Business One. Configuralo una vez y dejalo funcionar.',
        'Orders to SAP'
            => 'Pedidos a SAP',
        'Full product import'
            => 'Importacion de productos',
        'B2B customer sync'
            => 'Sync de clientes B2B',
        'TikTok Shop + Amazon'
            => 'TikTok Shop + Amazon',
        'Smart retry on failures'
            => 'Reintento inteligente',
        'Revenue dashboard'
            => 'Dashboard de facturacion',
        'Get SAP Woo Suite PRO'
            => 'Obtener SAP Woo Suite PRO',

        /* ──────────────────────────────────────
         * Settings  (class-settings-page.php)
         * ────────────────────────────────────── */
        'Connection'
            => 'Conexion',
        'Sync Settings'
            => 'Ajustes de Sync',
        'Sync your entire business with PRO'
            => 'Sincroniza todo tu negocio con PRO',
        'Orders, products, customers and multi-channel -- all connected to SAP.'
            => 'Pedidos, productos, clientes y multicanal -- todo conectado a SAP.',
        'Upgrade to PRO'
            => 'Obtener PRO',
        'Service Layer URL'
            => 'URL del Service Layer',
        'SAP Business One Service Layer URL (without /b1s/v1)'
            => 'URL del Service Layer de SAP Business One (sin /b1s/v1)',
        'Username'
            => 'Usuario',
        'Password'
            => 'Contrasena',
        'Company Database'
            => 'Base de datos',
        'SSL Verification'
            => 'Verificacion SSL',
        'Verify SSL certificate (recommended for production)'
            => 'Verificar certificado SSL (recomendado para produccion)',
        'Save Connection'
            => 'Guardar Conexion',
        'Test Connection'
            => 'Probar Conexion',
        'Testing...'
            => 'Probando...',
        'Connected!'
            => '¡Conectado!',
        'Connection failed'
            => 'Error de conexion',
        'Request failed'
            => 'Error en la peticion',
        'Syncing...'
            => 'Sincronizando...',
        'products updated'
            => 'productos actualizados',
        'Sync failed'
            => 'Fallo el sync',
        'Configure your SAP connection first'
            => 'Configura tu conexion SAP primero',
        'before setting up sync options.'
            => 'antes de configurar las opciones de sync.',
        'How sync works:'
            => 'Como funciona el sync:',
        'Products are matched by SKU (WooCommerce SKU = SAP ItemCode). Once configured, stock and prices sync on your chosen schedule -- set it and forget it.'
            => 'Los productos se emparejan por SKU (SKU de WooCommerce = ItemCode de SAP). Una vez configurado, stock y precios se sincronizan segun tu programacion.',
        'Sync Configuration'
            => 'Configuracion de Sync',
        'Price List'
            => 'Lista de Precios',
        '-- Select Price List --'
            => '-- Seleccionar Lista de Precios --',
        'Could not load price lists. Check your connection.'
            => 'No se pudieron cargar las listas de precios. Revisa tu conexion.',
        'Connect to SAP to load warehouses.'
            => 'Conecta con SAP para cargar almacenes.',
        'What to Sync'
            => 'Que sincronizar',
        'Sync Stock'
            => 'Sync Stock',
        'Keep WooCommerce stock in sync with SAP'
            => 'Mantener el stock de WooCommerce sincronizado con SAP',
        'Sync Prices'
            => 'Sync Precios',
        'Keep prices updated from your SAP price list'
            => 'Mantener precios actualizados desde tu tarifa SAP',
        'Sync Orders'
            => 'Sync Pedidos',
        'Send orders to SAP as Sales Orders'
            => 'Enviar pedidos a SAP como Ordenes de Venta',
        'Sync Customers'
            => 'Sync Clientes',
        'Create Business Partners in SAP from customers'
            => 'Crear Business Partners en SAP desde los clientes',
        'Import Products'
            => 'Importar Productos',
        'Import products from SAP with images and attributes'
            => 'Importar productos de SAP con imagenes y atributos',
        'Automatic Sync'
            => 'Sync Programado',
        'Sync Interval'
            => 'Intervalo de Sync',
        'Every Hour'
            => 'Cada hora',
        'Twice Daily'
            => '2 veces al dia',
        'Syncs run on this schedule. Set it and forget it.'
            => 'Los syncs se ejecutan segun esta programacion. Configuralo y olvidate.',
        'Save Sync Settings'
            => 'Guardar Ajustes de Sync',
        'Sync Now'
            => 'Sincronizar Ahora',
        'Want to sync more than stock and prices?'
            => '¿Quieres sincronizar mas que stock y precios?',
        'With PRO, orders, products, customers and more also sync with SAP. Set it up once, it just works.'
            => 'Con PRO, pedidos, productos, clientes y mas tambien se sincronizan con SAP. Configuralo una vez y funciona.',
        'Full Product Import'
            => 'Importacion de Productos',
        'Products created and updated from SAP'
            => 'Productos creados y actualizados desde SAP',
        'Order Sync'
            => 'Sync de Pedidos',
        'Every order flows to SAP as a Sales Order'
            => 'Cada pedido llega a SAP como Orden de Venta',
        'Customer Sync'
            => 'Sync de Clientes',
        'Business Partners created from WooCommerce customers'
            => 'Business Partners creados desde clientes de WooCommerce',
        'Field Mapping'
            => 'Mapeo de Campos',
        'Map any SAP field to WooCommerce attributes'
            => 'Mapea cualquier campo de SAP a atributos de WooCommerce',
        'Smart Retry'
            => 'Reintento Inteligente',
        'Failed syncs retry with intelligent backoff'
            => 'Los syncs fallidos se reintentan con backoff inteligente',
        'Multi-channel'
            => 'Multicanal',
        'TikTok Shop, Amazon, Miravia included'
            => 'TikTok Shop, Amazon, Miravia incluidos',
        'See All PRO Features'
            => 'Ver todas las Funciones PRO',
        'SAP Woo Suite Lite - Logs'
            => 'SAP Woo Suite Lite - Logs',
        'Date'
            => 'Fecha',
        'Action'
            => 'Accion',
        'Status'
            => 'Estado',
        'Message'
            => 'Mensaje',
        'No logs yet.'
            => 'Sin logs aun.',
        'Need advanced log management?'
            => '¿Necesitas gestion avanzada de logs?',
        'SAP Woo Suite PRO includes filtering by date and action, CSV export, log cleanup, and a visual analytics dashboard.'
            => 'SAP Woo Suite PRO incluye filtrado por fecha y accion, exportacion CSV, limpieza de logs y un dashboard de analiticas.',
        'See PRO Features'
            => 'Ver Funciones PRO',

        /* ──────────────────────────────────────
         * PRO Features  (class-pro-features.php)
         * ────────────────────────────────────── */
        'PRO Features'
            => 'Funciones PRO',
        'Products are imported from SAP with images, attributes, categories, and variations -- no manual data entry needed.'
            => 'Los productos se importan desde SAP con imagenes, atributos, categorias y variaciones -- sin introduccion manual de datos.',
        'Stock & prices only'
            => 'Solo stock y precios',
        'Order Sync to SAP'
            => 'Sync de Pedidos a SAP',
        'Every WooCommerce order flows to SAP as a Sales Order -- zero copy-paste, zero delays.'
            => 'Cada pedido de WooCommerce llega a SAP como Orden de Venta -- sin copiar-pegar, sin retrasos.',
        'Not available'
            => 'No disponible',
        'Business Partners are created and updated in SAP from WooCommerce customers -- including B2B addresses.'
            => 'Los Business Partners se crean y actualizan en SAP desde clientes de WooCommerce -- incluyendo direcciones B2B.',
        'Category Import'
            => 'Importacion de Categorias',
        'SAP item groups are imported as WooCommerce categories with full hierarchy.'
            => 'Los grupos de articulos de SAP se importan como categorias de WooCommerce con jerarquia completa.',
        'Map any SAP field (including UDFs) to WooCommerce attributes. Data flows on every sync cycle.'
            => 'Mapea cualquier campo de SAP (incluyendo UDFs) a atributos de WooCommerce. Los datos fluyen en cada ciclo de sync.',
        'REST API & Webhooks'
            => 'REST API y Webhooks',
        'Expose API endpoints for external integrations -- trigger workflows and connect third-party tools.'
            => 'Expone endpoints de API para integraciones externas -- activa flujos y conecta herramientas de terceros.',
        'Multi-channel Support'
            => 'Soporte Multicanal',
        'TikTok Shop, Amazon, and Miravia orders sync to SAP through SAP Woo Suite addons.'
            => 'Los pedidos de TikTok Shop, Amazon y Miravia se sincronizan con SAP mediante addons de SAP Woo Suite.',
        'Smart Retry System'
            => 'Sistema de Reintento Inteligente',
        'Failed orders retry with intelligent backoff -- no manual re-sending required.'
            => 'Los pedidos fallidos se reintentan con backoff inteligente -- sin reenvio manual.',
        'Multi-warehouse Support'
            => 'Soporte Multi-almacen',
        'Different warehouses per product with stock aggregation and warehouse-specific pricing.'
            => 'Diferentes almacenes por producto con agregacion de stock y precios por almacen.',
        'Global warehouse only'
            => 'Solo almacen global',
        'Analytics Dashboard'
            => 'Dashboard de Analiticas',
        'Revenue charts, top customers, channel distribution -- all generated from your sync data.'
            => 'Graficos de ingresos, mejores clientes, distribucion por canal -- todo generado desde tus datos de sync.',
        'Basic logs'
            => 'Logs basicos',
        'Connect Your Entire Store to SAP'
            => 'Conecta Tu Tienda Completa a SAP',
        "You're using the %s version -- stock and prices stay in sync. Upgrade to PRO to also sync orders, products, customers, and multi-channel sales."
            => "Estas usando la version %s -- stock y precios se mantienen sincronizados. Mejora a PRO para sincronizar tambien pedidos, productos, clientes y ventas multicanal.",
        'Ready for the full SAP connection?'
            => '¿Listo para la conexion completa con SAP?',
        'SAP Woo Suite PRO keeps your entire WooCommerce-SAP workflow in sync. Set it up once and let it run.'
            => 'SAP Woo Suite PRO mantiene todo tu flujo WooCommerce-SAP sincronizado. Configuralo una vez y dejalo funcionar.',
        'Read Documentation'
            => 'Leer Documentacion',

        /* ──────────────────────────────────────
         * Main Plugin File  (sap-woo-suite-lite.php)
         * ────────────────────────────────────── */
        'has been deactivated. You have the PRO version active - all your settings have been preserved!'
            => 'ha sido desactivado. Tienes la version PRO activa -- ¡todos tus ajustes se han conservado!',
        'SAP Woo Suite PRO is already active. The Lite version is not needed - your settings will work with PRO.'
            => 'SAP Woo Suite PRO ya esta activo. La version Lite no es necesaria -- tus ajustes funcionaran con PRO.',
        'Plugin Activation Error'
            => 'Error de Activacion del Plugin',
        'requires WooCommerce to be installed and activated.'
            => 'requiere WooCommerce instalado y activado.',
        'SAP Lite: Connected'
            => 'SAP Lite: Conectado',
        'SAP Lite: Not configured'
            => 'SAP Lite: Sin configurar',
        'Sync orders, products, customers and more with SAP'
            => 'Sincroniza pedidos, productos, clientes y mas con SAP',
        'Never'
            => 'Nunca',
    ),
);
