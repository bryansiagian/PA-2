@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Manajemen Kategori Obat</h4>
            <p class="text-muted small mb-0">Kelola pengelompokan jenis obat untuk katalog.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-2"></i> Kategori Baru
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Nama Kategori</th>
                        <th>Jumlah Produk</th>
                        <th>Dibuat Pada</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    <tr><td colspan="4" class="text-center py-5">Memuat kategori...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT KATEGORI -->
<div class="modal fade" id="modalCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Kategori</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="categoryForm">
                    <input type="hidden" id="cat_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Kategori</label>
                        <input type="text" id="cat_name" class="form-control border-0 bg-light py-2" placeholder="Contoh: Antibiotik" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-white text-muted" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnSave" onclick="saveCategory()" class="btn btn-primary rounded-pill px-4">Simpan Kategori</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchCategories() {
        axios.get('/api/categories').then(res => {
            let html = '';
            res.data.forEach(cat => {
                html += `
                <tr class="border-bottom">
                    <td class="ps-4 py-3 fw-bold text-dark">${cat.name}</td>
                    <td><span class="badge bg-light text-primary border">${cat.drugs_count} Produk</span></td>
                    <td><small class="text-muted">${new Date(cat.created_at).toLocaleDateString('id-ID')}</small></td>
                    <td class="text-center pe-4">
                        <button onclick="openEditModal(${cat.id}, '${cat.name}')" class="btn btn-light btn-sm rounded-circle text-primary me-1 shadow-sm"><i class="bi bi-pencil"></i></button>
                        <button onclick="confirmDelete(${cat.id}, '${cat.name}')" class="btn btn-light btn-sm rounded-circle text-danger shadow-sm"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
            document.getElementById('categoryTableBody').innerHTML = html || '<tr><td colspan="4" class="text-center py-4">Kosong</td></tr>';
        });
    }

    function openAddModal() {
        document.getElementById('cat_id').value = '';
        document.getElementById('cat_name').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Kategori';
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

        if(!name) return Swal.fire('Error', 'Nama kategori wajib diisi', 'error');

        const request = id ? axios.put(`/api/categories/${id}`, { name }) : axios.post('/api/categories', { name });

        request.then(res => {
            bootstrap.Modal.getInstance(document.getElementById('modalCategory')).hide();
            Swal.fire('Berhasil!', res.data.message, 'success');
            fetchCategories();
        }).catch(err => Swal.fire('Gagal', err.response.data.message || 'Error simpan', 'error'));
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Kategori?',
            text: `Kategori "${name}" akan dihapus.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33'
        }).then(result => {
            if(result.isConfirmed) {
                axios.delete(`/api/categories/${id}`).then(res => {
                    Swal.fire('Terhapus!', res.data.message, 'success');
                    fetchCategories();
                }).catch(err => Swal.fire('Gagal', err.response.data.message, 'error'));
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchCategories);
</script>
@endsection