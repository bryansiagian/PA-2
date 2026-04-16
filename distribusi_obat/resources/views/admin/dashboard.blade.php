@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Dashboard Rekapitulasi Umum</h4>
            <div class="text-muted small">Pantau tren distribusi, stok inventaris, dan status logistik real-time.</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-indigo dropdown-toggle rounded-pill px-4 shadow-sm" data-bs-toggle="dropdown">
                    <i class="ph-file-arrow-down me-2"></i> Export Report
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item py-2" href="/admin/export/excel"><i class="ph-file-xls text-success me-2"></i> Export Excel (.xlsx)</a></li>
                    <li><a class="dropdown-item py-2" href="/admin/export/pdf"><i class="ph-file-pdf text-danger me-2"></i> Export PDF (.pdf)</a></li>
                </ul>
            </div>
            <button onclick="initDashboard()" class="btn btn-light btn-icon rounded-circle shadow-sm">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Quick stats boxes (Rekapitulasi Umum) -->
    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalUsers">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Total Customer</div>
                    </div>
                    <i class="ph-users-three ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-teal text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalProducts">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Katalog Produk</div>
                    </div>
                    <i class="ph-package ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-pink text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalOrders">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Jumlah Pembelian</div>
                    </div>
                    <i class="ph-shopping-cart-simple ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-warning text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalShipping">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Belum Terkirim</div>
                    </div>
                    <i class="ph-truck ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CHARTS ROW -->
    <div class="row">
        <!-- Grafik Tren Permintaan (Line) -->
        <div class="col-xl-7">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="ph-chart-line me-2 text-primary"></i>Tren Distribusi</h5>
                    <div class="ms-auto d-flex gap-1">
                        <button onclick="changePeriod('daily')" class="btn btn-xs btn-light rounded-pill px-3 shadow-none">Harian</button>
                        <button onclick="changePeriod('monthly')" class="btn btn-xs btn-light rounded-pill px-3 shadow-none">Bulanan</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:320px;">
                        <canvas id="orderTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Rasio Pengiriman & Top Produk -->
        <div class="col-xl-5">
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="mb-0 fw-bold"><i class="ph-chart-pie me-2 text-success"></i>Rasio Pengiriman</h5>
                        </div>
                        <div class="card-body d-flex align-items-center">
                            <div class="chart-container" style="position: relative; height:180px; width: 180px;">
                                <canvas id="deliveryRatioChart"></canvas>
                            </div>
                            <div class="ms-4 flex-fill">
                                <div class="mb-2">
                                    <span class="badge badge-dot bg-success me-1"></span> <small>Selesai Terkirim</small>
                                    <h6 class="fw-bold mb-0" id="ratioShippedText">0</h6>
                                </div>
                                <div>
                                    <span class="badge badge-dot bg-warning me-1"></span> <small>Belum Terkirim</small>
                                    <h6 class="fw-bold mb-0" id="ratioPendingText">0</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-transparent border-bottom py-2">
                            <h6 class="mb-0 fw-bold">Top 5 Produk Terdistribusi</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="topProductsList">
                                <div class="p-3 text-center text-muted small">Memuat data...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <!-- Aktivitas Terbaru -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header d-flex align-items-center py-3 bg-transparent border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="ph-clock-counter-clockwise me-2"></i>Log Aktivitas Terakhir</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="fs-xs text-uppercase fw-bold text-muted">
                                <th>Aktor</th>
                                <th>Aksi / Aktivitas</th>
                                <th class="text-center">Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="auditLogsBody">
                            <tr><td colspan="3" class="text-center py-4">Memuat log...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Info Sistem & Ringkasan Stok -->
        <div class="col-xl-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-info me-2"></i>Status Inventaris</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex px-0 border-0">
                            Stok Kritis/Rendah <span class="ms-auto fw-bold text-danger" id="summaryLowStock">0</span>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            Produk Stok Habis <span class="ms-auto fw-bold text-dark" id="summaryOutOfStock">0</span>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            Total Item Terdistribusi <span class="ms-auto fw-bold text-success" id="summaryDistributed">0</span>
                        </div>
                    </div>
                    <hr class="opacity-10 my-3">
                    <div class="chart-container mb-3" style="position: relative; height:150px;">
                        <canvas id="userRoleChart"></canvas>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">E-Pharma Engine v1.2 | Laravel {{ app()->version() }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let trendChartObj = null;
    let roleChartObj = null;
    let ratioChartObj = null;

    function changePeriod(p) { initDashboard(p); }

    function initDashboard(period = 'daily') {
        const apiUsers = axios.get('/api/users');
        const apiProducts = axios.get('/api/products');
        const apiAnalytics = axios.get(`/api/admin/analytics?period=${period}`);

        Promise.all([apiUsers, apiProducts, apiAnalytics])
            .then(results => {
                const users = results[0].data;
                const products = results[1].data;
                const analytics = results[2].data;

                // 1. Update Widget & Summary (Sync dengan key dari Backend)
                if (analytics.summary) {
                    document.getElementById('totalUsers').innerText = analytics.summary.total_users || 0;
                    document.getElementById('totalProducts').innerText = analytics.summary.total_products || 0;
                    document.getElementById('totalOrders').innerText = analytics.summary.total_orders || 0;
                    document.getElementById('totalShipping').innerText = analytics.summary.not_shipped || 0;

                    document.getElementById('summaryLowStock').innerText = analytics.summary.low_stock_products || 0;
                    document.getElementById('summaryDistributed').innerText = (analytics.summary.total_items_distributed || 0).toLocaleString() + ' Unit';

                    const outOfStock = products.filter(p => p.stock <= 0).length;
                    document.getElementById('summaryOutOfStock').innerText = outOfStock;
                }

                // 2. Render Top Products List
                let topHtml = '';
                if (analytics.top_drugs && analytics.top_drugs.length > 0) {
                    analytics.top_drugs.forEach((p, index) => {
                        topHtml += `
                            <div class="list-group-item d-flex align-items-center py-2 px-3 border-0">
                                <span class="badge bg-light text-indigo me-3">${index+1}</span>
                                <div class="flex-fill small fw-bold text-dark text-truncate">${p.name}</div>
                                <div class="ms-2 badge bg-primary bg-opacity-10 text-primary">${p.total_qty}</div>
                            </div>`;
                    });
                } else {
                    topHtml = '<div class="p-3 text-center text-muted small">Belum ada data distribusi.</div>';
                }
                document.getElementById('topProductsList').innerHTML = topHtml;

                // 3. Render Charts
                renderTrendChart(analytics.stats || []);
                renderRoleChart(users || []);
                renderDeliveryRatioChart(analytics.delivery_ratio || {shipped: 0, not_shipped: 0});
            })
            .catch(err => console.error("Gagal sinkronisasi data dashboard:", err));

        // Load Audit Logs
        axios.get('/api/admin/logs').then(res => {
            let html = '';
            const logs = res.data || [];
            logs.slice(0, 8).forEach(log => {
                const name = log.user ? log.user.name : 'System';
                html += `<tr>
                    <td><div class="fw-bold small text-indigo">${name}</div></td>
                    <td><div class="text-muted small">${log.action}</div></td>
                    <td class="text-center small opacity-50">${new Date(log.created_at).toLocaleTimeString('id-ID')}</td>
                </tr>`;
            });
            document.getElementById('auditLogsBody').innerHTML = html || '<tr><td colspan="3" class="text-center py-3">Tidak ada log.</td></tr>';
        });
    }

    function renderTrendChart(stats) {
        const ctx = document.getElementById('orderTrendChart').getContext('2d');
        if (trendChartObj) trendChartObj.destroy();
        trendChartObj = new Chart(ctx, {
            type: 'line',
            data: {
                labels: stats.map(s => s.label),
                datasets: [{
                    label: 'Pesanan Masuk',
                    data: stats.map(s => s.total_requests),
                    borderColor: '#5c6bc0',
                    backgroundColor: 'rgba(92, 107, 192, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#5c6bc0'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    function renderDeliveryRatioChart(ratio) {
        const ctx = document.getElementById('deliveryRatioChart').getContext('2d');
        document.getElementById('ratioShippedText').innerText = (ratio.shipped || 0) + " Selesai";
        document.getElementById('ratioPendingText').innerText = (ratio.not_shipped || 0) + " Proses";

        if (ratioChartObj) ratioChartObj.destroy();
        ratioChartObj = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Terkirim', 'Proses'],
                datasets: [{
                    data: [ratio.shipped || 0, ratio.not_shipped || 0],
                    backgroundColor: ['#10b981', '#ffa726'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: { legend: { display: false } }
            }
        });
    }

    function renderRoleChart(users) {
        const ctx = document.getElementById('userRoleChart').getContext('2d');
        const roles = {};
        users.forEach(u => {
            const roleName = (u.roles && u.roles[0]) ? u.roles[0].name : 'unknown';
            roles[roleName] = (roles[roleName] || 0) + 1;
        });

        if (roleChartObj) roleChartObj.destroy();
        roleChartObj = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(roles).map(r => r.toUpperCase()),
                datasets: [{
                    label: 'Jumlah Akun',
                    data: Object.values(roles),
                    backgroundColor: '#5c6bc0',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => initDashboard('daily'));
</script>

<style>
    .card { border-radius: 0.6rem; }
    .badge-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; vertical-align: middle; }
    .list-group-item { border-bottom: 1px solid #f0f0f0 !important; }
</style>
@endsection
