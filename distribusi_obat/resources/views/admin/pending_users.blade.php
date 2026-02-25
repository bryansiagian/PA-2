@extends('layouts.backoffice')

@section('page_title', 'Verifikasi Akun Baru')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Persetujuan Akun Baru</h4>
            <div class="text-muted">Tinjau dan verifikasi pendaftaran pengguna sebelum memberikan akses sistem</div>
        </div>
        <div class="ms-3">
            <a href="/admin/users" class="btn btn-light btn-label rounded-pill fw-bold">
                <i class="ph-arrow-left me-2"></i> Kembali ke Daftar User
            </a>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-user-plus me-2 text-warning"></i>Antrian Pendaftaran</h5>
            <div class="ms-auto">
                <!-- Badge dibuat solid agar teks terlihat jelas -->
                <span class="badge bg-warning text-dark fw-bold px-3 shadow-sm rounded-pill">
                    <i class="ph-clock-counter-clockwise me-1"></i> PENDING VERIFICATION
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3 py-3">Calon Pengguna</th>
                        <th>Role Pengajuan</th>
                        <th>Tanggal Daftar</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pendingTableBody">
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="ph-spinner spinner text-indigo me-2"></div>
                            <span class="fw-bold text-muted">Memuat data pendaftaran...</span>
                        </td>
                    </tr>
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
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-user-focus me-2"></i>Tinjau Profil Pengaju</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="userDetailContent">
                    <!-- Konten Detail User Diisi via JS -->
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">TUTUP</button>
                <div id="footerActions" class="d-flex gap-2">
                    <!-- Tombol Approve/Reject diisi via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let pendingUsers = [];

    function loadPending() {
        const tableBody = document.getElementById('pendingTableBody');

        axios.get('/api/users/pending').then(res => {
            pendingUsers = res.data;
            let html = '';

            if (!pendingUsers || pendingUsers.length === 0) {
                html = '<tr><td colspan="4" class="text-center py-5 text-muted small">Tidak ada permintaan akun baru saat ini.</td></tr>';
            } else {
                pendingUsers.forEach(u => {
                    const roleName = u.roles.length > 0 ? u.roles[0].name : 'No Role';
                    const date = new Date(u.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});

                    html += `
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm fw-bold" style="width: 40px; height: 40px;">
                                    ${u.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">${u.name}</div>
                                    <div class="fs-xs text-muted">${u.email}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-indigo bg-opacity-10 text-indigo rounded-pill px-3 fw-bold">
                                ${roleName.toUpperCase()}
                            </span>
                        </td>
                        <td><div class="text-muted small fw-bold"><i class="ph-calendar me-1"></i>${date}</div></td>
                        <td class="text-center pe-3">
                            <button onclick="showFullProfile(${u.id})" class="btn btn-indigo btn-sm rounded-pill px-3 shadow-sm fw-bold">
                                <i class="ph-magnifying-glass me-1"></i> PERIKSA PROFIL
                            </button>
                        </td>
                    </tr>`;
                });
            }
            tableBody.innerHTML = html;
        }).catch(err => {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-danger fw-bold">Gagal sinkronisasi dengan server pendaftaran.</td></tr>';
        });
    }

    function showFullProfile(id) {
        const u = pendingUsers.find(user => user.id === id);
        const roleName = u.roles.length > 0 ? u.roles[0].name : 'No Role';

        let detailHtml = `
            <div class="text-center mb-4">
                <div class="d-inline-block position-relative mb-3">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=5c68e2&color=fff&size=128&bold=true" class="rounded-circle shadow border border-4 border-white" width="90">
                </div>
                <h5 class="fw-bold mb-0 text-dark">${u.name}</h5>
                <div class="text-muted fs-xs mb-2">${u.email}</div>
                <span class="badge bg-indigo text-white rounded-pill px-3" style="font-size: 9px; letter-spacing: 1px;">ROLE: ${roleName.toUpperCase()}</span>
            </div>

            <div class="mb-3">
                <div class="p-3 bg-light rounded-3 border-start border-indigo border-3">
                    <label class="d-block fs-xs fw-bold text-muted text-uppercase mb-1">Alamat / Lokasi Unit</label>
                    <div class="text-dark small fw-semibold">${u.address || 'Tidak mencantumkan alamat'}</div>
                </div>
            </div>

            <div class="mb-0">
                <div class="p-3 bg-light rounded-3 border-start border-indigo border-3">
                    <label class="d-block fs-xs fw-bold text-muted text-uppercase mb-1">Nomor Telepon</label>
                    <div class="text-dark small fw-semibold">${u.phone || '-'}</div>
                </div>
            </div>`;

        // DATA KURIR (REVISI POIN #4)
        if (roleName === 'courier' && u.courier_detail) {
            detailHtml += `
            <div class="mt-3 p-3 border border-indigo border-opacity-20 rounded-3 bg-white shadow-sm">
                <label class="d-block fs-xs text-indigo fw-bold text-uppercase mb-2"><i class="ph-truck me-1"></i> Informasi Armada Kurir</label>
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="fs-xs text-muted mb-1">KENDARAAN</div>
                        <span class="fw-bold text-dark small">${u.courier_detail.vehicle_type.toUpperCase()}</span>
                    </div>
                    <div class="col-6">
                        <div class="fs-xs text-muted mb-1">NOMOR PLAT</div>
                        <span class="fw-bold text-dark small">${u.courier_detail.vehicle_plate}</span>
                    </div>
                </div>
            </div>`;
        }

        document.getElementById('userDetailContent').innerHTML = detailHtml;

        document.getElementById('footerActions').innerHTML = `
            <button onclick="decide(${u.id}, 'reject')" class="btn btn-light text-danger fw-bold rounded-pill px-3 btn-sm">TOLAK</button>
            <button onclick="decide(${u.id}, 'approve')" class="btn btn-indigo rounded-pill px-3 shadow-sm fw-bold btn-sm"><i class="ph-check-circle me-1"></i>SETUJUI AKUN</button>
        `;

        new bootstrap.Modal(document.getElementById('modalDetailUser')).show();
    }

    function decide(id, action) {
        const title = action === 'approve' ? 'Setujui pendaftaran ini?' : 'Tolak pendaftaran ini?';
        const confirmColor = action === 'approve' ? '#059669' : '#ef4444';

        Swal.fire({
            title: title,
            text: "Status akses pengguna akan segera diperbarui.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-indigo', cancelButton: 'btn btn-light' }
        }).then(result => {
            if(result.isConfirmed) {
                bootstrap.Modal.getInstance(document.getElementById('modalDetailUser')).hide();
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

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
    /* Styling Dasar Indigo Limitless */
    .bg-indigo { background-color: #5c68e2 !important; }
    .text-indigo { color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }

    .table td { padding: 0.85rem 1rem; }
    .fs-xs { font-size: 0.75rem; }

    /* Animasi Spinner */
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
