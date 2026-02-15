<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Portal - RS & Klinik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/custom-style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Set Global Axios Headers
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Animasi Getar untuk Ikon Keranjang */
        @keyframes bounceCart {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        .animate-cart {
            animation: bounceCart 0.5s ease-in-out;
        }

        /* Style untuk SweetAlert agar sesuai tema e-commerce */
        .swal2-popup {
            border-radius: 15px !important;
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-light">
    <!-- E-commerce Navbar -->
    <nav class="navbar navbar-expand-lg navbar-white bg-white shadow-sm sticky-top p-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/dashboard">💊 E-PHARMA</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navPortal"><span class="navbar-toggler-icon"></span></button>

            <div class="collapse navbar-collapse" id="navPortal">
                <form class="mx-auto d-flex col-md-6 mt-2 mt-lg-0" onsubmit="return false;">
                    <input id="globalSearchInput" class="form-control rounded-pill" type="search" placeholder="Cari obat...">
                </form>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <a href="{{ route('customer.cart') }}" class="nav-link position-relative">
                            <i class="bi bi-cart3 fs-4 text-primary"></i>
                            <!-- Tambahkan id="cartBadge" dan style display none -->
                            <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle"
                                  id="cartBadge"
                                  style="display: none;">
                                  0
                            </span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-bold" href="#" id="userDrop" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="{{ route('customer.manual_request') }}"><i class="bi bi-pencil-square"></i> Request Obat Baru</a></li>
                            <li><a class="dropdown-item" href="/customer/history"><i class="bi bi-clock-history"></i> Riwayat Pesanan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="/logout" method="POST">@csrf <button class="dropdown-item text-danger">Logout</button></form></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi Update Counter Keranjang (Global)
        function updateCartBadge() {
            const badge = document.getElementById('cartBadge');

            // Ambil data keranjang langsung dari database
            axios.get('/api/cart')
                .then(res => {
                    const cartItems = res.data;
                    const count = cartItems.length; // Jumlah macam obat

                    if (count > 0) {
                        badge.innerText = count;
                        badge.style.display = 'block'; // Tampilkan jika ada isi

                        // Opsional: Tambahkan efek animasi berdenyut saat angka berubah
                        badge.classList.add('animate-cart');
                        setTimeout(() => badge.classList.remove('animate-cart'), 500);
                    } else {
                        badge.style.display = 'none'; // Sembunyikan jika kosong
                    }
                })
                .catch(err => {
                    console.error("Gagal update badge keranjang:", err);
                    badge.style.display = 'none';
                });
        }
        document.addEventListener('DOMContentLoaded', updateCartBadge);

        axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
    </script>
</body>
</html>