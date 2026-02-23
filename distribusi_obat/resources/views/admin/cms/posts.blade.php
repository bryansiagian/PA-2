@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold">Konten Berita & Kegiatan</h4>
            <p class="text-muted small mb-0">Kelola publikasi informasi terbaru untuk Yayasan E-Pharma.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPost">
            <i class="bi bi-plus-lg me-2"></i> Buat Post Baru
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small fw-bold text-muted">
                        <th class="ps-4 py-3">TANGGAL</th>
                        <th>JUDUL POSTINGAN</th>
                        <th>KATEGORI</th>
                        <th>STATUS</th>
                        <th class="text-center pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody id="postTableBody">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Memuat database konten...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT POST -->
<div class="modal fade" id="modalPost" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalPostLabel">Buat Konten Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="postForm" onsubmit="savePost(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="post_id" name="id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold small text-muted">Judul Postingan</label>
                            <input type="text" name="title" id="post_title" class="form-control border-0 bg-light py-2" placeholder="Masukkan judul menarik..." required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-muted">Kategori</label>
                            <select name="post_category_id" id="post_category_id" class="form-select border-0 bg-light py-2" required>
                                <option value="" selected disabled>Pilih Kategori</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Isi Konten</label>
                        <textarea name="content" id="post_content" class="form-control border-0 bg-light py-2" rows="6" required></textarea>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Gambar Cover</label>
                            <input type="file" name="image" class="form-control border-0 bg-light py-2" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Status Publikasi</label>
                            <select name="status" id="post_status" class="form-select border-0 bg-light py-2">
                                <option value="0">Simpan sebagai Draft</option>
                                <option value="1">Langsung Publikasikan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-white text-muted" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSavePost" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Postingan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function initPostsPage() {
        // Load Kategori untuk Select
        axios.get('/api/post-categories').then(res => {
            const select = document.getElementById('post_category_id');
            res.data.forEach(cat => {
                select.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
            });
            loadPosts();
        });
    }

    function loadPosts() {
        const tableBody = document.getElementById('postTableBody');
        axios.get('/api/cms/posts').then(res => {
            let html = '';
            res.data.forEach(p => {
                const date = new Date(p.created_at).toLocaleDateString('id-ID');
                const statusBadge = p.status == 1 ? 'bg-success' : 'bg-warning text-dark';
                const statusText = p.status == 1 ? 'PUBLISHED' : 'DRAFT';
                const catName = p.category ? p.category.name : 'Uncategorized';

                html += `
                <tr class="border-bottom">
                    <td class="ps-4 small text-muted">${date}</td>
                    <td>
                        <div class="fw-bold text-dark">${p.title}</div>
                        <small class="text-muted">Oleh: ${p.author ? p.author.name : 'Admin'}</small>
                    </td>
                    <td><span class="badge bg-light text-primary border px-3">${catName}</span></td>
                    <td><span class="badge ${statusBadge} rounded-pill px-3" style="font-size: 9px;">${statusText}</span></td>
                    <td class="text-center pe-4">
                        <button onclick="editPost(${p.id})" class="btn btn-sm btn-light rounded-circle text-primary me-1 shadow-sm"><i class="bi bi-pencil"></i></button>
                        <button onclick="deletePost(${p.id}, '${p.title}')" class="btn btn-sm btn-light rounded-circle text-danger shadow-sm"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
            tableBody.innerHTML = html || '<tr><td colspan="5" class="text-center py-4">Belum ada konten.</td></tr>';
        });
    }

    function savePost(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSavePost');
        const formData = new FormData(e.target);
        const postId = document.getElementById('post_id').value;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        // Jika ada post_id, gunakan update (PUT), jika tidak store (POST)
        // Note: Laravel FormData update harus POST dengan _method=PUT
        let url = '/api/cms/posts';
        if (postId) {
            url = `/api/cms/posts/${postId}`;
            formData.append('_method', 'PUT');
        }

        axios.post(url, formData).then(res => {
            bootstrap.Modal.getInstance(document.getElementById('modalPost')).hide();
            Swal.fire('Berhasil', 'Konten telah diperbarui', 'success');
            document.getElementById('postForm').reset();
            document.getElementById('post_id').value = '';
            loadPosts();
        }).catch(err => {
            Swal.fire('Gagal', err.response.data.message || 'Cek kembali inputan Anda', 'error');
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Simpan Postingan';
        });
    }

    function deletePost(id, title) {
        Swal.fire({
            title: 'Hapus Postingan?',
            text: `"${title}" akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then(res => {
            if(res.isConfirmed) {
                axios.delete(`/api/cms/posts/${id}`).then(() => {
                    Swal.fire('Terhapus', 'Konten telah dihapus.', 'success');
                    loadPosts();
                });
            }
        });
    }

    // Tambahan fungsi edit untuk mengisi modal
    function editPost(id) {
        axios.get(`/api/cms/posts/${id}`).then(res => {
            const p = res.data;
            document.getElementById('post_id').value = p.id;
            document.getElementById('post_title').value = p.title;
            document.getElementById('post_category_id').value = p.post_category_id;
            document.getElementById('post_content').value = p.content;
            document.getElementById('post_status').value = p.status;
            document.getElementById('modalPostLabel').innerText = 'Edit Postingan';
            new bootstrap.Modal(document.getElementById('modalPost')).show();
        });
    }

    document.addEventListener('DOMContentLoaded', initPostsPage);
</script>
@endsection