@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-dark">Ringkasan Kerja Kurir</h4>
        <p class="text-muted small">Pantau performa dan ketersediaan tugas pengiriman Anda.</p>
    </div>

    <div class="row g-4">
        <!-- Bursa Tugas Tersedia -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-primary border-4 position-relative card-hover">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-megaphone-fill text-primary fs-2"></i>
                    </div>
                    <div class="ms-4">
                        <small class="text-muted fw-bold d-block mb-1 text-uppercase" style="font-size: 11px;">Tersedia di Bursa</small>
                        <h1 class="fw-bold mb-0 text-dark" id="statAvailable">0</h1>
                        <!-- TAMBAHKAN KETERANGAN INI -->
                    </div>
                </div>
                <a href="/courier/available" class="stretched-link"></a>
            </div>
        </div>

        <!-- Tugas Sedang Berjalan -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-warning border-4 position-relative card-hover">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-bicycle text-warning fs-2"></i>
                    </div>
                    <div class="ms-4">
                        <small class="text-muted fw-bold d-block mb-1 text-uppercase" style="font-size: 11px;">Tugas Aktif Saya</small>
                        <h1 class="fw-bold mb-0 text-dark" id="statActive">0</h1>
                    </div>
                </div>
                <a href="/courier/active" class="stretched-link"></a>
            </div>
        </div>

        <!-- Total Pengiriman Selesai -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-success border-4 position-relative card-hover">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-check-all text-success fs-2"></i>
                    </div>
                    <div class="ms-4">
                        <small class="text-muted fw-bold d-block mb-1 text-uppercase" style="font-size: 11px;">Total Berhasil</small>
                        <h1 class="fw-bold mb-0 text-dark" id="statCompleted">0</h1>
                    </div>
                </div>
                <a href="/courier/history" class="stretched-link"></a>
            </div>
        </div>
    </div>
</div>

<script>
    // Header Token Global sudah diset di Layout Backoffice
    function loadCourierStats() {
        axios.get('/api/courier/stats')
            .then(res => {
                const data = res.data;
                document.getElementById('statAvailable').innerText = data.available;
                document.getElementById('statActive').innerText = data.active;
                document.getElementById('statCompleted').innerText = data.completed;

                // Tambahan: Tampilkan jenis kendaraan agar kurir tidak bingung
                // Kita bisa ambil role/detail user dari session atau panggil API profile
                // Sebagai contoh sederhana, kita beri label sesuai hasil filter:
                // (Atau Anda bisa modifikasi API stats untuk mengirimkan nama kendaraan kurir)
            })
            .catch(err => {
                console.error("Gagal memuat statistik kurir:", err);
            });
    }

    document.addEventListener('DOMContentLoaded', loadCourierStats);
</script>

<style>
    .card-hover { transition: transform 0.2s, box-shadow 0.2s; }
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
</style>
@endsection