@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-dark">Ringkasan Sistem</h4>
        <p class="text-muted">Selamat datang kembali, Administrator. Berikut adalah statistik sistem hari ini.</p>
    </div>

    <!-- Statistik Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card card-stats p-4 border-0 shadow-sm bg-white border-start border-primary border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-people-fill text-primary fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">TOTAL PENGGUNA</small>
                        <h3 class="fw-bold mb-0 text-dark" id="totalUsers">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats p-4 border-0 shadow-sm bg-white border-start border-success border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-capsule text-success fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">KATALOG OBAT</small>
                        <h3 class="fw-bold mb-0 text-dark" id="totalDrugs">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats p-4 border-0 shadow-sm bg-white border-start border-warning border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-clipboard-data text-warning fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">TRANSAKSI REQUEST</small>
                        <h3 class="fw-bold mb-0 text-dark" id="totalRequests">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats p-4 border-0 shadow-sm bg-white border-start border-info border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-truck text-info fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">PENGIRIMAN AKTIF</small>
                        <h3 class="fw-bold mb-0 text-dark" id="totalDeliveries">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Aktivitas Terbaru -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i> Log Aktivitas Terakhir</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small">
                                <tr>
                                    <th class="ps-4">Admin</th>
                                    <th>Aksi</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="auditLogsBody">
                                <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada log aktivitas.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Distribution Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2"></i> Info Sistem</h6>
                </div>
                <div class="card-body">
                    <div class="p-3 mb-2 bg-light rounded-3 d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-muted">Versi Laravel</span>
                        <span class="badge bg-white text-dark shadow-sm">{{ app()->version() }}</span>
                    </div>
                    <div class="p-3 mb-2 bg-light rounded-3 d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-muted">Versi PHP</span>
                        <span class="badge bg-white text-dark shadow-sm">{{ PHP_VERSION }}</span>
                    </div>
                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-muted">Database</span>
                        <span class="badge bg-white text-dark shadow-sm">MySQL (Active)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadAdminStats() {
        // Ambil data dari API Admin yang sudah kita buat
        // Kita gunakan endpoint yang ada atau panggil beberapa endpoint sekaligus
        const apiUsers = axios.get('/api/users');
        const apiDrugs = axios.get('/api/drugs');
        const apiRequests = axios.get('/api/requests');

        Promise.all([apiUsers, apiDrugs, apiRequests])
            .then(results => {
                document.getElementById('totalUsers').innerText = results[0].data.length;
                document.getElementById('totalDrugs').innerText = results[1].data.length;
                document.getElementById('totalRequests').innerText = results[2].data.length;

                // Menghitung pengiriman (contoh simpel)
                const activeShipping = results[2].data.filter(r => r.status === 'shipping').length;
                document.getElementById('totalDeliveries').innerText = activeShipping;
            })
            .catch(err => console.error("Gagal memuat statistik:", err));
    }

    document.addEventListener('DOMContentLoaded', loadAdminStats);
</script>
@endsection