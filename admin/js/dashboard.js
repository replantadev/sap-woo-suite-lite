/**
 * SAP Woo Suite Lite — Dashboard Charts
 *
 * Reads `sapwcLiteDash` global and renders:
 *   - Sync trend bar chart (success vs errors, 14d)
 *   - Stock distribution doughnut
 *
 * @since 1.2.0
 */

/* global sapwcLiteDash, Chart */

(function () {
    'use strict';

    if (typeof sapwcLiteDash === 'undefined' || typeof Chart === 'undefined') {
        return;
    }

    const D     = sapwcLiteDash;
    const root  = getComputedStyle(document.documentElement);
    const brand = root.getPropertyValue('--sapwc-lite-accent').trim() || '#41999f';
    const gray  = root.getPropertyValue('--sapwc-gray-200').trim() || '#e2e8f0';

    const BASE = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 600, easing: 'easeOutQuart' },
    };

    /* ── Sync Trend (stacked bar) ─────────────────────────────────────── */

    function buildSyncTrend() {
        const el = document.getElementById('sapwc-lite-chart-trend');
        if (!el || !D.syncTrend) return;

        new Chart(el, {
            type: 'bar',
            data: {
                labels: D.syncTrend.labels,
                datasets: [
                    {
                        label: 'OK',
                        data: D.syncTrend.success,
                        backgroundColor: '#22c55e',
                        borderRadius: 3,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Errores',
                        data: D.syncTrend.error,
                        backgroundColor: '#ef4444',
                        borderRadius: 3,
                        barPercentage: 0.6,
                    },
                ],
            },
            options: {
                ...BASE,
                plugins: {
                    legend: { display: true, position: 'top', labels: { boxWidth: 12, padding: 16, font: { size: 11 } } },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 12 },
                        bodyFont: { size: 11 },
                        cornerRadius: 6,
                        padding: 10,
                    },
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 10 }, maxRotation: 45 },
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: gray },
                        ticks: {
                            font: { size: 10 },
                            precision: 0,
                            callback: function (v) { return Number.isInteger(v) ? v : ''; },
                        },
                    },
                },
            },
        });
    }

    /* ── Stock Distribution (doughnut) ────────────────────────────────── */

    function buildStockDist() {
        const el = document.getElementById('sapwc-lite-chart-stock');
        if (!el || !D.stockDist) return;

        const total = D.stockDist.values.reduce(function (a, b) { return a + b; }, 0);

        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: D.stockDist.labels,
                datasets: [{
                    data: D.stockDist.values,
                    backgroundColor: ['#22c55e', '#ef4444', '#e2e8f0'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }],
            },
            options: {
                ...BASE,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16, boxWidth: 12, font: { size: 11 } },
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        cornerRadius: 6,
                        padding: 10,
                        callbacks: {
                            label: function (ctx) {
                                var pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            },
                        },
                    },
                },
            },
        });
    }

    /* ── Init ─────────────────────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {
        buildSyncTrend();
        buildStockDist();
    });
})();
