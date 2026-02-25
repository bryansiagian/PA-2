@extends('layouts.backoffice')

@section('page_title', 'Verifikasi Akun Baru')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Persetujuan Akun Baru</h4>
            <div class="text-muted small">Tinjau dan verifikasi pendaftaran pengguna sebelum memberikan akses sistem.</div>
        </div>
        <a href="/admin/users" class="btn btn-outline-indigo rounded-pill px-3">
            <i class="ph-arrow-left me-2"></i> Kembali ke Daftar User
        </a>
    </div>

    <!-- TABLE CARD -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="ph-user-plus me-2 text-orange"></i>Antrian Pendaftaran</h6>
            <div class="ms-auto">
                <span class="badge bg-orange bg-opacity-10 text-orange rounded-pill">Pending Verification</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3 py-3">Calon Pengguna</th>
                        <th>Role Pengajuan</th>
                        <th>Tanggal Daftar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pendingTableBody">
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <div class="ph-spinner spinner me-2"></div> Memuat data pendaftaran...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: DETAIL PROFIL CALON USER (Limitless Style)
     ========================================== -->
<div class="modal fade" id="modalDetailUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="ph-user-focus me-2"></i>Tinjau Profil Pengaju</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="userDetailContent">
                    <!-- Konten Detail User Diisi via JS -->
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                <button type="button" class="btn btn-link text-muted text-decoration-none fw-semibold" data-bs-dismiss="modal">Tutup</button>
                <div id="footerActions" class="d-flex gap-2">
                    <!-- Tombol Approve/Reject diisi via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Token sudah diset di layout, tapi kita pastikan lagi jika perlu
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let pendingUsers = [];

    function loadPending() {
        const tableBody = document.getElementById('pendingTableBody');

        axios.get('/api/users/pending').then(res => {
            pendingUsers = res.data;
            let html = '';

            if (!pendingUsers || pendingUsers.length === 0) {
                html = '<tr><td colspan="4" class="text-center py-5 text-muted italic small">Tidak ada permintaan akun baru saat ini.</td></tr>';
            } else {
                pendingUsers.forEach(u => {
                    const roleName = u.roles.length > 0 ? u.roles[0].name : 'No Role';
                    const date = new Date(u.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});

                    html += `
                    <tr class="border-bottom">
                        <td class="ps-3 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 40px; height: 40px; font-weight: bold;">
                                    ${u.name.charAt(0)}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">${u.name}</div>
                                    <div class="text-muted small">${u.email}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-indigo bg-opacity-10 text-indigo rounded-pill px-3">
                                ${roleName.toUpperCase()}
                            </span>
                        </td>
                        <td><div class="text-muted small fw-semibold">${date}</div></td>
                        <td class="text-center">
                            <button onclick="showFullProfile(${u.id})" class="btn btn-indigo btn-sm rounded-pill px-3 shadow-sm">
                                <i class="ph-magnifying-glass me-1"></i> Periksa Profil
                            </button>
                        </td>
                    </tr>`;
                });
            }
            tableBody.innerHTML = html;
        }).catch(err => {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-danger">Gagal sinkronisasi dengan server pendaftaran.</td></tr>';
        });
    }

    function showFullProfile(id) {
        const u = pendingUsers.find(user => user.id === id);
        const roleName = u.roles.length > 0 ? u.roles[0].name : 'No Role';

        let detailHtml = `
            <div class="text-center mb-4">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=random&size=128&bold=true" class="rounded-circle shadow mb-3 border border-4 border-white" width="100">
                <h4 class="fw-bold mb-1 text-indigo">${u.name}</h4>
                <div class="text-muted small mb-0">${u.email}</div>
                <span class="badge bg-indigo rounded-pill px-3 mt-2" style="font-size: 10px; letter-spacing: 1px;">ROLE: ${roleName.toUpperCase()}</span>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 bg-light rounded-3 border-start border-indigo border-3">
                        <label class="d-block fs-xs fw-bold text-muted text-uppercase mb-1">Alamat / Lokasi Unit</label>
                        <div class="text-dark small">${u.address || '<span class="text-muted italic">Tidak mencantumkan alamat</span>'}</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 bg-light rounded-3 border-start border-indigo border-3">
                        <label class="d-block fs-xs fw-bold text-muted text-uppercase mb-1">Nomor Telepon</label>
                        <div class="text-dark small">${u.phone || '-'}</div>
                    </div>
                </div>
            </div>`;

        // JIKA KURIR, TAMPILKAN DATA KENDARAAN (REVISI POIN #4)
        if (roleName === 'courier' && u.courier_detail) {
            detailHtml += `
            <div class="mt-4 p-3 border border-indigo border-opacity-20 rounded-3 bg-white shadow-sm">
                <label class="d-block fs-xs text-indigo fw-bold text-uppercase mb-3"><i class="ph-truck me-1"></i> Informasi Armada Kurir</label>
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <small class="text-muted d-block fs-xs">JENIS KENDARAAN</small>
                        <span class="fw-bold text-dark">${u.courier_detail.vehicle_type.toUpperCase()}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fs-xs">NOMOR PLAT</small>
                        <span class="fw-bold text-dark">${u.courier_detail.vehicle_plate}</span>
                    </div>
                </div>
            </div>`;
        }

        document.getElementById('userDetailContent').innerHTML = detailHtml;

        // Update tombol aksi di footer modal
        document.getElementById('footerActions').innerHTML = `
            <button onclick="decide(${u.id}, 'reject')" class="btn btn-outline-danger rounded-pill px-3 fw-bold btn-sm">Tolak Akun</button>
            <button onclick="decide(${u.id}, 'approve')" class="btn btn-success rounded-pill px-3 shadow-sm fw-bold btn-sm"><i class="ph-check-circle me-1"></i>Setujui Akun</button>
        `;

        new bootstrap.Modal(document.getElementById('modalDetailUser')).show();
    }

    function decide(id, action) {
        const title = action === 'approve' ? 'Setujui pendaftaran ini?' : 'Tolak pendaftaran ini?';
        const confirmColor = action === 'approve' ? '#10b981' : '#ef4444';

        const modalEl = document.getElementById('modalDetailUser');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if(modalInstance) modalInstance.hide();

        Swal.fire({
            title: title,
            text: "Konfirmasi aksi ini untuk memperbarui status akses pengguna.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(result => {
            if(result.isConfirmed) {
                Swal.fire({ title: 'Sedang memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                axios.post(`/api/users/${id}/${action}`).then(res => {
                    Swal.fire('Berhasil!', res.data.message, 'success');
                    loadPending();
                }).catch(err => {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem otoritas.', 'error');
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', loadPending);
</script>

<style>
    .bg-indigo { background-color: #5c6bc0 !important; }
    .text-indigo { color: #5c6bc0 !important; }
    .btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .btn-outline-indigo { color: #5c6bc0; border-color: #5c6bc0; }
    .btn-outline-indigo:hover { background-color: #5c6bc0; color: #fff; }
    .italic { font-style: italic; }
    .fs-xs { font-size: 0.7rem; }
    .border-start-indigo { border-left: 3px solid #5c6bc0 !important; }
</style>
@endsection