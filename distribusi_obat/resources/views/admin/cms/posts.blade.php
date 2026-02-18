@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Manajemen Berita & Kegiatan</h4>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalPost">
            <i class="bi bi-plus-lg me-2"></i> Tambah Konten
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small fw-bold">
                        <th class="ps-4">TANGGAL</th>
                        <th>JUDUL</th>
                        <th>TIPE</th>
                        <th class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody id="postTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH POST -->
<div class="modal fade" id="modalPost" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold">Buat Konten Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="postForm" onsubmit="savePost(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Judul</label>
                        <input type="text" name="title" class="form-control border-0 bg-light" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tipe Konten</label>
                        <select name="type" class="form-select border-0 bg-light">
                            <option value="news">Berita</option>
                            <option value="activity">Kegiatan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Isi Berita</label>
                        <textarea name="content" class="form-control border-0 bg-light" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Gambar Cover</label>
                        <input type="file" name="image" class="form-control border-0 bg-light" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="submit" id="btnSavePost" class="btn btn-primary w-100 rounded-pill py-2">Publikasikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function loadPosts() {
        axios.get('/api/cms/posts').then(res => {
            let html = '';
            res.data.forEach(p => {
                html += `
                <tr class="border-bottom">
                    <td class="ps-4 small text-muted">${new Date(p.created_at).toLocaleDateString()}</td>
                    <td class="fw-bold">${p.title}</td>
                    <td><span class="badge bg-light text-primary border px-3">${p.type.toUpperCase()}</span></td>
                    <td class="text-center">
                        <button onclick="deletePost(${p.id})" class="btn btn-light btn-sm rounded-circle text-danger"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
            document.getElementById('postTableBody').innerHTML = html || '<tr><td colspan="4" class="text-center py-4">Kosong</td></tr>';
        });
    }

    function savePost(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSavePost');
        const formData = new FormData(e.target);

        btn.disabled = true;
        axios.post('/api/cms/posts', formData).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalPost')).hide();
            Swal.fire('Berhasil!', 'Berita telah diupload.', 'success');
            loadPosts();
        }).finally(() => btn.disabled = false);
    }

    function deletePost(id) {
        if(confirm('Hapus berita ini?')) {
            axios.delete(`/api/cms/posts/${id}`).then(() => loadPosts());
        }
    }

    document.addEventListener('DOMContentLoaded', loadPosts);
</script>
@endsection