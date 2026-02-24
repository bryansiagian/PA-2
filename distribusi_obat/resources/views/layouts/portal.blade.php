<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Portal E-Pharma - Unit Kesehatan Terpadu</title>

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
  <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

  <!-- Core Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --accent-color: #3fbbc0;
      --heading-color: #2c4964;
    }

    body {
      background-color: #f1f7f8;
      padding-top: 80px; /* Offset for fixed header */
    }

    .header {
      background: #fff;
      box-shadow: 0px 2px 20px rgba(0, 0, 0, 0.1);
      padding: 15px 0;
    }

    /* Style Search Bar ala MediNest */
    .search-box .form-control {
      border-radius: 25px;
      padding-left: 20px;
      border: 1px solid #deebec;
      font-size: 14px;
    }

    .search-box .form-control:focus {
      border-color: var(--accent-color);
      box-shadow: none;
    }

    /* Cart Badge Animation */
    @keyframes bounceCart {
      0% { transform: scale(1); }
      50% { transform: scale(1.3); }
      100% { transform: scale(1); }
    }
    .animate-cart {
      animation: bounceCart 0.5s ease-in-out;
    }

    #cartBadge {
      font-size: 10px;
      background-color: #ff4d4d;
      border: 2px solid #fff;
    }

    /* Dropdown Styling */
    .dropdown-menu {
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 10px;
    }

    .dropdown-item {
      border-radius: 8px;
      padding: 8px 15px;
      font-size: 14px;
      color: var(--heading-color);
    }

    .dropdown-item i {
      color: var(--accent-color);
      margin-right: 10px;
    }

    .dropdown-item:hover {
      background-color: #f1f7f8;
      color: var(--accent-color);
    }

    .btn-portal {
      background: var(--accent-color);
      color: #fff;
      border-radius: 25px;
      padding: 8px 25px;
      font-weight: 500;
      transition: 0.3s;
    }

    .btn-portal:hover {
      background: #329ea2;
      color: #fff;
    }
  </style>

  <script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  </script>
</head>

<body>

  <!-- HEADER -->
  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

      <a href="/dashboard" class="logo d-flex align-items-center me-auto me-xl-0">
        <h1 class="sitename" style="font-size: 24px; color: var(--heading-color); font-weight: 700; font-family: 'Ubuntu', sans-serif;">
            E-<span>Pharma</span>
        </h1>
      </a>

      <!-- Search Section -->
      <div class="search-box d-none d-lg-block mx-4 flex-grow-1" style="max-width: 400px;">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 pe-0"><i class="bi bi-search text-muted"></i></span>
            <input id="globalSearchInput" class="form-control border-0" type="search" placeholder="Cari stok obat di gudang...">
        </div>
      </div>

      <nav id="navmenu" class="navmenu">
        <ul class="d-flex align-items-center">
          <!-- Cart Link -->
          <li class="me-3">
            <a href="{{ route('customer.cart') }}" class="position-relative p-2">
              <i class="bi bi-cart3 fs-4" style="color: var(--heading-color);"></i>
              <span id="cartBadge" class="badge rounded-pill position-absolute top-0 start-100 translate-middle" style="display: none;">0</span>
            </a>
          </li>

          <!-- User Dropdown -->
          <li class="dropdown">
            <a href="#" class="d-flex align-items-center fw-semibold" style="color: var(--heading-color);">
              <span><i class="bi bi-person-circle fs-5 me-1"></i> {{ Auth::user()->name }}</span>
              <i class="bi bi-chevron-down toggle-dropdown ms-1"></i>
            </a>
            <ul>
              <li><a href="{{ route('customer.manual_request') }}"><i class="bi bi-pencil-square"></i> Request Obat Baru</a></li>
              <li><a href="/customer/history"><i class="bi bi-clock-history"></i> Riwayat Pesanan</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="/logout" method="POST" id="logout-form">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Logout</button>
                </form>
              </li>
            </ul>
          </li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-portal ms-3 d-none d-md-inline-block" href="/">Lihat Katalog</a>

    </div>
  </header>

  <!-- CONTENT AREA -->
  <main id="main">
    @yield('content')
  </main>

  <!-- FOOTER -->
  <footer class="bg-white border-top py-4 mt-5">
    <div class="container text-center">
      <p class="text-muted small mb-0">© 2024 <strong>Yayasan E-Pharma</strong>. Terintegrasi dengan Sistem Logistik Nasional.</p>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>

  <script>
    // Fungsi Update Counter Keranjang
    function updateCartBadge() {
        const badge = document.getElementById('cartBadge');
        axios.get('/api/cart')
            .then(res => {
                const count = res.data.length;
                if (count > 0) {
                    badge.innerText = count;
                    badge.style.display = 'block';
                    badge.classList.add('animate-cart');
                    setTimeout(() => badge.classList.remove('animate-cart'), 500);
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(err => console.error("Gagal update badge:", err));
    }

    document.addEventListener('DOMContentLoaded', updateCartBadge);
  </script>

</body>

</html>
