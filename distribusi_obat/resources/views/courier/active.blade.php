@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Tugas Pengantaran Aktif</h4>
            <div class="text-muted">Selesaikan pengiriman dan unggah bukti penerimaan di lokasi</div>
        </div>
        <div class="ms-3">
            <button onclick="fetchActive()" class="btn btn-light btn-icon shadow-sm rounded-circle" title="Refresh Tugas">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Active Tasks Container -->
    <div id="activeList" class="row g-3">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-indigo spinner-border-sm" role="status"></div>
            <span class="ms-2 text-muted small fw-bold">Memuat data pengantaran...</span>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: KONFIRMASI SELESAI (Limitless Style)
     ========================================== -->
<div class="modal fade" id="modalProof" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-check-square-offset me-2"></i>Penyelesaian Pengiriman</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- FORM DATA PENERIMA -->
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nama Penerima Paket</label>
                    <input type="text" id="receiver_name" class="form-control border-light-subtle" placeholder="Siapa yang menerima paket?" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Hubungan Penerima</label>
                    <select id="receiver_relation" class="form-select border-light-subtle">
                        <option value="Staff">Staff / Pegawai RS</option>
                        <option value="Ybs">Yang Bersangkutan (Pasien)</option>
                        <option value="Security">Keamanan / Security</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <!-- AREA FOTO BUKTI -->
                <label class="form-label fw-bold small">Foto Bukti Penerimaan</label>
                <div id="uploadArea" class="rounded-3 p-4 mb-3 border-2 border-dashed d-flex flex-column align-items-center justify-content-center bg-light"
                     style="height: 200px; cursor: pointer;" onclick="document.getElementById('proofInput').click()">

                    <div id="placeholderUI" class="text-center">
                        <i class="ph-camera text-indigo" style="font-size: 3rem;"></i>
                        <p class="mb-0 small fw-bold text-muted">Ketuk untuk Ambil Foto</p>
                        <span class="fs-xs text-muted">Pastikan wajah penerima atau paket terlihat jelas</span>
                    </div>

                    <img id="imagePreview" src="#" class="img-fluid rounded-3 d-none" style="max-height: 100%; object-fit: cover;">
                </div>

                <input type="file" id="proofInput" accept="image/*" capture="environment" class="d-none" onchange="handlePreview(this)">

                <div class="d-grid mt-4">
                    <button id="btnComplete" onclick="submitComplete()" class="btn btn-indigo py-2 fw-bold shadow-sm rounded-pill" disabled>
                        KONFIRMASI & SELESAIKAN
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
    let selectedId = null;

    function fetchActive() {
        const container = document.getElementById('activeList');
        axios.get('/api/deliveries/active').then(res => {
            let html = '';
            if (res.data.length === 0) {
                html = `
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3 py-5 text-center border-2 border-dashed border-light">
                        <div class="card-body">
                            <i class="ph-bicycle ph-4x text-muted opacity-25 mb-3"></i>
                            <h5 class="fw-bold text-muted">Tidak Ada Tugas Aktif</h5>
                            <p class="text-muted">Ambil tugas baru di menu Bursa Tugas.</p>
                        </div>
                    </div>
                </div>`;
            } else {
                res.data.forEach(d => {
                    const isMoving = d.status === 'in_transit';
                    const statusBadge = isMoving
                        ? '<span class="badge bg-warning bg-opacity-10 text-warning fw-bold px-2 py-1"><i class="ph-truck me-1"></i>DALAM PERJALANAN</span>'
                        : '<span class="badge bg-info bg-opacity-10 text-info fw-bold px-2 py-1"><i class="ph-package me-1"></i>SIAP DIANTAR</span>';

                    html += `
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 h-100 border-top border-4 ${isMoving ? 'border-warning' : 'border-info'}">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted font-monospace fs-xs fw-bold">#${d.tracking_number}</span>
                                    ${statusBadge}
                                </div>
                                <h5 class="fw-bold text-dark mb-1">${d.request.user.name}</h5>
                                <div class="small text-muted mb-4 d-flex align-items-start">
                                    <i class="ph-map-pin text-danger me-2 mt-1"></i>
                                    <span>${d.request.user.address}</span>
                                </div>
                                <div class="d-grid">
                                    ${!isMoving ? `
                                        <button onclick="startMoving(${d.id})" class="btn btn-warning text-dark fw-bold py-2 rounded-pill shadow-sm">
                                            <i class="ph-navigation-arrow me-2"></i>MULAI JALAN SEKARANG
                                        </button>` : `
                                        <button onclick="openModalComplete(${d.id})" class="btn btn-indigo fw-bold py-2 rounded-pill shadow-sm">
                                            <i class="ph-check-circle me-2"></i>KONFIRMASI SAMPAI
                                        </button>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
            }
            container.innerHTML = html;
        });
    }

    function startMoving(id) {
        axios.post(`/api/deliveries/start/${id}`).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Hati-hati di Jalan!',
                text: 'Status pengiriman kini: Dalam Perjalanan.',
                confirmButtonColor: '#5c68e2'
            });
            fetchActive();
        });
    }

    function openModalComplete(id) {
        selectedId = id;
        document.getElementById('imagePreview').classList.add('d-none');
        document.getElementById('placeholderUI').classList.remove('d-none');
        document.getElementById('btnComplete').disabled = true;
        document.getElementById('proofInput').value = "";
        document.getElementById('receiver_name').value = "";
        new bootstrap.Modal(document.getElementById('modalProof')).show();
    }

    function handlePreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.getElementById('imagePreview');
                img.src = e.target.result;
                img.classList.remove('d-none');
                document.getElementById('placeholderUI').classList.add('d-none');
                document.getElementById('btnComplete').disabled = false;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitComplete() {
        const name = document.getElementById('receiver_name').value;
        const relation = document.getElementById('receiver_relation').value;
        const fileInput = document.getElementById('proofInput');

        if (!name) return Swal.fire('Error', 'Nama penerima wajib diisi', 'error');

        const formData = new FormData();
        formData.append('image', fileInput.files[0]);
        formData.append('receiver_name', name);
        formData.append('receiver_relation', relation);

        const btn = document.getElementById('btnComplete');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Menyimpan...';

        axios.post(`/api/deliveries/complete/${selectedId}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Pengiriman Selesai!',
                    text: 'Terima kasih telah menjalankan tugas.',
                    confirmButtonColor: '#5c68e2'
                }).then(() => window.location.href = '/courier/history');
            })
            .catch(() => { btn.disabled = false; btn.innerHTML = 'KONFIRMASI & SELESAIKAN'; });
    }

    document.addEventListener('DOMContentLoaded', fetchActive);
</script>

<style>
    /* Styling Dasar Limitless */
    .bg-indigo { background-color: #5c68e2 !important; }
    .text-indigo { color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }

    .card { border-radius: 0.5rem; transition: all 0.2s ease; }
    .ph-4x { font-size: 4rem; }
    .fs-xs { font-size: 0.75rem; }
    .font-monospace { font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

    .border-dashed { border-style: dashed !important; border-color: #cbd5e1 !important; }

    /* Animasi Spinner */
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
