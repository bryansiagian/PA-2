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
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

  <!-- API Core (Axios & SweetAlert) -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
      body { font-family: 'Plus Jakarta Sans', sans-serif; }
      #cartBadge { font-size: 0.7rem; }
      .drug-card { transition: 0.3s; border: 1px solid #eee; border-radius: 15px; overflow: hidden; background: #fff; }
      .drug-card:hover { transform: translateY(-10px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
      .service-card { border-radius: 20px !important; box-shadow: 0 5px 20px rgba(0,0,0,0.05); transition: 0.3s; }
      .footer { background-color: #0f172a; color: #f8fafc; }

      /* Styling untuk Teaser */
      .content-excerpt { overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; margin-bottom: 15px; }
      .btn-read-more { font-size: 0.8rem; font-weight: 700; color: #3fbbc0; text-decoration: none; cursor: pointer; }
      .btn-read-more:hover { text-decoration: underline; }
  </style>
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top shadow-sm">
    <div class="container position-relative d-flex align-items-center justify-content-between">
      <a href="/" class="logo d-flex align-items-center me-auto me-xl-0 text-decoration-none">
        <h1 class="sitename">E-<span>Pharma</span></h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Beranda</a></li>
          <li><a href="#home-about">Tentang</a></li>
          <li><a href="#berita">Berita</a></li>
          <li><a href="#katalog">Katalog Obat</a></li>
          <li><a href="#organisasi">Organisasi</a></li>
          <li><a href="#galeri">Galeri</a></li>

          @auth
            @role('customer')
                <li>
                    <a href="/customer/cart" class="position-relative p-2">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span id="cartBadge" class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="display: none;">0</span>
                    </a>
                </li>
                <li><a href="/customer/history">Pesanan Saya</a></li>
            @endrole
          @endauth
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      @guest
        <a class="btn-getstarted shadow-sm" href="/login">Masuk</a>
      @else
        <div class="dropdown">
            <a class="btn-getstarted dropdown-toggle shadow-sm" href="#" data-bs-toggle="dropdown">{{ Auth::user()->name }}</a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 rounded-4">
                <li><a class="dropdown-item py-2" href="/dashboard"><i class="bi bi-speedometer2 me-2"></i> Panel Kerja</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-none">@csrf</form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item text-danger py-2 fw-bold"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a>
                </li>
            </ul>
        </div>
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
              <img src="https://images.unsplash.com/photo-1587854680352-936b22b91030?auto=format&fit=crop&w=800&q=80" alt="Logistik Farmasi" class="img-fluid main-image shadow-lg" style="border-radius: 20px;">
              <div class="floating-card emergency-card">
                <div class="card-content"><i class="bi bi-truck text-primary"></i><div class="text"><span class="label text-primary fw-bold">Pengiriman Cepat</span><span class="number small">Sistem Terintegrasi</span></div></div>
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="hero-content" data-aos="fade-left" data-aos-delay="200">
              <div class="badge-container"><span class="hero-badge bg-soft-primary text-light px-3 py-2 rounded-pill small fw-bold">E-PHARMA LOGISTICS HUB</span></div>
              <h1 class="hero-title mt-3">Logistik Farmasi Cepat & Transparan</h1>
              <p class="hero-description text-muted">Solusi terpadu untuk kebutuhan sediaan farmasi RS & Klinik dengan sistem pengawasan digital.</p>
              <div class="cta-buttons">
                <a href="#katalog" class="btn btn-primary rounded-pill px-5 py-3 shadow">Jelajahi Katalog</a>
                <a href="#home-about" class="btn btn-outline-primary rounded-pill px-5 py-3 ms-md-3 mt-3 mt-md-0">Tentang Kami</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Profil Section (TEASER MODE) -->
    <section id="home-about" class="home-about section py-5">
      <div class="container" data-aos="fade-up">
        <div class="row align-items-stretch gy-5">
          <div class="col-lg-6">
              <img src="https://images.unsplash.com/photo-1586015555751-63bb77f4322a?auto=format&fit=crop&w=800&q=80" alt="Gudang Farmasi" class="img-fluid rounded-5 shadow h-100" style="object-fit: cover;">
          </div>
          <div class="col-lg-6 ps-lg-5">
            <div class="content-wrapper">
                <h2 class="fw-bold mb-4" id="aboutTitle" style="color: #2c4964;">E-Pharma Profile</h2>
                <div id="aboutExcerpt" class="text-muted lh-lg mb-2">Memuat...</div>
                <a onclick="showFullContent('about')" class="btn-read-more mb-4 d-inline-block">Baca Selengkapnya...</a>

                <div class="d-flex align-items-start mb-4 p-3 bg-light rounded-4">
                    {{-- <div class="bg-primary text-white rounded-circle p-3 me-3"><i class="bi bi-clock-history fs-5"></i></div> --}}
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Sejarah Singkat</h6>
                        <div id="historyExcerpt" class="small text-muted mb-1">Memuat...</div>
                        <a onclick="showFullContent('history')" class="btn-read-more">Detail Sejarah</a>
                    </div>
                </div>

                <div class="d-flex align-items-start p-3 bg-light rounded-4">
                    {{-- <div class="bg-warning text-white rounded-circle p-3 me-3"><i class="bi bi-lightbulb fs-5"></i></div> --}}
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Visi & Misi</h6>
                        <div id="visionExcerpt" class="small text-muted mb-1">Memuat...</div>
                        <a onclick="showFullContent('vision_mission')" class="btn-read-more">Detail Visi Misi</a>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Berita, Katalog, Organisasi, Galeri Sections (Tetap) -->
    <section id="berita" class="featured-services section py-5" style="background-color: #f4f9f9;"><div class="container"><h2 class="section-title">Berita & Kegiatan</h2><div id="postsContainer" class="row gy-4"></div></div></section>
    <section id="katalog" class="section py-5"><div class="container"><h2 class="section-title">Katalog Produk Obat</h2><div id="drugsContainer" class="row gy-4"></div></div></section>
    <section id="organisasi" class="section py-5" style="background-color: #f4f9f9;"><div class="container"><h2 class="section-title">Struktur Organisasi</h2><div id="orgContainer" class="row gy-4 justify-content-center text-center"></div></div></section>
    <section id="galeri" class="section py-5"><div class="container"><h2 class="section-title">Galeri Dokumentasi</h2><div id="publicGalleryContainer" class="row gy-4"></div></div></section>

  </main>

  <!-- MODAL UNTUK DETAIL KONTEN PANJANG -->
  <div class="modal fade" id="contentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header border-0 pb-0">
          <h4 class="fw-bold" id="modalContentTitle" style="color: #2c4964;">Judul</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4 lh-lg text-muted" id="modalContentBody">
          <!-- Isi Lengkap -->
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <footer id="footer" class="footer py-5 mt-5">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="/" class="logo d-flex align-items-center text-decoration-none"><span class="sitename text-white fw-bold">E-PHARMA</span></a>
          <p id="footerAbout" class="mt-3 small opacity-75">Sistem logistik farmasi digital terpadu.</p>
        </div>
        <div class="col-lg-4 col-md-6 footer-links">
          <h5 class="fw-bold mb-3 text-white">Hubungi Kami</h5>
          <div id="dynamicContactList" class="footer-contact small opacity-75"></div>
        </div>
        <div class="col-lg-4 col-md-6 footer-links">
          <h5 class="fw-bold mb-3 text-white">Akses</h5>
          <ul class="list-unstyled small">
            <li><a href="/login" class="text-white opacity-75 text-decoration-none">Login Internal</a></li>
            <li><a href="/register" class="text-white opacity-75 text-decoration-none">Pendaftaran Mitra</a></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>

  <script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    // Variabel Global untuk menampung data profil lengkap
    let globalProfiles = {};

    document.addEventListener('DOMContentLoaded', () => {
        AOS.init();
        fetchLandingPage();
        fetchCatalog();
        @auth @role('customer') updateCartBadge(); @endrole @endauth
    });

    function fetchLandingPage() {
        axios.get('/api/public/landing-page').then(res => {
            const d = res.data;
            globalProfiles = d.profiles || {};

            // 1. Profil Section (Teaser)
            if(globalProfiles.about) {
                document.getElementById('aboutTitle').innerText = globalProfiles.about.title;
                document.getElementById('aboutExcerpt').innerText = globalProfiles.about.content.substring(0, 250) + '...';
                document.getElementById('footerAbout').innerText = globalProfiles.about.content.substring(0, 100) + '...';
            }
            if(globalProfiles.history) {
                document.getElementById('historyExcerpt').innerText = globalProfiles.history.content.substring(0, 120) + '...';
            }
            if(globalProfiles.vision_mission) {
                // Menghilangkan tag HTML untuk teaser teks murni
                const plainText = globalProfiles.vision_mission.content.replace(/<[^>]*>?/gm, '');
                document.getElementById('visionExcerpt').innerText = plainText.substring(0, 120) + '...';
            }

            // 2. Contacts (Footer)
            const c = d.contacts || {};
            let contactHtml = '';
            Object.values(c).forEach(item => {
                contactHtml += `<p class="mb-2"><strong>${item.title}:</strong> ${item.value}</p>`;
            });
            document.getElementById('dynamicContactList').innerHTML = contactHtml;

            // 3. Posts
            let postHtml = '';
            const allPosts = [...(d.news || []), ...(d.activities || [])];
            allPosts.forEach(post => {
                const img = post.image ? `/storage/${post.image}` : 'https://placehold.co/600x400';
                postHtml += `
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="drug-card h-100">
                        <img src="${img}" class="img-fluid" style="height:200px; width:100%; object-fit:cover">
                        <div class="p-4">
                            <span class="badge bg-primary mb-2">${post.category?.name || 'INFO'}</span>
                            <h5 class="fw-bold">${post.title}</h5>
                            <p class="small text-muted">${post.content.substring(0, 80)}...</p>
                        </div>
                    </div>
                </div>`;
            });
            document.getElementById('postsContainer').innerHTML = postHtml;

            // 4. Organization & Gallery (Logic disingkat demi ruang, tetap sama fungsinya)
            let orgHtml = ''; d.organization.forEach(o => {
                const img = o.photo ? `/storage/${o.photo}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(o.name)}&background=3fbbc0&color=fff`;
                orgHtml += `<div class="col-lg-3 col-md-6 text-center"><img src="${img}" class="rounded-circle mb-3 border" width="120" height="120" style="object-fit:cover"><h6 class="fw-bold mb-0">${o.name}</h6><p class="text-primary small">${o.position}</p></div>`;
            });
            document.getElementById('orgContainer').innerHTML = orgHtml;

            let galHtml = ''; d.gallery.forEach(g => {
                if(g.files?.length > 0) galHtml += `<div class="col-md-4"><div class="drug-card overflow-hidden"><img src="/${g.files[0].file_path}" class="img-fluid" style="height:200px; width:100%; object-fit:cover"><div class="p-3 text-center"><h6>${g.title}</h6></div></div></div>`;
            });
            document.getElementById('publicGalleryContainer').innerHTML = galHtml;
        });
    }

    // FUNGSI UNTUK MENAMPILKAN MODAL DETAIL (DIPANGGIL OLEH TOMBOL BACA SELENGKAPNYA)
    function showFullContent(key) {
        const data = globalProfiles[key];
        if(!data) return;

        document.getElementById('modalContentTitle').innerText = data.title;
        // Gunakan innerHTML agar list (ul/li) pada Visi Misi tampil benar
        document.getElementById('modalContentBody').innerHTML = data.content;

        const myModal = new bootstrap.Modal(document.getElementById('contentModal'));
        myModal.show();
    }

    function fetchCatalog() {
        axios.get('/api/public/drugs').then(res => {
            let html = '';
            res.data.forEach(drug => {
                const isCustomer = @auth @if(auth()->user()->hasRole('customer')) true @else false @endif @else false @endauth;
                const img = drug.image ? `/${drug.image}` : 'https://placehold.co/400x300';
                html += `
                <div class="col-lg-3 col-md-6">
                    <div class="drug-card p-3 text-center h-100">
                        <img src="${img}" class="img-fluid rounded-3 mb-3" style="height:150px; object-fit:cover">
                        <h6 class="fw-bold">${drug.name}</h6>
                        <p class="small text-muted mb-3">Stok: ${drug.stock} ${drug.unit}</p>
                        ${isCustomer ? `<button onclick="addToCart(${drug.id}, '${drug.name}')" class="btn btn-primary btn-sm w-100 rounded-pill">Pesan Sekarang</button>` : `<a href="/login" class="btn btn-outline-secondary btn-sm w-100 rounded-pill">Masuk untuk Pesan</a>`}
                    </div>
                </div>`;
            });
            document.getElementById('drugsContainer').innerHTML = html;
        });
    }

    function addToCart(id, name) {
        axios.post('/api/cart', { drug_id: id }).then(() => {
            updateCartBadge();
            Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: name + ' masuk keranjang', showConfirmButton: false, timer: 2000 });
        });
    }

    function updateCartBadge() {
        const badge = document.getElementById('cartBadge');
        axios.get('/api/cart').then(res => {
            if(res.data.length > 0) { badge.innerText = res.data.length; badge.style.display = 'block'; }
            else { badge.style.display = 'none'; }
        });
    }
  </script>
</body>
</html>