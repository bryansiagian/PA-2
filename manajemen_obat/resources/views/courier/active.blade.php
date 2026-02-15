@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Tugas Aktif Saya</h4>
            <p class="text-muted small mb-0">Kelola pengiriman yang sedang Anda tangani saat ini.</p>
        </div>
        <button onclick="fetchActive()" class="btn btn-white border shadow-sm rounded-pill px-3">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>

    <!-- LIST TUGAS AKTIF -->
    <div id="activeList" class="row g-3">
        <!-- Data dimuat via JavaScript -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="mt-2 text-muted">Menyinkronkan tugas aktif...</p>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL 1: RINCIAN BARANG DALAM PAKET
     ========================================== -->
<div class="modal fade" id="modalItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="bi bi-box-seam me-2"></i>Rincian Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalItemsBody">
                <!-- List obat diisi via JS -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL 2: KONFIRMASI SAMPAI (UPLOAD FOTO)
     ========================================== -->
<div class="modal fade" id="modalProof" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 text-center">
                <h5 class="fw-bold w-100">Konfirmasi Paket Sampai</h5>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted small mb-4">Ambil foto paket atau foto bersama penerima sebagai bukti pengiriman yang sah.</p>

                <!-- Area Preview Kamera/Galeri -->
                <div id="uploadArea" class="rounded-4 p-4 mb-3 border border-2 border-dashed d-flex flex-column align-items-center justify-content-center position-relative"
                     style="height: 250px; cursor: pointer; background-color: #f8f9fa;"
                     onclick="document.getElementById('proofInput').click()">

                    <div id="placeholderUI">
                        <i class="bi bi-camera-fill text-success mb-2" style="font-size: 3rem;"></i>
                        <p class="mb-0 fw-bold">Klik untuk Ambil Foto</p>
                        <small class="text-muted">Mendukung Kamera & Galeri</small>
                    </div>

                    <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded-4 d-none" style="max-height: 100%; width: 100%; object-fit: cover;">
                </div>

                <input type="file" id="proofInput" accept="image/*" capture="environment" class="d-none" onchange="handlePreview(this)">

                <div class="d-grid gap-2">
                    <button id="btnComplete" onclick="submitComplete()" class="btn btn-success py-3 rounded-pill fw-bold shadow-sm" disabled>
                        <i class="bi bi-check-circle-fill me-2"></i> Selesaikan Pengiriman
                    </button>
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Pastikan token terpasang
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let selectedId = null;

    function fetchActive() {
        const container = document.getElementById('activeList');

        axios.get('/api/deliveries/active')
            .then(res => {
                let html = '';
                if (res.data.length === 0) {
                    html = `
                    <div class="col-12 text-center py-5">
                        <div class="card border-0 shadow-sm rounded-4 p-5">
                            <i class="bi bi-check2-all text-success display-1 opacity-25"></i>
                            <h5 class="mt-3 fw-bold">Tidak Ada Tugas Aktif</h5>
                            <p class="text-muted">Semua paket telah Anda selesaikan atau Anda belum mengambil tugas baru.</p>
                            <a href="/courier/available" class="btn btn-primary rounded-pill px-4">Cari di Bursa Tugas</a>
                        </div>
                    </div>`;
                } else {
                    res.data.forEach(d => {
                        const isMoving = d.status === 'in_transit';
                        const itemsJson = JSON.stringify(d.request.items).replace(/"/g, '&quot;');
                        const badgeClass = isMoving ? 'bg-warning text-dark' : 'bg-info text-white';

                        html += `
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 ${isMoving ? 'border-warning' : 'border-info'}">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="badge ${badgeClass} rounded-pill px-3">${d.status.toUpperCase().replace('_', ' ')}</span>
                                        <button onclick="showItems('${itemsJson}')" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="bi bi-box"></i> Lihat Barang
                                        </button>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">${d.request.user.name}</h5>
                                    <p class="text-muted small mb-4"><i class="bi bi-geo-alt-fill text-danger"></i> ${d.request.user.address || 'Alamat tidak tersedia'}</p>

                                    <div class="d-grid gap-2">
                                        ${!isMoving ? `
                                            <button onclick="startMoving(${d.id})" class="btn btn-warning py-3 rounded-pill fw-bold shadow-sm">
                                                <i class="bi bi-play-fill me-1"></i> MULAI PERJALANAN
                                            </button>` : `
                                            <button onclick="openModalComplete(${d.id})" class="btn btn-success py-3 rounded-pill fw-bold shadow-sm">
                                                <i class="bi bi-camera me-1"></i> KONFIRMASI SAMPAI
                                            </button>`
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                }
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<div class="col-12 text-center text-danger">Gagal memuat data aktif.</div>';
            });
    }

    // --- FUNGSI MODAL BARANG ---
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

    // --- FUNGSI UPDATE STATUS ---
    function startMoving(id) {
        Swal.fire({
            title: 'Mulai Pengiriman?',
            text: "Status akan berubah menjadi Dalam Perjalanan.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ya, Berangkat!'
        }).then(res => {
            if(res.isConfirmed) {
                axios.post(`/api/deliveries/start/${id}`).then(() => {
                    Swal.fire('Hati-hati di jalan!', 'Status diperbarui.', 'success');
                    fetchActive();
                });
            }
        });
    }

    function openModalComplete(id) {
        selectedId = id;
        document.getElementById('imagePreview').classList.add('d-none');
        document.getElementById('placeholderUI').classList.remove('d-none');
        document.getElementById('btnComplete').disabled = true;
        document.getElementById('proofInput').value = "";
        new bootstrap.Modal(document.getElementById('modalProof')).show();
    }

    function handlePreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('imagePreview');
                img.src = e.target.result;
                img.classList.remove('d-none');
                document.getElementById('placeholderUI').classList.add('d-none');
                document.getElementById('btnComplete').disabled = false;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitComplete() {
        const fileInput = document.getElementById('proofInput');
        const btn = document.getElementById('btnComplete');

        if (!fileInput.files[0]) return;

        const formData = new FormData();
        formData.append('image', fileInput.files[0]);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

        axios.post(`/api/deliveries/complete/${selectedId}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(() => {
            Swal.fire('Selesai!', 'Pengiriman telah sukses diselesaikan.', 'success')
                .then(() => window.location.href = '/courier/history');
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Selesaikan Pengiriman';
            Swal.fire('Gagal', 'Gagal mengunggah bukti foto.', 'error');
        });
    }

    document.addEventListener('DOMContentLoaded', fetchActive);
</script>

<style>
    .border-dashed { border-style: dashed !important; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .italic { font-style: italic; }
</style>
@endsection