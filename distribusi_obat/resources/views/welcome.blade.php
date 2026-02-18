<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yayasan E-Pharma - Logistik Farmasi Terpadu</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Core JS (Load in Head for global access) -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; background-color: #ffffff; color: #334155; }
        .hero-section { background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), url('https://images.unsplash.com/photo-1587854680352-936b22b91030?auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; color: white; padding: 140px 0; }
        .nav-link { font-weight: 600; color: #475569 !important; }
        .nav-link:hover { color: #0d6efd !important; }
        .section-title { font-weight: 800; color: #0f172a; margin-bottom: 3rem; text-align: center; position: relative; }
        .section-title::after { content: ''; display: block; width: 60px; height: 4px; background: #0d6efd; margin: 10px auto; border-radius: 2px; }
        .card-custom { border: none; border-radius: 1.25rem; transition: 0.3s; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
        .card-custom:hover { transform: translateY(-5px); box-shadow: 0 20px 35px -5px rgba(0,0,0,0.1); }
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.05); }
        #cartBadge { font-size: 0.65rem; padding: 0.35em 0.5em; }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/"><i class="bi bi-capsule-pill me-2"></i>E-PHARMA</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link mx-2" href="#profil">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link mx-2" href="#berita">Berita</a></li>
                    <li class="nav-item"><a class="nav-link mx-2" href="#katalog">Katalog Obat</a></li>
                    <li class="nav-item"><a class="nav-link mx-2" href="#organisasi">Organisasi</a></li>

                    @auth
                        @role('customer')
                            <li class="nav-item mx-2">
                                <a class="nav-link position-relative" href="/customer/cart">
                                    <i class="bi bi-cart3 fs-5"></i>
                                    <span id="cartBadge" class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="display: none;">0</span>
                                </a>
                            </li>
                            <li class="nav-item mx-2"><a class="nav-link" href="/customer/history">Pesanan Saya</a></li>
                        @endrole

                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                            </form>
                        </li>
                        {{-- <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle btn btn-primary text-white rounded-pill px-4 shadow-sm" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 rounded-4">
                                @if(!auth()->user()->hasRole('customer'))
                                    <li><a class="dropdown-item py-2" href="/dashboard"><i class="bi bi-speedometer2 me-2"></i> Panel Kerja</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger py-2"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </li> --}}
                    @else
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-primary rounded-pill px-4 fw-bold" href="/login">Masuk</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <header class="hero-section text-center">
        <div class="container">
            <h1 class="display-3 fw-bold mb-3">Logistik Farmasi Cepat & Transparan</h1>
            <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 800px;">Sistem terpadu Yayasan E-Pharma untuk distribusi obat-obatan ke seluruh unit kesehatan dengan pengawasan stok digital.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#katalog" class="btn btn-primary btn-lg rounded-pill px-5 shadow fw-bold">Jelajahi Katalog</a>
                <a href="#profil" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold">Tentang Kami</a>
            </div>
        </div>
    </header>

    <!-- PROFIL -->
    <section class="py-5" id="profil">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill mb-3 fw-bold small">E-PHARMA PROFILE</span>
                    <h2 class="fw-bold mb-4 text-dark" id="aboutTitle">Memuat Judul...</h2>
                    <p class="text-muted lh-lg" id="aboutContent">Sedang memuat informasi profil yayasan...</p>
                    <div class="mt-4 p-4 bg-light rounded-4 border-start border-primary border-4">
                        <h6 class="fw-bold text-dark"><i class="bi bi-clock-history me-2"></i>Sejarah Singkat</h6>
                        <p class="small text-muted mb-0" id="historyContent">Memuat sejarah...</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-5 rounded-5 shadow-sm border bg-white">
                        <h4 class="fw-bold text-dark mb-4"><i class="bi bi-lightbulb text-warning me-2"></i>Visi & Misi</h4>
                        <div id="visionMissionContent" class="text-muted lh-lg small">Memuat visi misi...</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BERITA -->
    <section class="py-5 bg-soft-primary" id="berita">
        <div class="container py-5">
            <h2 class="section-title">Berita & Kegiatan</h2>
            <div id="postsContainer" class="row g-4">
                <div class="col-12 text-center text-muted">Memuat berita terbaru...</div>
            </div>
        </div>
    </section>

    <!-- KATALOG OBAT -->
    <section class="py-5" id="katalog">
        <div class="container py-5">
            <h2 class="section-title">Katalog Produk Obat</h2>
            <div id="drugsContainer" class="row row-cols-1 row-cols-md-4 g-4">
                <div class="col-12 text-center text-muted">Menyinkronkan stok gudang...</div>
            </div>
        </div>
    </section>

    <!-- ORGANISASI -->
    <section class="py-5 bg-light" id="organisasi">
        <div class="container py-5 text-center">
            <h2 class="section-title">Struktur Organisasi</h2>
            <div id="orgContainer" class="row justify-content-center g-4"></div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-5">
        <div class="container pt-4">
            <div class="row g-4 text-center text-md-start">
                <div class="col-md-4">
                    <h4 class="fw-bold text-primary mb-3">E-PHARMA</h4>
                    <p class="small opacity-50" id="footerAbout">Sistem manajemen gudang dan distribusi obat terpadu.</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                    <ul class="list-unstyled small opacity-75">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i> <span id="footerAddress">-</span></li>
                        <li class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i> <span id="footerPhone">-</span></li>
                        <li class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i> <span id="footerEmail">-</span></li>
                        <li><i class="bi bi-whatsapp me-2 text-primary"></i> <span id="footerWA">-</span></li>
                    </ul>
                </div>
                <div class="col-md-4 text-md-end">
                    <h6 class="fw-bold mb-3">Tautan Cepat</h6>
                    <a href="/login" class="text-white-50 text-decoration-none d-block small mb-2">Login Petugas</a>
                    <a href="/register" class="text-white-50 text-decoration-none d-block small">Pendaftaran Unit</a>
                </div>
            </div>
            <hr class="my-5 opacity-25">
            <p class="text-center small opacity-50 mb-0">&copy; 2024 Yayasan E-Pharma. Professional Edition Laravel 12.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Set Default Axios Headers
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

        document.addEventListener('DOMContentLoaded', () => {
            fetchLandingPage();
            fetchCatalog();
            @auth updateCartBadge(); @endauth
        });

        function fetchLandingPage() {
            axios.get('/api/public/landing-page').then(res => {
                const d = res.data;
                const p = d.profiles;

                // Content
                if(p.about) {
                    document.getElementById('aboutTitle').innerText = p.about.title;
                    document.getElementById('aboutContent').innerText = p.about.content;
                    document.getElementById('footerAbout').innerText = p.about.content.substring(0, 150) + '...';
                }
                if(p.history) document.getElementById('historyContent').innerText = p.history.content;
                if(p.vision_mission) document.getElementById('visionMissionContent').innerHTML = p.vision_mission.content;

                // Contact
                document.getElementById('footerAddress').innerText = p.contact_address?.content || '-';
                document.getElementById('footerPhone').innerText = p.contact_phone?.content || '-';
                document.getElementById('footerEmail').innerText = p.contact_email?.content || '-';
                document.getElementById('footerWA').innerText = p.contact_wa?.content || '-';

                // Posts
                let postHtml = '';
                [...d.news, ...d.activities].forEach(post => {
                    const img = post.image ? `/storage/${post.image}` : 'https://placehold.co/600x400?text=E-Pharma';
                    postHtml += `
                    <div class="col-md-4">
                        <div class="card card-custom h-100 overflow-hidden border-0">
                            <img src="${img}" class="card-img-top" style="height:200px; object-fit:cover">
                            <div class="card-body p-4">
                                <span class="badge bg-primary rounded-pill mb-2" style="font-size: 10px;">${post.type.toUpperCase()}</span>
                                <h5 class="fw-bold text-dark">${post.title}</h5>
                                <p class="text-muted small mb-0">${post.content.substring(0, 100)}...</p>
                            </div>
                        </div>
                    </div>`;
                });
                document.getElementById('postsContainer').innerHTML = postHtml || '<p class="text-center w-100">Belum ada berita.</p>';

                // Org
                let orgHtml = '';
                d.organization.forEach(o => {
                    const img = o.photo ? `/storage/${o.photo}` : 'https://ui-avatars.com/api/?name='+o.name+'&background=0D6EFD&color=fff';
                    orgHtml += `
                    <div class="col-md-3">
                        <img src="${img}" class="rounded-circle mb-3 border shadow-sm" width="120" height="120" style="object-fit:cover">
                        <h6 class="fw-bold mb-0">${o.name}</h6>
                        <span class="text-primary small fw-bold text-uppercase">${o.position}</span>
                    </div>`;
                });
                document.getElementById('orgContainer').innerHTML = orgHtml;
            });
        }

        function fetchCatalog() {
            axios.get('/api/public/drugs').then(res => {
                let drugHtml = '';
                res.data.forEach(drug => {
                    const img = drug.image ? `/${drug.image}` : 'https://placehold.co/400x300?text=Produk+Obat';

                    const isCustomer = @auth @if(auth()->user()->hasRole('customer')) true @else false @endif @else false @endauth;

                    let actionBtn = isCustomer
                        ? `<button onclick="addToCart(${drug.id}, '${drug.name}')" class="btn btn-primary btn-sm w-100 rounded-pill shadow-sm py-2 fw-bold">Pesan Sekarang</button>`
                        : `<a href="/login" class="btn btn-outline-secondary btn-sm w-100 rounded-pill py-2 fw-bold">Login untuk Pesan</a>`;

                    drugHtml += `
                    <div class="col">
                        <div class="card h-100 card-custom border-top border-primary border-4 overflow-hidden">
                            <img src="${img}" class="card-img-top" style="height:180px; object-fit:cover">
                            <div class="card-body p-4 text-center">
                                <h6 class="fw-bold text-dark mb-1">${drug.name}</h6>
                                <p class="text-muted small mb-3">Tersedia: ${drug.stock} ${drug.unit}</p>
                                ${actionBtn}
                            </div>
                        </div>
                    </div>`;
                });
                document.getElementById('drugsContainer').innerHTML = drugHtml;
            });
        }

        function addToCart(id, name) {
            axios.post('/api/cart', { drug_id: id }).then(res => {
                updateCartBadge();
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: name + ' masuk keranjang',
                    showConfirmButton: false,
                    timer: 2000
                });
            }).catch(() => {
                Swal.fire('Gagal', 'Sesi Anda mungkin berakhir.', 'error');
            });
        }

        function updateCartBadge() {
            const badge = document.getElementById('cartBadge');
            if(!badge) return;
            axios.get('/api/cart').then(res => {
                if(res.data.length > 0) {
                    badge.innerText = res.data.length;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>