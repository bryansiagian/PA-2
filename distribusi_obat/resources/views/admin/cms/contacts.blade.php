@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Kelola Pengguna Sistem</h4>
            <div class="text-muted">Manajemen akun administrator, operator, dan kurir</div>
        </div>
        <div class="ms-3">
            <button class="btn btn-indigo shadow-sm" onclick="openAddModal()">
                <i class="ph-user-plus me-2"></i> Tambah Pengguna
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-users-three me-2 text-primary"></i>Daftar Akun Terdaftar</h5>
            <div class="ms-auto">
                <span class="badge bg-indigo text-white fw-bold px-3 shadow-sm">
                    <i class="ph-shield-check me-1"></i> Akses Kontrol Aktif
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">Nama & Email</th>
                        <th>Role / Jabatan</th>
                        <th>Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-muted me-2"></div>
                            Sinkronisasi data pengguna...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT USER (Limitless Style) -->
<div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold" id="modalTitle"><i class="ph-user-plus me-2"></i>Tambah Pengguna Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm" onsubmit="saveUser(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="user_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Lengkap</label>
                        <div class="form-control-feedback form-control-feedback-start">
                            <input type="text" name="name" id="name" class="form-control border-light-subtle" placeholder="Contoh: Budi Santoso" required>
                            <div class="form-control-feedback-icon"><i class="ph-user text-muted"></i></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Alamat Email</label>
                        <div class="form-control-feedback form-control-feedback-start">
                            <input type="email" name="email" id="email" class="form-control border-light-subtle" placeholder="nama@e-pharma.org" required>
                            <div class="form-control-feedback-icon"><i class="ph-envelope text-muted"></i></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Role / Hak Akses</label>
                        <select name="role" id="role" class="form-select border-light-subtle" required>
                            <option value="" selected disabled>Pilih Role</option>
                            <option value="admin">Administrator</option>
                            <option value="operator">Operator Gudang</option>
                            <option value="courier">Kurir Logistik</option>
                        </select>
                    </div>

                    <div id="passwordArea">
                        <div class="mb-0">
                            <label class="form-label fw-bold small">Password</label>
                            <input type="password" name="password" id="password" class="form-control border-light-subtle" placeholder="Minimal 8 karakter">
                            <div class="form-text fs-xs text-muted" id="passwordHelp">Kosongkan jika tidak ingin mengubah password.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSave" class="btn btn-indigo px-4 fw-bold">
                        <i class="ph-check-circle me-2"></i>SIMPAN PENGGUNA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchUsers() {
        const tableBody = document.getElementById('userTableBody');
        axios.get('/api/admin/users').then(res => {
            let html = '';
            res.data.forEach(u => {
                const roleName = u.roles[0] ? u.roles[0].name.toUpperCase() : 'USER';
                html += `
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-2 rounded-circle me-3">
                                <i class="ph-user text-indigo fs-base"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">${u.name}</div>
                                <div class="fs-xs text-muted">${u.email}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-indigo bg-opacity-10 text-indigo fw-bold px-2 py-1">${roleName}</span></td>
                    <td><span class="badge bg-success text-white fw-bold px-2 py-1 shadow-sm fs-xs">AKTIF</span></td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex">
                            <button onclick="editUser(${u.id})" class="btn btn-sm btn-light text-primary border-0 me-2" title="Edit">
                                <i class="ph-note-pencil"></i>
                            </button>
                            <button onclick="deleteUser(${u.id}, '${u.name}')" class="btn btn-sm btn-light text-danger border-0" title="Hapus">
                                <i class="ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
            tableBody.innerHTML = html || '<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data pengguna.</td></tr>';
        });
    }

    function openAddModal() {
        document.getElementById('userForm').reset();
        document.getElementById('user_id').value = '';
        document.getElementById('passwordHelp').classList.add('d-none');
        document.getElementById('password').required = true;
        document.getElementById('modalTitle').innerHTML = '<i class="ph-user-plus me-2"></i>Tambah Pengguna Baru';
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    }

    function editUser(id) {
        axios.get(`/api/admin/users/${id}`).then(res => {
            const u = res.data;
            document.getElementById('user_id').value = u.id;
            document.getElementById('name').value = u.name;
            document.getElementById('email').value = u.email;
            document.getElementById('role').value = u.roles[0] ? u.roles[0].name : '';
            document.getElementById('password').required = false;
            document.getElementById('passwordHelp').classList.remove('d-none');
            document.getElementById('modalTitle').innerHTML = '<i class="ph-note-pencil me-2"></i>Edit Pengguna';
            new bootstrap.Modal(document.getElementById('modalUser')).show();
        });
    }

    function saveUser(e) {
        e.preventDefault();
        const id = document.getElementById('user_id').value;
        const btn = document.getElementById('btnSave');
        const formData = new FormData(e.target);

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        if(id) formData.append('_method', 'PUT');
        const url = id ? `/api/admin/users/${id}` : '/api/admin/users';

        axios.post(url, formData).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalUser')).hide();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data pengguna diperbarui', confirmButtonColor: '#5c68e2' });
            fetchUsers();
        }).catch(err => {
            Swal.fire('Gagal', err.response.data.message || 'Cek kembali inputan', 'error');
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph-check-circle me-2"></i>SIMPAN PENGGUNA';
        });
    }

    function deleteUser(id, name) {
        Swal.fire({
            title: 'Hapus Akun?',
            text: `Akun "${name}" akan dihapus secara permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus'
        }).then(res => {
            if(res.isConfirmed) {
                axios.delete(`/api/admin/users/${id}`).then(() => fetchUsers());
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchUsers);
</script>

<style>
    .bg-indigo { background-color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }
    .text-indigo { color: #5c68e2 !important; }

    .card { border-radius: 0.5rem; }
    .table td { padding: 0.75rem 1rem; }

    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
