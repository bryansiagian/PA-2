@extends('layouts.backoffice')

@section('page_title', 'Profil Saya')

@section('content')
<div class="container-fluid">
    <!-- Header Halaman -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Profil Saya</h4>
            <div class="text-muted text-uppercase fs-xs">Informasi Akun & Keamanan</div>
        </div>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Kartu Identitas -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-4">
                <!-- Cover Background Kecil -->
                <div class="bg-indigo py-4"></div>

                <div class="card-body text-center" style="margin-top: -50px;">
                    <!-- Foto Profil -->
                    <div class="d-inline-block position-relative mb-3">
                        <img src="{{ asset('admin/assets/images/demo/users/face11.jpg') }}"
                             class="rounded-circle border border-4 border-white shadow-sm"
                             width="110" height="110" style="object-fit: cover;">
                        <button class="btn btn-indigo btn-icon btn-sm rounded-pill position-absolute bottom-0 end-0 border-2 border-white shadow">
                            <i class="ph-camera"></i>
                        </button>
                    </div>

                    <!-- Nama & Role -->
                    <h5 class="fw-bold mb-1">{{ Auth::user()->name }}</h5>
                    <span class="badge bg-indigo text-white fw-bold text-uppercase px-3 mb-4 rounded-pill shadow-sm">
                    <i class="ph-shield-check me-1"></i> {{ Auth::user()->roles->first()->name ?? 'Pengguna' }}
                    </span>

                    <!-- Info List -->
                    <div class="list-group list-group-flush border-top text-start">
                        <div class="list-group-item d-flex align-items-center px-0 py-3">
                            <div class="bg-light p-2 rounded-circle me-3">
                                <i class="ph-envelope text-muted"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="fs-xs text-muted text-uppercase fw-bold">Alamat Email</div>
                                <div class="small fw-semibold">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0 py-3">
                            <div class="bg-light p-2 rounded-circle me-3">
                                <i class="ph-calendar text-muted"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="fs-xs text-muted text-uppercase fw-bold">Tanggal Bergabung</div>
                                <div class="small fw-semibold">{{ Auth::user()->created_at->format('d F Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Pengaturan -->
        <div class="col-xl-8 col-lg-7">
            <!-- Card Edit Profil -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-user-focus me-2 text-indigo"></i>Ubah Informasi Dasar</h6>
                </div>
                <div class="card-body p-4">
                    <form id="updateProfileForm" onsubmit="handleUpdateProfile(event)">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Nama Lengkap</label>
                                <div class="form-control-feedback form-control-feedback-start">
                                    <input type="text" id="user_name" class="form-control border-light-subtle" value="{{ Auth::user()->name }}" required>
                                    <div class="form-control-feedback-icon"><i class="ph-user"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Email (Tidak dapat diubah)</label>
                            <div class="form-control-feedback form-control-feedback-start">
                                <input type="email" class="form-control border-light-subtle bg-light" value="{{ Auth::user()->email }}" readonly>
                                <div class="form-control-feedback-icon"><i class="ph-envelope-simple"></i></div>
                            </div>
                            <div class="form-text fs-xs">Hubungi Admin Sistem jika ingin mengganti alamat email utama.</div>
                        </div>
                        <div class="text-end">
                            <button type="submit" id="btnSaveProfile" class="btn btn-indigo px-4 fw-bold rounded-pill">
                                <i class="ph-check-circle me-2"></i>SIMPAN PERUBAHAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card Keamanan -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-lock-key me-2 text-danger"></i>Keamanan Kata Sandi</h6>
                </div>
                <div class="card-body p-4">
                    <form id="updatePasswordForm" onsubmit="handleUpdatePassword(event)">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Kata Sandi Baru</label>
                                <input type="password" id="new_password" class="form-control border-light-subtle" placeholder="Minimal 8 karakter" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Konfirmasi Kata Sandi</label>
                                <input type="password" id="confirm_password" class="form-control border-light-subtle" placeholder="Ulangi kata sandi baru" required>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button type="submit" id="btnSavePassword" class="btn btn-outline-danger px-4 fw-bold rounded-pill">
                                <i class="ph-shield-check me-2"></i>PERBARUI PASSWORD
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Fungsi Update Nama
    function handleUpdateProfile(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveProfile');
        const name = document.getElementById('user_name').value;

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        // Panggil API (Sesuaikan endpoint Anda jika ada)
        axios.post('/api/user/update-profile', { name })
            .then(res => {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Profil Anda telah diperbarui.', confirmButtonColor: '#5c68e2' });
            })
            .catch(err => {
                Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph-check-circle me-2"></i>SIMPAN PERUBAHAN';
            });
    }

    // 2. Fungsi Update Password
    function handleUpdatePassword(e) {
        e.preventDefault();
        const pass = document.getElementById('new_password').value;
        const confirm = document.getElementById('confirm_password').value;

        if (pass !== confirm) {
            return Swal.fire('Error', 'Konfirmasi password tidak cocok!', 'error');
        }

        const btn = document.getElementById('btnSavePassword');
        btn.disabled = true;

        axios.post('/api/user/update-password', { password: pass })
            .then(res => {
                Swal.fire({ icon: 'success', title: 'Password Diganti', text: 'Gunakan password baru pada login berikutnya.', confirmButtonColor: '#ef4444' });
                document.getElementById('updatePasswordForm').reset();
            })
            .catch(err => Swal.fire('Gagal', 'Gagal mengganti password.', 'error'))
            .finally(() => btn.disabled = false);
    }
</script>

<style>
    /* Styling Dasar Limitless */
    .bg-indigo { background-color: #5c68e2 !important; }
    .text-indigo { color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }

    .fs-xs { font-size: 0.75rem; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
