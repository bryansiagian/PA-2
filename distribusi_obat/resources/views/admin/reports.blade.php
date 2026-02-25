@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page Actions -->
    <div class="d-sm-flex align-items-sm-center justify-content-sm-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Laporan Distribusi Obat</h4>
            <div class="text-muted">Analisis mendalam mengenai tren pengeluaran logistik farmasi</div>
        </div>
        <br>
        <div class="mt-3 mt-sm-0">
            <!-- TOMBOL EKSPOR (REVISI DOSEN: HARUS BISA PDF/EXCEL) -->
            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                <a href="/api/admin/export/excel" class="btn btn-success border-0 px-3">
                    <i class="ph-file-xls me-2"></i> Excel
                </a>
                <a href="/api/admin/export/pdf" class="btn btn-danger border-0 px-3">
                    <i class="ph-file-pdf me-2"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Filter & Summary -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white d-flex align-items-center border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-funnel me-2"></i>Filter Rentang Waktu</h6>
                    <div class="ms-auto">
                        <div class="btn-group btn-group-sm p-1 bg-light rounded-pill">
                            <button id="btnDaily" onclick="updateReport('daily')" class="btn btn-white shadow-sm rounded-pill border-0 px-3 active">Harian</button>
                            <button id="btnMonthly" onclick="updateReport('monthly')" class="btn btn-flat-dark rounded-pill border-0 px-3">Bulanan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
             <!-- Placeholder untuk filter spesifik lainnya jika ada -->
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="card card-body bg-indigo text-white border-0 shadow-sm rounded-3">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-pill me-3">
                        <i class="ph-check-circle ph-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold" id="sumCompleted">0</h3>
                        <span class="text-uppercase fs-xs opacity-75">Permintaan Selesai</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card card-body bg-success text-white border-0 shadow-sm rounded-3">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-pill me-3">
                        <i class="ph-package ph-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold" id="sumItems">0</h3>
                        <span class="text-uppercase fs-xs opacity-75">Item Terdistribusi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Line Chart -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-chart-line-up me-2 text-primary"></i>Visualisasi Tren Pengiriman</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="reportChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Top Medicines -->
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-ranking me-2 text-warning"></i>Obat Paling Diminati</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush border-top-0" id="topDrugsList">
                        <!-- Data via JS -->
                        <div class="text-center py-5">
                            <div class="ph-spinner spinner text-muted"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let myChartObj = null;

    function fetchReport(period = 'daily') {
        axios.get(`/api/admin/analytics?period=${period}`)
            .then(res => {
                const data = res.data;

                // 1. Update Summary Counters
                document.getElementById('sumCompleted').innerText = data.summary.total_completed;
                document.getElementById('sumItems').innerText = data.summary.total_items_distributed;

                // 2. Setup Chart.js
                const ctx = document.getElementById('reportChart').getContext('2d');
                const labels = data.stats.map(s => s.label);
                const values = data.stats.map(s => s.total_requests);

                if (myChartObj) myChartObj.destroy();

                myChartObj = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Pesanan Selesai',
                            data: values,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.05)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
                            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                        }
                    }
                });

                // 3. Render Top Drugs List
                let drugHtml = '';
                data.top_drugs.forEach((drug, index) => {
                    drugHtml += `
                    <div class="list-group-item d-flex align-items-center py-3">
                        <div class="me-3">
                            <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: bold; font-size: 12px;">
                                ${index + 1}
                            </div>
                        </div>
                        <div class="flex-fill">
                            <div class="fw-bold text-dark small text-uppercase">${drug.name}</div>
                        </div>
                        <div class="ms-3 text-end">
                            <span class="badge bg-indigo rounded-pill px-2">${drug.total_qty} unit</span>
                        </div>
                    </div>`;
                });
                document.getElementById('topDrugsList').innerHTML = drugHtml || '<div class="p-4 text-center text-muted small">Belum ada transaksi selesai.</div>';
            });
    }

    function updateReport(period) {
        // Toggle Active UI
        document.getElementById('btnDaily').className = period === 'daily' ? 'btn btn-white shadow-sm rounded-pill border-0 px-3 active' : 'btn btn-flat-dark rounded-pill border-0 px-3';
        document.getElementById('btnMonthly').className = period === 'monthly' ? 'btn btn-white shadow-sm rounded-pill border-0 px-3 active' : 'btn btn-flat-dark rounded-pill border-0 px-3';

        fetchReport(period);
    }

    document.addEventListener('DOMContentLoaded', () => fetchReport('daily'));
</script>

<style>
    /* Menyelaraskan dengan Limitless theme */
    .card-header { padding: 1.25rem; }
    .list-group-item { border-left: none; border-right: none; }
    .bg-indigo { background-color: #5c6bc0 !important; }
    .btn-white { background: #fff; color: #333; }
</style>
@endsection