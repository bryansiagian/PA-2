@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Laporan & Analitik Distribusi</h4>
            <p class="text-muted small">Data performa pengiriman dan tren permintaan obat.</p>
        </div>
        <div class="btn-group shadow-sm">
            <button onclick="updateChart('daily')" class="btn btn-white border px-3 btn-sm active">Harian</button>
            <button onclick="updateChart('monthly')" class="btn btn-white border px-3 btn-sm">Bulanan</button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-all fs-1 opacity-50"></i>
                    <div class="ms-4">
                        <small class="text-white-50 fw-bold d-block">TOTAL PESANAN SUKSES</small>
                        <h2 class="fw-bold mb-0" id="sumCompleted">0</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-white">
                <div class="d-flex align-items-center">
                    <i class="bi bi-box-seam fs-1 opacity-50"></i>
                    <div class="ms-4">
                        <small class="text-white-50 fw-bold d-block">TOTAL OBAT TERDISTRIBUSI</small>
                        <h2 class="fw-bold mb-0" id="sumItems">0</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Grafik Utama -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>Tren Permintaan Selesai</h6>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Top 5 Obat -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 text-center">
                    <h6 class="fw-bold mb-0">Obat Paling Banyak Diminta</h6>
                </div>
                <div class="card-body">
                    <div id="topDrugsList">
                        <!-- Diisi via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Library Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let myChart = null;

    function fetchAnalytics(period = 'daily') {
        axios.get(`/api/admin/analytics?period=${period}`)
            .then(res => {
                const data = res.data;

                // 1. Update Summary
                document.getElementById('sumCompleted').innerText = data.summary.total_completed;
                document.getElementById('sumItems').innerText = data.summary.total_items_distributed;

                // 2. Render Chart
                const ctx = document.getElementById('distributionChart').getContext('2d');
                const labels = data.stats.map(s => s.label);
                const values = data.stats.map(s => s.total_requests);

                if(myChart) myChart.destroy(); // Hapus chart lama sebelum buat baru

                myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Pesanan Selesai',
                            data: values,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointBackgroundColor: '#0d6efd'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { display: false } },
                            x: { grid: { display: false } }
                        }
                    }
                });

                // 3. Render Top Drugs
                let drugHtml = '';
                data.top_drugs.forEach((drug, index) => {
                    drugHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge bg-light text-dark me-2">${index + 1}</span>
                                <span class="small fw-bold">${drug.name}</span>
                            </div>
                            <span class="badge bg-soft-primary text-primary rounded-pill">${drug.total_qty} unit</span>
                        </div>`;
                });
                document.getElementById('topDrugsList').innerHTML = drugHtml || '<p class="text-center text-muted">Belum ada data.</p>';
            });
    }

    function updateChart(period) {
        // Toggle active button UI
        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        fetchAnalytics(period);
    }

    document.addEventListener('DOMContentLoaded', () => fetchAnalytics('daily'));
</script>

<style>
    .bg-soft-primary { background-color: #e7f1ff; color: #0d6efd; }
    canvas { width: 100% !important; }
</style>
@endsection