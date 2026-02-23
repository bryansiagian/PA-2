@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold">Manajemen Galeri Media</h4>
            <p class="text-muted small mb-0">Kelola album foto dan video kegiatan yayasan.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGallery">
            <i class="bi bi-plus-lg me-2"></i> Buat Album Baru
        </button>
    </div>

    <div id="galleryList" class="row g-4">
        <!-- Render via JS -->
    </div>
</div>

<!-- MODAL TAMBAH GALERI -->
<div class="modal fade" id="modalGallery" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold">Album Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="galleryForm" onsubmit="saveGallery(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold">Nama Album / Kegiatan</label>
                        <input type="text" name="title" class="form-control border-0 bg-light" placeholder="Contoh: Dokumentasi Baksos 2024" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Unggah Foto (Bisa pilih banyak)</label>
                        <input type="file" name="files[]" class="form-control border-0 bg-light" accept="image/*" multiple required>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="submit" id="btnSave" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Simpan Album</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchGalleries() {
        axios.get('/api/cms/galleries').then(res => {
            let html = '';
            res.data.forEach(g => {
                const cover = g.files.length > 0 ? `/${g.files[0].file_path}` : 'https://placehold.co/400x300?text=No+Media';
                html += `
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <img src="${cover}" class="card-img-top" style="height:200px; object-fit:cover">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">${g.title}</h6>
                            <small class="text-muted d-block mb-3">${g.files.length} Media Terlampir</small>
                            <div class="d-flex justify-content-between">
                                <button onclick="deleteGallery(${g.id})" class="btn btn-sm btn-light text-danger rounded-pill px-3">Hapus</button>
                                <span class="small text-muted">${new Date(g.created_at).toLocaleDateString()}</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            document.getElementById('galleryList').innerHTML = html || '<div class="col-12 text-center py-5">Belum ada album galeri.</div>';
        });
    }

    function saveGallery(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSave');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

        axios.post('/api/cms/galleries', new FormData(e.target))
            .then(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalGallery')).hide();
                Swal.fire('Berhasil', 'Album telah dipublikasikan', 'success');
                fetchGalleries();
                e.target.reset();
            }).finally(() => btn.disabled = false);
    }

    function deleteGallery(id) {
        Swal.fire({ title: 'Hapus Album?', icon: 'warning', showCancelButton: true }).then(res => {
            if(res.isConfirmed) axios.delete(`/api/cms/galleries/${id}`).then(() => fetchGalleries());
        });
    }

    document.addEventListener('DOMContentLoaded', fetchGalleries);
</script>
@endsection