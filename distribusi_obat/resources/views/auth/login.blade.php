<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Login - Yayasan E-Pharma</title>

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
    body {
      background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)),
                  url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1920&q=80');
      background-size: cover;
      background-position: center;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
    }

    .login-card {
      background: #ffffff;
      border: none;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      width: 100%;
      max-width: 450px;
    }

    .card-header-accent {
      background: #3fbbc0;
      height: 6px;
      width: 100%;
    }

    .login-logo {
      font-family: 'Ubuntu', sans-serif;
      font-size: 2.5rem;
      font-weight: 700;
      color: #2c4964;
      text-decoration: none;
      display: block;
      margin-bottom: 5px;
    }

    .login-logo span {
      color: #3fbbc0;
    }

    .form-control {
      border-radius: 10px;
      padding: 12px 15px;
      border: 1px solid #e1e1e1;
      font-size: 0.9rem;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: #3fbbc0;
      box-shadow: 0 0 0 0.2rem rgba(63, 187, 192, 0.1);
    }

    .btn-login {
      background: #3fbbc0;
      color: white;
      border-radius: 30px;
      padding: 12px;
      font-weight: 600;
      border: none;
      transition: 0.3s;
      width: 100%;
      margin-top: 10px;
    }

    .btn-login:hover {
      background: #329ea2;
      box-shadow: 0 5px 15px rgba(63, 187, 192, 0.3);
      color: white;
    }

    .back-to-site {
      color: #3fbbc0;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 500;
      transition: 0.3s;
    }

    .back-to-site:hover {
      color: #2c4964;
    }

    .input-group-text {
      background: #f8f9fa;
      border-radius: 10px 0 0 10px;
      border: 1px solid #e1e1e1;
      border-right: none;
      color: #3fbbc0;
    }

    .form-control-with-icon {
      border-radius: 0 10px 10px 0;
    }
  </style>
</head>

<body>

  <div class="container p-3">
    <div class="login-card mx-auto">
      <div class="card-header-accent"></div>
      <div class="card-body p-4 p-md-5">

        <!-- Logo Section -->
        <div class="text-center mb-4">
          <a href="/" class="login-logo">E-<span>Pharma</span></a>
          <p class="text-muted small">Sistem Manajemen Logistik Farmasi Terpadu</p>
        </div>

        <h5 class="fw-bold mb-4 text-dark text-center">Silakan Masuk</h5>

        <form action="{{ route('login') }}" method="POST">
          @csrf

          <!-- Email Field -->
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">ALAMAT EMAIL</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control form-control-with-icon @error('email') is-invalid @enderror"
                     placeholder="nama@unitkesehatan.id" value="{{ old('email') }}" required autofocus>
            </div>
            @error('email')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <!-- Password Field -->
          <div class="mb-4">
            <label class="form-label small fw-bold text-muted">KATA SANDI</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" class="form-control form-control-with-icon"
                     placeholder="••••••••" required>
            </div>
          </div>

          <!-- Login Button -->
          <button type="submit" class="btn btn-login shadow-sm mb-3">
            Masuk ke Sistem <i class="bi bi-box-arrow-in-right ms-1"></i>
          </button>

          <div class="text-center mt-3">
            <p class="small text-muted mb-2">Belum memiliki akun unit kesehatan?</p>
            <a href="/register" class="back-to-site fw-bold">Daftar Akun Baru</a>
          </div>

          <hr class="my-4 opacity-25">

          <div class="text-center">
            <a href="/" class="back-to-site">
              <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

</body>

</html>
