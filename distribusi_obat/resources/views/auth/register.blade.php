<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Registrasi - Yayasan E-Pharma</title>

  <!-- Favicons -->
  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

  <style>
    :root {
      --accent-color: #3fbbc0;
      --heading-color: #2c4964;
    }

    body {
      background: linear-gradient(rgba(241, 247, 248, 0.9), rgba(241, 247, 248, 0.9)),
                  url('https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1920&q=80');
      background-size: cover;
      background-attachment: fixed;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 40px 0;
    }

    .card-register {
      border: none;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .register-header {
      background: var(--accent-color);
      color: white;
      padding: 40px 30px;
      text-align: center;
    }

    .register-logo {
      font-family: 'Ubuntu', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      color: #fff;
      text-decoration: none;
      display: block;
      margin-bottom: 10px;
    }

    .register-logo span {
      color: var(--heading-color);
    }

    .form-label {
      color: var(--heading-color);
      font-weight: 600;
      font-size: 0.85rem;
      margin-bottom: 8px;
    }

    .form-control, .form-select {
      border-radius: 10px;
      padding: 12px 15px;
      border: 1px solid #deebec;
      background-color: #fcfcfc;
      font-size: 0.9rem;
      transition: 0.3s;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--accent-color);
      box-shadow: 0 0 0 0.25rem rgba(63, 187, 192, 0.1);
      background-color: #fff;
    }

    .vehicle-box {
      background-color: #f1f7f8;
      border: 2px dashed var(--accent-color);
      border-radius: 15px;
      display: none;
      transition: 0.4s;
    }

    .btn-register {
      background: var(--accent-color);
      color: white;
      border-radius: 30px;
      padding: 15px;
      font-weight: 600;
      border: none;
      transition: 0.3s;
      width: 100%;
      margin-top: 20px;
    }

    .btn-register:hover {
      background: #329ea2;
      box-shadow: 0 8px 20px rgba(63, 187, 192, 0.3);
      color: white;
    }

    .login-link {
      color: var(--accent-color);
      text-decoration: none;
      font-weight: 600;
    }

    .login-link:hover {
      text-decoration: underline;
    }

    .input-group-text {
      background: #fff;
      border-color: #deebec;
      color: var(--accent-color);
      border-radius: 10px 0 0 10px;
    }

    .form-with-icon {
      border-radius: 0 10px 10px 0;
    }
  </style>
</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-register">
                <div class="register-header">
                    <a href="/" class="register-logo">E-<span>Pharma</span></a>
                    <h4 class="fw-bold mb-1">Registrasi Akun Baru</h4>
                    <p class="mb-0 opacity-75 small">Bergabunglah dalam jaringan distribusi farmasi digital kami</p>
                </div>
                <div class="card-body p-4 p-md-5">

                    <form id="formRegister" onsubmit="submitRegister(event)">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control form-with-icon" placeholder="Nama lengkap petugas" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control form-with-icon" placeholder="email@unitkesehatan.id" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-uppercase">Hak Akses Sistem (Role)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <select name="role_id" id="roleSelect" class="form-select form-with-icon" onchange="toggleVehicleFields()" required>
                                    <option value="" selected disabled>-- Pilih Peran Anda --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" data-name="{{ $role->name }}">
                                            {{ strtoupper($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- SEKSI KENDARAAN (Khusus Kurir) -->
                        <div id="vehicleSection" class="vehicle-box p-4 mb-4">
                            <h6 class="fw-bold mb-3" style="color: var(--accent-color);">
                                <i class="bi bi-truck-flatbed me-2"></i>Informasi Kendaraan Operasional
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Jenis Kendaraan</label>
                                    <select name="vehicle_type" class="form-select border-0">
                                        <option value="motorcycle">Sepeda Motor</option>
                                        <option value="car">Mobil / Van Farmasi</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Nomor Plat Kendaraan</label>
                                    <input type="text" name="vehicle_plate" class="form-control border-0" placeholder="Contoh: B 1234 ABC">
                                </div>
                            </div>
                            <p class="mb-0 text-muted" style="font-size: 11px;">
                                <i class="bi bi-info-circle me-1"></i> Data kendaraan diperlukan untuk optimasi rute pengiriman obat.
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-uppercase">Alamat Lengkap Unit / Domisili</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <textarea name="address" class="form-control form-with-icon" rows="2" placeholder="Sebutkan jalan, nomor, dan kota..."></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase">Kata Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password" name="password" class="form-control form-with-icon" placeholder="••••••••" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase">Konfirmasi Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control form-with-icon" placeholder="••••••••" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="btnSubmit" class="btn btn-register shadow">
                            Daftar Sekarang <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>

                        <div class="text-center mt-4">
                            <p class="small text-muted">Sudah memiliki akun? <a href="/login" class="login-link">Masuk di sini</a></p>
                            <hr class="my-4 opacity-25">
                            <a href="/" class="text-muted small text-decoration-none">
                                <i class="bi bi-house-door me-1"></i> Kembali ke Beranda
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function toggleVehicleFields() {
        const roleSelect = document.getElementById('roleSelect');
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption.getAttribute('data-name');
        const vehicleSection = document.getElementById('vehicleSection');

        if (roleName === 'courier') {
            vehicleSection.style.display = 'block';
            document.getElementsByName('vehicle_plate')[0].required = true;
        } else {
            vehicleSection.style.display = 'none';
            document.getElementsByName('vehicle_plate')[0].required = false;
        }
    }

    function submitRegister(event) {
        event.preventDefault();
        const btn = document.getElementById('btnSubmit');
        const formData = new FormData(document.getElementById('formRegister'));

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses Data...';

        axios.post('/register', formData)
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil',
                    text: 'Akun Anda telah dibuat dan menunggu verifikasi admin.',
                    confirmButtonColor: '#3fbbc0',
                }).then(() => window.location.href = '/login');
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Daftar Sekarang <i class="bi bi-arrow-right-circle ms-2"></i>';
                let msg = err.response.data.message || 'Terjadi kesalahan sistem.';
                if(err.response.data.errors) msg = Object.values(err.response.data.errors)[0][0];
                Swal.fire({
                    icon: 'error',
                    title: 'Pendaftaran Gagal',
                    text: msg,
                    confirmButtonColor: '#3fbbc0',
                });
            });
    }
</script>
</body>
</html>
