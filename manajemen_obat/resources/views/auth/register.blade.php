<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Pharma System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f4f7fa; min-height: 100vh; display: flex; align-items: center; }
        .card-register { border: none; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        .register-header { background: #0d6efd; color: white; padding: 30px; border-radius: 20px 20px 0 0; text-align: center; }
        .form-control, .form-select { border-radius: 10px; padding: 12px 15px; border: 1px solid #eee; background-color: #fcfcfc; }
        .vehicle-box { background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 15px; display: none; /* Hidden by default */ }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-register">
                <div class="register-header">
                    <h3 class="fw-bold mb-1">Buat Akun Baru</h3>
                    <p class="mb-0 opacity-75 small">Lengkapi data untuk bergabung dalam jaringan E-Pharma</p>
                </div>
                <div class="card-body p-4 p-md-5">

                    <form id="formRegister" onsubmit="submitRegister(event)">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" placeholder="Nama Anda" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Pilih Peran (Role)</label>
                            <select name="role_id" id="roleSelect" class="form-select" onchange="toggleVehicleFields()" required>
                                <option value="" selected disabled>-- Pilih Hak Akses --</option>
                                @foreach($roles as $role)
                                    <!-- Kita simpan nama role di atribut data untuk dicek di JS -->
                                    <option value="{{ $role->id }}" data-name="{{ $role->name }}">
                                        {{ strtoupper($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- SEKSI KENDARAAN (Hanya muncul jika Role = Courier) -->
                        <div id="vehicleSection" class="vehicle-box p-4 mb-3">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-truck me-2"></i>Informasi Kendaraan Kurir</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Jenis Kendaraan</label>
                                    <select name="vehicle_type" class="form-select border-white bg-white">
                                        <option value="motorcycle">Sepeda Motor</option>
                                        <option value="car">Mobil / Van</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Nomor Plat</label>
                                    <input type="text" name="vehicle_plate" class="form-control border-white bg-white" placeholder="B 1234 ABC">
                                </div>
                            </div>
                            <small class="text-muted" style="font-size: 10px;">* Data kendaraan akan digunakan sistem untuk mencocokkan kapasitas paket.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Alamat / Lokasi Unit</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold small">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold small">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" id="btnSubmit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">
                            Daftar Sekarang
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Fungsi untuk menyembunyikan/menampilkan input kendaraan
    function toggleVehicleFields() {
        const roleSelect = document.getElementById('roleSelect');
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption.getAttribute('data-name');
        const vehicleSection = document.getElementById('vehicleSection');

        if (roleName === 'courier') {
            vehicleSection.style.display = 'block';
            // Beri atribut required secara dinamis jika perlu
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
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

        axios.post('/register', formData)
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil!',
                    text: 'Akun Anda sedang menunggu verifikasi Admin.',
                    confirmButtonColor: '#0d6efd',
                }).then(() => window.location.href = '/login');
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Daftar Sekarang';
                let msg = err.response.data.message || 'Terjadi kesalahan.';
                if(err.response.data.errors) msg = Object.values(err.response.data.errors)[0][0];
                Swal.fire('Gagal', msg, 'error');
            });
    }
</script>
</body>
</html>