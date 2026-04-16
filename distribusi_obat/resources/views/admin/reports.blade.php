@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-sm-flex align-items-sm-center justify-content-sm-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Laporan Rekapitulasi Distribusi</h4>
            <div class="text-muted small">Analisis data transaksi dan pengiriman sediaan farmasi.</div>
        </div>
        <div class="mt-3 mt-sm-0">
            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                <a href="/admin/export/excel" class="btn btn-success border-0 px-3">
                    <i class="ph-file-xls me-2"></i> Excel
                </a>
                <a href="/admin/export/pdf" class="btn btn-danger border-0 px-3">
                    <i class="ph-file-pdf me-2"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Filter & Statistik -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold text-indigo"><i class="ph-funnel me-2"></i>Filter Laporan</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="fs-xs fw-bold text-muted text-uppercase mb-1">Dari Tanggal</label>
                            <input type="date" id="start_date" class="form-control form-control-sm bg-light border-0 rounded-pill">
                        </div>
                        <div class="col-md-5">
                            <label class="fs-xs fw-bold text-muted text-uppercase mb-1">Sampai Tanggal</label>
                            <input type="date" id="end_date" class="form-control form-control-sm bg-light border-0 rounded-pill">
                        </div>
                        <div class="col-md-2">
                            <button onclick="fetchReportData()" class="btn btn-indigo btn-sm w-100 rounded-pill fw-bold shadow-sm">
                                FILTER
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="row g-3">
                <div class="col-6">
                    <div class="card card-body bg-indigo text-white shadow-sm border-0 rounded-3 p-3">
                        <small class="text-uppercase fs-xs opacity-75">Selesai</small>
                        <h4 class="mb-0 fw-bold" id="sumCompleted">0</h4>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card card-body bg-teal text-white shadow-sm border-0 rounded-3 p-3">
                        <small class="text-uppercase fs-xs opacity-75">Terkirim</small>
                        <h4 class="mb-0 fw-bold" id="sumItems">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-chart-line-up me-2 text-primary"></i>Tren Volume Pesanan</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="reportChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-ranking me-2 text-warning"></i>Produk Terlaris</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush border-top-0" id="topProductsList">
                        <div class="text-center py-5"><div class="ph-spinner spinner text-muted"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 mb-5">
        <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="ph-table me-2 text-indigo"></i>Data Detail Transaksi</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">ID Order</th>
                        <th>Fasilitas Kesehatan</th>
                        <th class="text-center">Item</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Total</th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let myChartObj = null;

    function fetchReportData() {
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;

        // 1. Ambil Data Analitik (Grafik & Summary)
        axios.get(`/api/admin/analytics?period=daily`).then(res => {
            const data = res.data;
            // FIX UNDEFINED: Gunakan fallback || 0 jika data null
            document.getElementById('sumCompleted').innerText = data.summary?.total_completed || 0;
            document.getElementById('sumItems').innerText = data.summary?.total_items_distributed || 0;

            renderChart(data.stats || []);
            renderTopProducts(data.top_drugs || []);
        });

        // 2. Ambil Data Tabel Detail dengan Filter Tanggal
        axios.get(`/api/admin/reports`, {
            params: { start_date: start, end_date: end }
        }).then(res => {
            let html = '';
            res.data.forEach(o => {
                const statusName = o.status?.name || 'PENDING';
                html += `
                <tr>
                    <td class="ps-3 fw-bold text-indigo">#${o.id.substring(0,8)}</td>
                    <td>
                        <div class="fw-bold text-dark">${o.user?.name || 'N/A'}</div>
                        <div class="fs-xs text-muted">${new Date(o.created_at).toLocaleDateString('id-ID')}</div>
                    </td>
                    <td class="text-center">${o.items ? o.items.length : 0} Jenis</td>
                    <td class="text-center">
                        <span class="badge bg-light text-primary border rounded-pill px-2">
                            ${statusName.toUpperCase()}
                        </span>
                    </td>
                    <td class="text-end pe-3 fw-bold">Rp${Number(o.total).toLocaleString()}</td>
                </tr>`;
            });
            document.getElementById('reportTableBody').innerHTML = html || '<tr><td colspan="5" class="text-center py-4">Tidak ada data.</td></tr>';
        }).catch(err => {
            console.error(err);
            document.getElementById('reportTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal memuat data tabel.</td></tr>';
        });
    }

    function renderChart(stats) {
        const ctx = document.getElementById('reportChart').getContext('2d');
        if (myChartObj) myChartObj.destroy();

        myChartObj = new Chart(ctx, {
            type: 'line',
            data: {
                labels: stats.map(s => s.label),
                datasets: [{
                    label: 'Pesanan',
                    data: stats.map(s => s.total_requests),
                    borderColor: '#5c6bc0',
                    backgroundColor: 'rgba(92, 107, 192, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    function renderTopProducts(products) {
        let html = '';
        products.forEach((p, idx) => {
            html += `
            <div class="list-group-item d-flex align-items-center py-3">
                <div class="me-3 badge bg-light text-indigo rounded-circle" style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;">${idx+1}</div>
                <div class="flex-fill fw-bold text-dark small text-uppercase">${p.name}</div>
                <div class="ms-2"><span class="badge bg-indigo bg-opacity-10 text-indigo">${p.total_qty} unit</span></div>
            </div>`;
        });
        document.getElementById('topProductsList').innerHTML = html || '<div class="p-3 text-center">Kosong</div>';
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').value = today;
        document.getElementById('end_date').value = today;
        fetchReportData();
    });
</script>
@endsection
