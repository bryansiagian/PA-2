@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Tugas Pengantaran Aktif</h4>
            <p class="text-muted small mb-0">Selesaikan pengiriman dan unggah bukti penerimaan.</p>
        </div>
        <button onclick="fetchActive()" class="btn btn-white border shadow-sm rounded-pill px-3">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>

    <div id="activeList" class="row g-3">
        <div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>
</div>

<!-- MODAL KONFIRMASI SELESAI (REVISI DOSEN #5) -->
<div class="modal fade" id="modalProof" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0"><h5 class="fw-bold">Penyelesaian Pengiriman</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <!-- FORM DATA PENERIMA -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Penerima Paket</label>
                    <input type="text" id="receiver_name" class="form-control border-0 bg-light py-2" placeholder="Nama Lengkap Penerima" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Hubungan Penerima</label>
                    <select id="receiver_relation" class="form-select border-0 bg-light py-2">
                        <option value="Staff">Staff/Pegawai RS</option>
                        <option value="Ybs">Yang Bersangkutan</option>
                        <option value="Security">Keamanan/Security</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <!-- AREA FOTO -->
                <div id="uploadArea" class="rounded-4 p-4 mb-3 border-2 border-dashed d-flex flex-column align-items-center justify-content-center"
                     style="height: 180px; cursor: pointer; background-color: #f8f9fa;" onclick="document.getElementById('proofInput').click()">
                    <div id="placeholderUI"><i class="bi bi-camera-fill fs-1 text-primary"></i><p class="mb-0 small fw-bold">Ambil Foto Bukti</p></div>
                    <img id="imagePreview" src="#" class="img-fluid rounded-4 d-none" style="max-height: 100%; object-fit: cover;">
                </div>

                <input type="file" id="proofInput" accept="image/*" capture="environment" class="d-none" onchange="handlePreview(this)">
                <div class="d-grid"><button id="btnComplete" onclick="submitComplete()" class="btn btn-success py-3 rounded-pill fw-bold shadow-sm" disabled>Konfirmasi & Selesaikan</button></div>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
    let selectedId = null;

    function fetchActive() {
        axios.get('/api/deliveries/active').then(res => {
            let html = '';
            if (res.data.length === 0) {
                html = '<div class="col-12 text-center py-5 bg-white rounded-4 border"><p class="text-muted small">Tidak ada tugas aktif.</p></div>';
            } else {
                res.data.forEach(d => {
                    const isMoving = d.status === 'in_transit';
                    html += `
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 ${isMoving ? 'border-warning' : 'border-info'}">
                            <div class="card-body p-4">
                                <span class="badge ${isMoving ? 'bg-warning text-dark' : 'bg-info text-white'} rounded-pill mb-3">${d.status.toUpperCase()}</span>
                                <h5 class="fw-bold text-dark">${d.request.user.name}</h5>
                                <p class="text-muted small mb-4"><i class="bi bi-geo-alt"></i> ${d.request.user.address}</p>
                                <div class="d-grid gap-2">
                                    ${!isMoving ? `<button onclick="startMoving(${d.id})" class="btn btn-warning py-2 rounded-pill fw-bold">MULAI JALAN</button>` :
                                    `<button onclick="openModalComplete(${d.id})" class="btn btn-success py-2 rounded-pill fw-bold shadow-sm">KONFIRMASI SAMPAI</button>`}
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
            }
            document.getElementById('activeList').innerHTML = html;
        });
    }

    function startMoving(id) {
        axios.post(`/api/deliveries/start/${id}`).then(() => { Swal.fire('Hati-hati!', 'Status: Dalam Perjalanan.', 'success'); fetchActive(); });
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

        document.getElementById('btnComplete').disabled = true;
        axios.post(`/api/deliveries/complete/${selectedId}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
            .then(() => { Swal.fire('Sukses!', 'Pengiriman selesai.', 'success').then(() => window.location.href = '/courier/history'); })
            .catch(() => { document.getElementById('btnComplete').disabled = false; });
    }

    document.addEventListener('DOMContentLoaded', fetchActive);
</script>
@endsection