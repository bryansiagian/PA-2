@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page dengan Tombol Export -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Dashboard Admin</h4>
            <div class="text-muted small">Rekapitulasi operasional logistik E-Pharma</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <!-- Dropdown Export sesuai Requirement MK PA2 -->
            {{-- <div class="dropdown">
                <button class="btn btn-indigo dropdown-toggle rounded-pill px-4 shadow-sm" data-bs-toggle="dropdown">
                    <i class="ph-file-arrow-down me-2"></i> Export Report
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item py-2" href="/admin/export/excel"><i class="ph-file-xls text-success me-2"></i> Export Excel (.xlsx)</a></li>
                    <li><a class="dropdown-item py-2" href="/admin/export/pdf"><i class="ph-file-pdf text-danger me-2"></i> Export PDF (.pdf)</a></li>
                </ul>
            </div> --}}
            <button onclick="initDashboard()" class="btn btn-light btn-icon rounded-circle shadow-sm">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Quick stats boxes -->
    <div class="row">
        <div class="col-lg-3">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalUsers">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Total Pengguna</div>
                    </div>
                    <i class="ph-users ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card card-body bg-teal text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalProducts">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Produk Katalog</div>
                    </div>
                    <i class="ph-pill ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card card-body bg-pink text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalOrders">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Pesanan Masuk</div>
                    </div>
                    <i class="ph-clipboard-text ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card card-body bg-warning text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <h4 class="mb-0 fw-bold" id="totalShipping">0</h4>
                    <div class="ms-auto"><i class="ph-truck ph-2x opacity-75"></i></div>
                </div>
                <div>
                    Pengiriman Aktif
                    <div class="fs-sm opacity-75">Dalam perjalanan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CHARTS ROW -->
    <div class="row">
        <div class="col-xl-8">
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

        <div class="col-xl-4">
            <div class="card shadow-sm border-0 rounded-3 mb-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="ph-chart-pie me-2 text-success"></i>Komposisi Role</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:200px;">
                        <canvas id="userRoleChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- TOP 5 PRODUK (BARU) -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-2">
                    <h6 class="mb-0 fw-bold">Top 5 Produk Terlaris</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="topProductsList">
                        <div class="p-3 text-center text-muted small">Memuat data...</div>
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
                    <h6 class="mb-0 fw-bold"><i class="ph-info me-2"></i>Status Rekapitulasi</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex px-0 border-0">
                            Total Item Katalog <span class="ms-auto fw-bold text-dark" id="summaryTotalProducts">0</span>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            Stok Kritis/Rendah <span class="ms-auto fw-bold text-danger" id="summaryLowStock">0</span>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            Total Item Terdistribusi <span class="ms-auto fw-bold text-success" id="summaryDistributed">0</span>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            Status Database <span class="ms-auto text-success fw-bold">Online</span>
                        </div>
                    </div>
                    <hr class="opacity-10">
                    <div class="text-center">
                        <small class="text-muted">Laravel 12 Engine v{{ app()->version() }}</small>
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

    function changePeriod(p) {
        initDashboard(p);
    }

    function initDashboard(period = 'daily') {
        const apiUsers = axios.get('/api/users');
        const apiProducts = axios.get('/api/products');
        const apiOrders = axios.get('/api/orders');
        const apiAnalytics = axios.get(`/api/admin/analytics?period=${period}`);

        Promise.all([apiUsers, apiProducts, apiOrders, apiAnalytics])
            .then(results => {
                const users = results[0].data;
                const products = results[1].data;
                const orders = results[2].data;
                const analytics = results[3].data;

                // 1. Update Widget Counters
                document.getElementById('totalUsers').innerText = users.length;
                document.getElementById('totalProducts').innerText = products.length;
                document.getElementById('totalOrders').innerText = orders.length;

                const shippingCount = orders.filter(o => o.status && o.status.name === 'Shipping').length;
                document.getElementById('totalShipping').innerText = shippingCount;

                // 2. Update Summary List
                document.getElementById('summaryTotalProducts').innerText = analytics.summary.total_products;
                document.getElementById('summaryLowStock').innerText = analytics.summary.low_stock_products;
                document.getElementById('summaryDistributed').innerText = analytics.summary.total_items_distributed.toLocaleString() + ' Unit';

                // 3. Render Top Products List
                let topHtml = '';
                analytics.top_drugs.forEach((p, index) => {
                    topHtml += `
                        <div class="list-group-item d-flex align-items-center py-2 px-3 border-0">
                            <span class="badge bg-light text-indigo me-3">${index+1}</span>
                            <div class="flex-fill small fw-bold text-dark text-truncate">${p.name}</div>
                            <div class="ms-2 badge bg-primary bg-opacity-10 text-primary">${p.total_qty} Unit</div>
                        </div>`;
                });
                document.getElementById('topProductsList').innerHTML = topHtml || '<div class="p-3 text-center">Belum ada data distribusi.</div>';

                // 4. Render Charts
                renderTrendChart(analytics.stats);
                renderRoleChart(users);
            })
            .catch(err => console.error("Gagal sinkronisasi data:", err));

        // Load Audit Logs
        axios.get('/api/admin/logs').then(res => {
            let html = '';
            res.data.slice(0, 8).forEach(log => {
                const name = log.user ? log.user.name : 'System';
                html += `<tr>
                    <td><div class="fw-bold small text-indigo">${name}</div></td>
                    <td><div class="text-muted small">${log.action}</div></td>
                    <td class="text-center small opacity-50">${new Date(log.created_at).toLocaleTimeString('id-ID')}</td>
                </tr>`;
            });
            document.getElementById('auditLogsBody').innerHTML = html;
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
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
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
            type: 'doughnut',
            data: {
                labels: Object.keys(roles).map(r => r.toUpperCase()),
                datasets: [{
                    data: Object.values(roles),
                    backgroundColor: ['#5c6bc0', '#26a69a', '#ffa726', '#ef5350'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, padding: 15 } } },
                cutout: '75%'
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => initDashboard('daily'));
</script>

<style>
    .card { border-radius: 0.6rem; }
    .btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .list-group-item { border-bottom: 1px solid #f0f0f0 !important; }
</style>
@endsection
