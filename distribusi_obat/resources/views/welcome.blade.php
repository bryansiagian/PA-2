<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Yayasan E-Pharma - Logistik Farmasi Terpadu</title>

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
  <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

  <!-- API Core (Axios & SweetAlert) -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
      #cartBadge { font-size: 0.7rem; }
      .drug-card { transition: 0.3s; border: 1px solid #eee; }
      .drug-card:hover { transform: translateY(-10px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
  </style>
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

      <a href="/" class="logo d-flex align-items-center me-auto me-xl-0">
        <h1 class="sitename">E-<span>Pharma</span></h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Beranda</a></li>
          <li><a href="#home-about">Tentang</a></li>
          <li><a href="#berita">Berita</a></li>
          <li><a href="#katalog">Katalog Obat</a></li>
          <li><a href="#organisasi">Organisasi</a></li>

          @auth
            @role('customer')
                <li>
                    <a href="/customer/cart" class="position-relative">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span id="cartBadge" class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="display: none;">0</span>
                    </a>
                </li>
                <li><a href="/customer/history">Pesanan Saya</a></li>
            @endrole
            <li>
                <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-none">@csrf</form>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger">Keluar</a>
            </li>
          @endauth
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      @guest
        <a class="btn-getstarted" href="/login">Masuk</a>
      @else
        <a class="btn-getstarted" href="/dashboard">Panel Kerja</a>
      @endguest

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-5">
            <div class="hero-image" data-aos="fade-right">
              <img src="https://images.unsplash.com/photo-1587854680352-936b22b91030?auto=format&fit=crop&w=800&q=80" alt="Logistik Farmasi" class="img-fluid main-image" style="border-radius: 20px;">
              <div class="floating-card emergency-card" data-aos="fade-up" data-aos-delay="300">
                <div class="card-content">
                  <i class="bi bi-truck"></i>
                  <div class="text">
                    <span class="label">Pengiriman Cepat</span>
                    <span class="number">Sistem Terintegrasi</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="hero-content" data-aos="fade-left" data-aos-delay="200">
              <div class="badge-container">
                <span class="hero-badge">Logistik Farmasi Terpadu</span>
              </div>
              <h1 class="hero-title">Logistik Farmasi Cepat & Transparan</h1>
              <p class="hero-description">Sistem terpadu Yayasan E-Pharma untuk distribusi obat-obatan ke seluruh unit kesehatan dengan pengawasan stok digital secara real-time.</p>

              <div class="cta-section">
                <div class="cta-buttons">
                  <a href="#katalog" class="btn btn-primary">Jelajahi Katalog</a>
                  <a href="#home-about" class="btn btn-secondary">Tentang Kami</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Profil Section (Mapping dari Home About) -->
    <section id="home-about" class="home-about section">
      <div class="container" data-aos="fade-up">
        <div class="row align-items-center gy-5">
          <div class="col-lg-7">
            <div class="image-grid">
              <div class="primary-image">
                <img src="https://images.unsplash.com/photo-1586015555751-63bb77f4322a?auto=format&fit=crop&w=800&q=80" alt="Gudang Farmasi" class="img-fluid">
              </div>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="content-wrapper">
                <h2 class="section-heading" id="aboutTitle">Memuat...</h2>
                <p id="aboutContent" class="mb-4">Sedang memuat informasi...</p>

                <div class="highlight-box mb-4">
                    <div class="highlight-icon"><i class="bi bi-clock-history"></i></div>
                    <div class="highlight-content">
                        <h4>Sejarah Singkat</h4>
                        <p id="historyContent" class="small">Memuat sejarah...</p>
                    </div>
                </div>

                <div class="highlight-box">
                    <div class="highlight-icon"><i class="bi bi-lightbulb"></i></div>
                    <div class="highlight-content">
                        <h4>Visi & Misi</h4>
                        <div id="visionMissionContent" class="small text-muted">Memuat visi misi...</div>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Berita Section (Mapping dari Featured Services) -->
    <section id="berita" class="featured-services section light-background">
      <div class="container section-title" data-aos="fade-up">
        <h2>Berita & Kegiatan</h2>
        <p>Informasi terbaru seputar distribusi dan kegiatan Yayasan E-Pharma</p>
      </div>
      <div class="container">
        <div id="postsContainer" class="row gy-4">
            <!-- Data API Berita akan masuk ke sini -->
        </div>
      </div>
    </section>

    <!-- Katalog Obat Section (Mapping dari Find A Doctor) -->
    <section id="katalog" class="find-a-doctor section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Katalog Produk Obat</h2>
        <p>Daftar obat-obatan tersedia yang dapat dipesan oleh unit kesehatan</p>
      </div>
      <div class="container">
        <div id="drugsContainer" class="row gy-4">
            <!-- Data API Katalog akan masuk ke sini -->
        </div>
      </div>
    </section>

    <!-- Organisasi Section -->
    <section id="organisasi" class="section light-background">
        <div class="container section-title" data-aos="fade-up">
            <h2>Struktur Organisasi</h2>
            <p>Tenaga profesional di balik layanan Yayasan E-Pharma</p>
        </div>
        <div class="container">
            <div id="orgContainer" class="row gy-4 justify-content-center text-center">
                <!-- Data API Organisasi masuk ke sini -->
            </div>
        </div>
    </section>

  </main>

  <footer id="footer" class="footer">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="/" class="logo d-flex align-items-center">
            <span class="sitename">E-PHARMA</span>
          </a>
          <p id="footerAbout" class="mt-3">Sistem manajemen gudang dan distribusi obat terpadu.</p>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 footer-links">
          <h4>Hubungi Kami</h4>
          <div class="footer-contact">
            <p id="footerAddress">-</p>
            <p class="mt-3"><strong>Phone:</strong> <span id="footerPhone">-</span></p>
            <p><strong>Email:</strong> <span id="footerEmail">-</span></p>
            <p><strong>WhatsApp:</strong> <span id="footerWA">-</span></p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 footer-links">
          <h4>Tautan Cepat</h4>
          <ul>
            <li><a href="/login">Login Petugas</a></li>
            <li><a href="/register">Pendaftaran Unit</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© 2024 <strong>Yayasan E-Pharma</strong>. All Rights Reserved</p>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
  <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>

  <!-- Main JS File -->
  <script src="{{ asset('assets/js/main.js') }}"></script>

  <!-- Logic API E-Pharma -->
  <script>
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

            if(p.about) {
                document.getElementById('aboutTitle').innerText = p.about.title;
                document.getElementById('aboutContent').innerText = p.about.content;
                document.getElementById('footerAbout').innerText = p.about.content.substring(0, 150) + '...';
            }
            if(p.history) document.getElementById('historyContent').innerText = p.history.content;
            if(p.vision_mission) document.getElementById('visionMissionContent').innerHTML = p.vision_mission.content;

            document.getElementById('footerAddress').innerText = p.contact_address?.content || '-';
            document.getElementById('footerPhone').innerText = p.contact_phone?.content || '-';
            document.getElementById('footerEmail').innerText = p.contact_email?.content || '-';
            document.getElementById('footerWA').innerText = p.contact_wa?.content || '-';

            // News Mapping to Template Cards
            let postHtml = '';
            [...d.news, ...d.activities].forEach(post => {
                const img = post.image ? `/storage/${post.image}` : 'https://placehold.co/600x400?text=E-Pharma';
                postHtml += `
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="service-card" style="padding: 0; overflow: hidden; height: 100%;">
                        <img src="${img}" class="img-fluid" style="height:200px; width:100%; object-fit:cover">
                        <div style="padding: 20px;">
                            <span class="badge bg-primary mb-2">${post.type.toUpperCase()}</span>
                            <h4 style="font-size: 1.2rem;">${post.title}</h4>
                            <p class="small">${post.content.substring(0, 100)}...</p>
                        </div>
                    </div>
                </div>`;
            });
            document.getElementById('postsContainer').innerHTML = postHtml || '<p class="text-center w-100">Belum ada berita.</p>';

            // Org Mapping
            let orgHtml = '';
            d.organization.forEach(o => {
                const img = o.photo ? `/storage/${o.photo}` : 'https://ui-avatars.com/api/?name='+o.name+'&background=0D6EFD&color=fff';
                orgHtml += `
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <img src="${img}" class="rounded-circle mb-3 border shadow-sm" width="120" height="120" style="object-fit:cover">
                    <h5 class="fw-bold mb-0">${o.name}</h5>
                    <p class="text-primary small fw-bold">${o.position}</p>
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
                    ? `<button onclick="addToCart(${drug.id}, '${drug.name}')" class="btn btn-primary w-100 rounded-pill">Pesan Sekarang</button>`
                    : `<a href="/login" class="btn btn-outline-secondary w-100 rounded-pill">Login untuk Pesan</a>`;

                drugHtml += `
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="drug-card bg-white p-3 rounded-4 text-center h-100">
                        <img src="${img}" class="img-fluid rounded-3 mb-3" style="height:150px; object-fit:cover">
                        <h6 class="fw-bold mb-1">${drug.name}</h6>
                        <p class="text-muted small mb-3">Stok: ${drug.stock} ${drug.unit}</p>
                        ${actionBtn}
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
                toast: true, position: 'bottom-end', icon: 'success',
                title: name + ' masuk keranjang',
                showConfirmButton: false, timer: 2000
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