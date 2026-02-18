@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Bursa Tugas Tersedia</h4>
            <p class="text-muted small mb-0">Daftar paket yang siap dijemput di gudang pusat.</p>
        </div>
        <button onclick="fetchAvailable()" class="btn btn-white border shadow-sm rounded-pill px-3">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Bursa
        </button>
    </div>

    <!-- LIST BURSA -->
    <div id="availableList" class="row g-3">
        <!-- Data dimuat via JavaScript -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
            <span class="ms-2 text-muted small">Memeriksa ketersediaan paket...</span>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: RINCIAN BARANG
     ========================================== -->
<div class="modal fade" id="modalItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark">Isi Paket Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalItemsBody">
                <!-- List obat diisi via JS -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchAvailable() {
        const container = document.getElementById('availableList');

        axios.get('/api/deliveries/available')
            .then(res => {
                let html = '';
                if (res.data.length === 0) {
                    html = `
                    <div class="col-12 text-center py-5">
                        <div class="card border-0 shadow-sm rounded-4 p-5 border border-dashed">
                            <i class="bi bi-inbox text-muted display-1 opacity-25"></i>
                            <h5 class="mt-3 fw-bold text-muted">Bursa Sedang Kosong</h5>
                            <p class="text-muted small">Belum ada paket baru yang disiapkan oleh operator gudang.</p>
                        </div>
                    </div>`;
                } else {
                    res.data.forEach(d => {
                        const itemsJson = JSON.stringify(d.request.items).replace(/"/g, '&quot;');
                        html += `
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100 card-hover border-top border-primary border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="badge bg-soft-primary text-primary px-3 rounded-pill border border-primary border-opacity-25">#${d.tracking_number}</span>
                                        <button onclick="showItems('${itemsJson}')" class="btn btn-sm btn-link p-0 text-decoration-none small fw-bold">
                                            <i class="bi bi-info-circle"></i> Detail Barang
                                        </button>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">${d.request.user.name}</h6>
                                    <p class="small text-muted mb-4"><i class="bi bi-geo-alt-fill text-danger"></i> ${d.request.user.address || 'Alamat tidak tersedia'}</p>

                                    <button onclick="claimTask(${d.id})" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm py-2">
                                        Ambil & Jemput Paket
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    });
                }
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<div class="col-12 text-center text-danger py-5">Gagal terhubung ke server.</div>';
            });
    }

    function showItems(encoded) {
        const items = JSON.parse(encoded);
        let html = '<div class="list-group list-group-flush">';
        items.forEach(i => {
            const name = i.drug ? i.drug.name : `<span class="text-danger fw-bold">${i.custom_drug_name} (Manual)</span>`;
            const unit = i.drug ? i.drug.unit : (i.custom_unit || 'Unit');
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                    <div>
                        <div class="fw-bold text-dark small">${name}</div>
                        <small class="text-muted">Unit: ${unit}</small>
                    </div>
                    <span class="badge bg-light text-primary border rounded-pill">x${i.quantity}</span>
                </div>`;
        });
        html += '</div>';
        document.getElementById('modalItemsBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalItems')).show();
    }

    function claimTask(id) {
        Swal.fire({
            title: 'Ambil Tugas?',
            text: "Anda akan bertanggung jawab atas paket ini sampai tujuan.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Saya Ambil',
            confirmButtonColor: '#0d6efd'
        }).then(result => {
            if(result.isConfirmed) {
                axios.post(`/api/deliveries/claim/${id}`).then(() => {
                    Swal.fire('Berhasil!', 'Tugas dipindahkan ke daftar tugas aktif Anda.', 'success')
                        .then(() => window.location.href = '/courier/active');
                }).catch(() => {
                    Swal.fire('Gagal', 'Maaf, tugas sudah diambil kurir lain.', 'error');
                    fetchAvailable();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchAvailable);
</script>

<style>
    .card-hover { transition: all 0.3s ease; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
    .bg-soft-primary { background-color: #e0f2fe; }
    .border-dashed { border-style: dashed !important; }
</style>
@endsection