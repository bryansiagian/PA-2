<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="mb-4">
        <a href="/admin/users" class="btn btn-sm btn-light border rounded-pill px-3 mb-2 text-decoration-none shadow-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar User
        </a>
        <h4 class="fw-bold text-dark mt-2">Persetujuan Akun Baru</h4>
        <p class="text-muted small">Tinjau profil lengkap calon pengguna sebelum memberikan akses sistem.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-warning bg-opacity-10 text-warning">
                    <tr class="small fw-bold">
                        <th class="ps-4 py-3">CALON PENGGUNA</th>
                        <th>ROLE PENGAJUAN</th>
                        <th>TANGGAL DAFTAR</th>
                        <th class="text-center pe-4">KEPUTUSAN</th>
                    </tr>
                </thead>
                <tbody id="pendingTableBody">
                    <tr><td colspan="4" class="text-center py-5">Memuat data pendaftaran...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: DETAIL PROFIL CALON USER
     ========================================== -->
<div class="modal fade" id="modalDetailUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark"><i class="bi bi-person-badge me-2"></i>Detail Profil Pengaju</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="userDetailContent">
                    <!-- Konten Detail User Diisi via JS -->
                </div>
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <div id="footerActions"></div>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '<?php echo e(session('api_token')); ?>';

    let pendingUsers = []; // Simpan data di variabel lokal untuk efisiensi

    function loadPending() {
        axios.get('/api/users/pending').then(res => {
            pendingUsers = res.data;
            let html = '';

            if (pendingUsers.length === 0) {
                html = '<tr><td colspan="4" class="text-center py-5 text-muted italic small">Tidak ada permintaan akun baru saat ini.</td></tr>';
            } else {
                pendingUsers.forEach(u => {
                    const roleName = u.roles.length > 0 ? u.roles[0].name : 'No Role';
                    const date = new Date(u.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'});

                    html += `
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark">${u.name}</div>
                            <small class="text-muted">${u.email}</small>
                        </td>
                        <td><span class="badge bg-light text-dark border px-3">${roleName.toUpperCase()}</span></td>
                        <td><small class="text-muted">${date}</small></td>
                        <td class="text-center pe-4">
                            <button onclick="showFullProfile(${u.id})" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                                <i class="bi bi-search me-1"></i> Tinjau Profil
                            </button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('pendingTableBody').innerHTML = html;
        });
    }

    function showFullProfile(id) {
        const u = pendingUsers.find(user => user.id === id);
        const roleName = u.roles.length > 0 ? u.roles[0].name : 'No Role';

        let detailHtml = `
            <div class="text-center mb-4">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=random&size=128" class="rounded-circle shadow-sm mb-2" width="80">
                <h5 class="fw-bold mb-0">${u.name}</h5>
                <span class="badge bg-info bg-opacity-10 text-info px-3 rounded-pill" style="font-size: 10px;">PENGAJUAN: ${roleName.toUpperCase()}</span>
            </div>

            <div class="mb-3 p-3 bg-light rounded-3">
                <label class="d-block small text-muted fw-bold mb-1">ALAMAT / LOKASI UNIT</label>
                <p class="mb-0 small text-dark">${u.address || '<span class="text-muted italic">Tidak mencantumkan alamat</span>'}</p>
            </div>

            <div class="mb-3 p-3 bg-light rounded-3">
                <label class="d-block small text-muted fw-bold mb-1">KONTAK EMAIL</label>
                <p class="mb-0 small text-dark">${u.email}</p>
            </div>`;

        // JIKA KURIR, TAMPILKAN DATA KENDARAAN (Revisi Poin #4)
        if (roleName === 'courier' && u.courier_detail) {
            detailHtml += `
            <div class="mb-3 p-3 border border-primary border-opacity-25 rounded-3 bg-white">
                <label class="d-block small text-primary fw-bold mb-2"><i class="bi bi-truck me-1"></i> DATA KENDARAAN</label>
                <div class="row g-0">
                    <div class="col-6">
                        <small class="text-muted d-block">Jenis</small>
                        <span class="fw-bold small">${u.courier_detail.vehicle_type.toUpperCase()}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">No. Plat</small>
                        <span class="fw-bold small">${u.courier_detail.vehicle_plate}</span>
                    </div>
                </div>
            </div>`;
        }

        document.getElementById('userDetailContent').innerHTML = detailHtml;

        // Update tombol aksi di footer modal
        document.getElementById('footerActions').innerHTML = `
            <button onclick="decide(${u.id}, 'reject')" class="btn btn-outline-danger rounded-pill px-3">Tolak</button>
            <button onclick="decide(${u.id}, 'approve')" class="btn btn-success rounded-pill px-3 shadow-sm">Setujui Akun</button>
        `;

        new bootstrap.Modal(document.getElementById('modalDetailUser')).show();
    }

    function decide(id, action) {
        const title = action === 'approve' ? 'Setujui pendaftaran ini?' : 'Tolak pendaftaran ini?';
        const confirmColor = action === 'approve' ? '#198754' : '#dc3545';

        // Tutup modal detail dulu sebelum Swal muncul
        const modalEl = document.getElementById('modalDetailUser');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if(modalInstance) modalInstance.hide();

        Swal.fire({
            title: title,
            text: "Status akun akan segera diperbarui dalam sistem.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal'
        }).then(result => {
            if(result.isConfirmed) {
                axios.post(`/api/users/${id}/${action}`).then(res => {
                    Swal.fire('Berhasil!', res.data.message, 'success');
                    loadPending();
                }).catch(err => {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', loadPending);
</script>

<style>
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .italic { font-style: italic; }
    #userDetailContent p { line-height: 1.5; }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.backoffice', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PA-2\manajemen_obat\resources\views/admin/pending_users.blade.php ENDPATH**/ ?>