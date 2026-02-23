<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Daftar Pengguna Aktif</h4>
            <p class="text-muted small mb-0">Manajemen akun Operator, Customer, dan Kurir.</p>
        </div>
        <a href="/admin/users/pending" class="btn btn-warning rounded-pill px-4 shadow-sm text-white fw-bold">
            <i class="bi bi-person-check-fill me-2"></i> Verifikasi Pendaftaran
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small fw-bold">
                        <th class="ps-4 py-3">NAMA PENGGUNA</th>
                        <th>EMAIL ADDRESS</th>
                        <th>ROLE / HAK AKSES</th>
                        <th>TANGGAL BERGABUNG</th>
                        <th class="text-center pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Memproses data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EDIT ROLE -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold">Ubah Akses Pengguna</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="edit_user_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nama Pengguna</label>
                    <input type="text" id="edit_user_name" class="form-control bg-light border-0" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Pilih Role Baru</label>
                    <select id="edit_user_role" class="form-select border-0 bg-light py-2">
                        <!-- Data Role dimuat via JS -->
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light text-end">
                <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Batal</button>
                <button onclick="updateUser()" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '<?php echo e(session('api_token')); ?>';

    function loadUsers() {
        const tableBody = document.getElementById('userTableBody');

        axios.get('/api/users')
            .then(res => {
                const users = res.data;
                let html = '';

                if (!users || users.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted italic">Tidak ada data pengguna aktif ditemukan.</td></tr>';
                    return;
                }

                users.forEach(u => {
                    // CARA SPATIE: roles adalah array. Ambil data pertama jika ada.
                    const roleName = u.roles.length > 0 ? u.roles[0].name : 'no role';
                    // Kita juga simpan roleId untuk kebutuhan fungsi openEdit
                    const roleId = u.roles.length > 0 ? u.roles[0].id : '';

                    const dateJoined = new Date(u.created_at).toLocaleDateString('id-ID', {
                        day: '2-digit', month: 'short', year: 'numeric'
                    });

                    html += `
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=0D6EFD&color=fff" class="rounded-circle me-3" width="35">
                                <span class="fw-bold text-dark">${u.name}</span>
                            </div>
                        </td>
                        <td><small class="text-muted">${u.email}</small></td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 rounded-pill" style="font-size: 10px;">
                                ${roleName.toUpperCase()}
                            </span>
                        </td>
                        <td><small class="text-muted">${dateJoined}</small></td>
                        <td class="text-center pe-4">
                            <button onclick="openEdit(${u.id}, '${u.name}', '${roleId}')" class="btn btn-light btn-sm rounded-circle shadow-sm me-1 text-primary">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button onclick="deleteUser(${u.id}, '${u.name}')" class="btn btn-light btn-sm rounded-circle shadow-sm text-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });

                tableBody.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger">Gagal memuat data. Sesi mungkin berakhir.</td></tr>';
            });
    }

    function openEdit(id, name, currentRoleId) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_user_name').value = name;

        axios.get('/api/roles').then(res => {
            let opt = '<option value="" disabled>-- Pilih Role --</option>';
            res.data.forEach(r => {
                opt += `<option value="${r.id}" ${r.id == currentRoleId ? 'selected' : ''}>${r.name.toUpperCase()}</option>`;
            });
            document.getElementById('edit_user_role').innerHTML = opt;
            new bootstrap.Modal(document.getElementById('modalEditUser')).show();
        });
    }

    function updateUser() {
        const id = document.getElementById('edit_user_id').value;
        const roleId = document.getElementById('edit_user_role').value;

        axios.put(`/api/users/${id}`, { role_id: roleId })
            .then(res => {
                Swal.fire('Berhasil!', 'Hak akses telah diperbarui.', 'success');
                bootstrap.Modal.getInstance(document.getElementById('modalEditUser')).hide();
                loadUsers();
            })
            .catch(err => Swal.fire('Error', 'Gagal mengubah role', 'error'));
    }

    function deleteUser(id, name) {
        Swal.fire({
            title: 'Hapus Akun?',
            text: `Akun ${name} akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus!'
        }).then(result => {
            if(result.isConfirmed) {
                axios.delete(`/api/users/${id}`).then(() => {
                    Swal.fire('Terhapus', 'User berhasil dihapus.', 'success');
                    loadUsers();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', loadUsers);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.backoffice', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PA-2\manajemen_obat\resources\views/admin/users.blade.php ENDPATH**/ ?>