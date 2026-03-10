@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Tugas Pengiriman Aktif</h4>
            <div class="text-muted small">Kelola paket yang sedang Anda bawa atau dalam perjalanan</div>
        </div>
        <div class="ms-3">
            <button onclick="fetchActive()" class="btn btn-light btn-icon shadow-sm rounded-circle">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- LIST TUGAS AKTIF -->
    <div id="activeList" class="row g-3">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-indigo spinner-border-sm" role="status"></div>
            <span class="ms-2 text-muted small fw-bold">Memuat tugas aktif...</span>
        </div>
    </div>
</div>

<!-- MODAL: SELESAIKAN PENGIRIMAN -->
<div class="modal fade" id="modalComplete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-check-circle me-2"></i>Konfirmasi Sampai Tujuan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formComplete" onsubmit="submitComplete(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="complete_delivery_id">

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Nama Penerima</label>
                        <input type="text" name="receiver_name" class="form-control border-light-subtle" placeholder="Siapa yang menerima paket?" required>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Hubungan Penerima</label>
                        <select name="receiver_relation" class="form-select border-light-subtle" required>
                            <option value="" selected disabled>-- Pilih Hubungan --</option>
                            <option value="Staff Farmasi">Staff Farmasi</option>
                            <option value="Perawat/Dokter">Perawat/Dokter</option>
                            <option value="Resepsionis">Resepsionis</option>
                            <option value="Keamanan/Satpam">Keamanan/Satpam</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Foto Bukti Terima (Proof of Delivery)</label>
                        <input type="file" name="image" class="form-control border-light-subtle" accept="image/*" required>
                        <small class="text-muted">Pastikan wajah penerima atau gedung terlihat jelas.</small>
                    </div>

                    <div class="mb-0">
                        <label class="small fw-bold text-muted mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="delivery_note" class="form-control border-light-subtle" rows="2" placeholder="Contoh: Paket diletakkan di meja resepsionis..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSubmitComplete" class="btn btn-indigo px-4 fw-bold shadow-sm rounded-pill">SELESAIKAN TUGAS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchActive() {
        const container = document.getElementById('activeList');

        axios.get('/api/deliveries/active')
            .then(res => {
                let html = '';
                if (res.data.length === 0) {
                    html = `
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3 py-5 text-center border-2 border-dashed border-light">
                            <div class="card-body">
                                <i class="ph-bicycle ph-4x text-muted opacity-25 mb-3"></i>
                                <h5 class="fw-bold text-muted">Tidak Ada Tugas Aktif</h5>
                                <p class="text-muted mx-auto" style="max-width: 400px;">Anda belum mengambil tugas dari bursa. Silakan cek menu "Bursa Tugas" untuk memulai pengiriman.</p>
                                <a href="/courier/available" class="btn btn-indigo rounded-pill px-4 mt-2">
                                    <i class="ph-hand-pointing me-2"></i>Cari Tugas
                                </a>
                            </div>
                        </div>
                    </div>`;
                } else {
                    res.data.forEach(d => {
                        // FIX: Gunakan d.order (bukan d.request) sesuai relasi terbaru
                        const statusName = d.status ? d.status.name : 'Unknown';
                        const address = d.order.user.address || 'Alamat tidak tersedia';

                        html += `
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-start-width-5 ${statusName === 'In Transit' ? 'border-start-primary' : 'border-start-warning'}">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="badge bg-light text-indigo border-indigo border-opacity-25 px-2">
                                            <i class="ph-hash me-1"></i>${d.tracking_number}
                                        </span>
                                        <span class="badge ${statusName === 'In Transit' ? 'bg-primary' : 'bg-warning text-dark'} rounded-pill">
                                            ${statusName.toUpperCase()}
                                        </span>
                                    </div>

                                    <div class="mb-3">
                                        <div class="fs-xs text-muted text-uppercase fw-bold mb-1">Penerima (Tujuan)</div>
                                        <h6 class="fw-bold text-dark mb-0">${d.order.user.name}</h6>
                                    </div>

                                    <div class="mb-4">
                                        <div class="fs-xs text-muted text-uppercase fw-bold mb-1">Alamat Pengantaran</div>
                                        <div class="small text-dark d-flex align-items-start">
                                            <i class="ph-map-pin text-danger me-2 mt-1"></i>
                                            <span style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                ${address}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        ${statusName === 'Claimed' ? `
                                            <button onclick="startShipping(${d.id})" class="btn btn-indigo fw-bold py-2 rounded-pill">
                                                <i class="ph-navigation-arrow me-2"></i>MULAI PERJALANAN
                                            </button>
                                        ` : `
                                            <button onclick="openCompleteModal(${d.id})" class="btn btn-success fw-bold py-2 rounded-pill shadow-sm">
                                                <i class="ph-check-circle me-2"></i>KONFIRMASI SAMPAI
                                            </button>
                                        `}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                }
                container.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<div class="col-12 text-center text-danger py-5 fw-bold"><i class="ph-warning-octagon me-2"></i>Gagal memuat data aktif.</div>';
            });
    }

    function startShipping(id) {
        axios.post(`/api/deliveries/start/${id}`)
            .then(() => {
                Swal.fire({ icon: 'success', title: 'Perjalanan Dimulai!', timer: 1500, showConfirmButton: false });
                fetchActive();
            });
    }

    function openCompleteModal(id) {
        document.getElementById('formComplete').reset();
        document.getElementById('complete_delivery_id').value = id;
        new bootstrap.Modal(document.getElementById('modalComplete')).show();
    }

    function submitComplete(e) {
        e.preventDefault();
        const id = document.getElementById('complete_delivery_id').value;
        const btn = document.getElementById('btnSubmitComplete');
        const formData = new FormData(e.target);

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        axios.post(`/api/deliveries/complete/${id}`, formData)
            .then(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalComplete')).hide();
                Swal.fire({ icon: 'success', title: 'Pengiriman Selesai!', text: 'Tugas telah dipindahkan ke riwayat.', confirmButtonColor: '#5c68e2' });
                fetchActive();
            })
            .catch(err => {
                Swal.fire('Error', err.response?.data?.message || 'Gagal mengirim data.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'SELESAIKAN TUGAS';
            });
    }

    document.addEventListener('DOMContentLoaded', fetchActive);
</script>

<style>
    .bg-indigo { background-color: #5c68e2 !important; }
    .text-indigo { color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }
    .border-start-width-5 { border-start-width: 5px !important; }
    .border-start-primary { border-left: 5px solid #5c68e2 !important; }
    .border-start-warning { border-left: 5px solid #ffb300 !important; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection