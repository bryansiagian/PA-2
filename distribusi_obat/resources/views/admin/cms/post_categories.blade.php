@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Manajemen Kategori Post</h4>
            <p class="text-muted small mb-0">Kelola kategori untuk mengelompokkan Berita dan Kegiatan Yayasan.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-2"></i> Tambah Kategori
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small fw-bold">
                        <th class="ps-4 py-3">NAMA KATEGORI</th>
                        <th>JUMLAH POSTINGAN</th>
                        <th>DIBUAT OLEH</th>
                        <th class="text-center pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    <tr><td colspan="4" class="text-center py-5 text-muted">Memuat data kategori...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT -->
<div class="modal fade" id="modalCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">Kategori Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="cat_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nama Kategori</label>
                    <input type="text" id="cat_name" class="form-control border-0 bg-light py-2" placeholder="Contoh: Berita Utama" required>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Batal</button>
                <button onclick="saveCategory()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Simpan Kategori</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchCategories() {
        axios.get('/api/cms/post-categories').then(res => {
            let html = '';
            res.data.forEach(cat => {
                // Ambil posts_count (hasil dari withCount di Laravel)
                const jumlahPost = cat.posts_count || 0;

                html += `
                <tr class="border-bottom">
                    <td class="ps-4 py-3 fw-bold text-dark">${cat.name}</td>
                    <td>
                        <span class="badge bg-light text-primary border px-3">
                            <i class="bi bi-file-text me-1"></i> ${jumlahPost} Postingan
                        </span>
                    </td>
                    <td><small class="text-muted">Admin</small></td>
                    <td class="text-center pe-4">
                        <button onclick="openEditModal(${cat.id}, '${cat.name}')" class="btn btn-sm btn-light rounded-circle text-primary me-1 shadow-sm"><i class="bi bi-pencil"></i></button>
                        <button onclick="deleteCategory(${cat.id}, '${cat.name}')" class="btn btn-sm btn-light rounded-circle text-danger shadow-sm"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
            document.getElementById('categoryTableBody').innerHTML = html || '<tr><td colspan="4" class="text-center py-4">Belum ada kategori.</td></tr>';
        });
    }

    function openAddModal() {
        document.getElementById('cat_id').value = '';
        document.getElementById('cat_name').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Kategori Baru';
        new bootstrap.Modal(document.getElementById('modalCategory')).show();
    }

    function openEditModal(id, name) {
        document.getElementById('cat_id').value = id;
        document.getElementById('cat_name').value = name;
        document.getElementById('modalTitle').innerText = 'Edit Kategori';
        new bootstrap.Modal(document.getElementById('modalCategory')).show();
    }

    function saveCategory() {
        const id = document.getElementById('cat_id').value;
        const name = document.getElementById('cat_name').value;
        if(!name) return Swal.fire('Error', 'Nama harus diisi', 'error');

        const request = id ? axios.put(`/api/cms/post-categories/${id}`, { name }) : axios.post('/api/cms/post-categories', { name });

        request.then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalCategory')).hide();
            Swal.fire('Berhasil!', 'Data kategori tersimpan.', 'success');
            fetchCategories();
        }).catch(err => Swal.fire('Error', err.response.data.message, 'error'));
    }

    function deleteCategory(id, name) {
        Swal.fire({
            title: 'Hapus Kategori?',
            text: `Kategori "${name}" akan dihapus.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33'
        }).then(res => {
            if(res.isConfirmed) {
                axios.delete(`/api/cms/post-categories/${id}`).then(() => {
                    Swal.fire('Terhapus', 'Kategori telah dihapus.', 'success');
                    fetchCategories();
                }).catch(err => Swal.fire('Gagal', err.response.data.message, 'error'));
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchCategories);
</script>
@endsection