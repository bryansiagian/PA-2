<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMS Control Panel - E-Pharma</title>

    <!-- CSS - Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Fonts - Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Core JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-width: 260px;
            --primary-accent: #3b82f6;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #334155; overflow-x: hidden; }

        /* --- SIDEBAR SCROLLABLE CORE --- */
        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: #fff;
            transition: all 0.3s ease-in-out;
            height: 100vh;
            position: fixed;
            z-index: 1050; /* Di atas navbar */
            overflow-y: auto; /* Aktifkan Scroll di Sidebar */
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
            display: flex;
            flex-direction: column;
        }

        /* Custom Scrollbar Styling */
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

        /* Sidebar Header & Menu */
        #sidebar .sidebar-header {
            padding: 2rem 1.5rem;
            background: #1e293b;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            position: sticky;
            top: 0;
            z-index: 1;
        }

        #sidebar .menu-label {
            padding: 1.5rem 1.5rem 0.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 700;
        }

        #sidebar ul li a {
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.875rem;
            margin: 0.2rem 0.8rem;
            border-radius: 0.5rem;
        }

        #sidebar ul li a i { font-size: 1.1rem; margin-right: 12px; }
        #sidebar ul li a:hover { color: #fff; background: var(--sidebar-hover); }
        #sidebar ul li a.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary-accent);
            font-weight: 600;
        }

        /* --- CONTENT AREA LOGIC --- */
        #content {
            width: 100%;
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Logic Toggle Desktop */
        #sidebar.active { margin-left: calc(-1 * var(--sidebar-width)); }
        #content.active { margin-left: 0; }

        /* --- MOBILE LOGIC (OFF-CANVAS) --- */
        #sidebar-overlay {
            display: none;
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            top: 0;
            left: 0;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 768px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
            #sidebar.show { margin-left: 0; } /* Muncul di Mobile */
            #content { margin-left: 0 !important; }
            #sidebar-overlay.show { display: block; }
        }

        /* Navbar Styling */
        .navbar-main {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 2rem;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .card { border: none; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body>
    <!-- Backdrop Overlay untuk Mobile -->
    <div id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="d-flex">
        <!-- SIDEBAR -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h4 class="fw-bold mb-0 text-white"><i class="bi bi-capsule-pill text-primary me-2"></i>E-PHARMA</h4>
                <p class="small text-muted mb-0" style="font-size: 10px; letter-spacing: 2px;">LOGISTICS CENTER</p>
            </div>

            <ul class="list-unstyled flex-grow-1">
                <li class="menu-label">Main Menu</li>
                <li><a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}"><i class="bi bi-house-door"></i> Dashboard</a></li>

                @can('view audit logs')
                <li class="menu-label">Analytics</li>
                <li><a href="/reports" class="{{ request()->is('reports') ? 'active' : '' }}"><i class="bi bi-bar-chart-line"></i> Laporan Distribusi</a></li>
                @endcan

                @can('manage users')
                <li class="menu-label">Verification</li>
                <li><a href="/admin/users/pending" class="{{ request()->is('admin/users/pending') ? 'active' : '' }}"><i class="bi bi-person-check"></i> Permintaan Akun</a></li>
                @endcan

                @role('admin')
                <li class="menu-label">System Control</li>
                <li><a href="/admin/users" class="{{ request()->is('admin/users') ? 'active' : '' }}"><i class="bi bi-people"></i> Kelola Pengguna</a></li>
                <li><a href="/admin/logs" class="{{ request()->is('admin/logs') ? 'active' : '' }}"><i class="bi bi-shield-lock"></i> Audit Sistem</a></li>
                <li class="menu-label">CMS Landing Page</li>
                <li><a href="/admin/cms/profile"><i class="bi bi-info-circle"></i> Profil & Visi Misi</a></li>
                <li><a href="/admin/cms/posts"><i class="bi bi-newspaper"></i> Berita & Kegiatan</a></li>
                <li><a href="/admin/cms/post-categories" class="{{ request()->is('admin/cms/post-categories') ? 'active' : '' }}"><i class="bi bi-tags"></i> Kategori Post</a></li>
                <li><a href="/admin/cms/gallery"><i class="bi bi-images"></i> Galeri Foto</a></li>
                <li><a href="/admin/cms/org"><i class="bi bi-diagram-3"></i> Struktur Organisasi</a></li>
                <li><a href="/admin/cms/contacts" class="{{ request()->is('admin/cms/contacts') ? 'active' : '' }}"><i class="bi bi-telephone-outbound"></i> Kontak & Sosmed</a></li>
                <li><a href="/admin/cms/files" class="{{ request()->is('admin/cms/files') ? 'active' : '' }}"><i class="bi bi-file-earmark-arrow-up"></i> Dokumen & File</a></li>
                @endrole

                @can('manage inventory')
                <li class="menu-label">Warehouse</li>
                <li><a href="/operator/drugs" class="{{ request()->is('operator/drugs') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> Stok & Katalog</a></li>
                <li><a href="/operator/categories" class="{{ request()->is('operator/categories') ? 'active' : '' }}"><i class="bi bi-tags"></i> Kelola Kategori</a></li>
                <li><a href="/operator/requests" class="{{ request()->is('operator/requests') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i> Antrian Request</a></li>
                @endcan

                @role('courier')
                <li class="menu-label">Logistics</li>
                <li><a href="/courier/available" class="{{ request()->is('courier/available') ? 'active' : '' }}"><i class="bi bi-megaphone"></i> Bursa Tugas</a></li>
                <li><a href="/courier/active" class="{{ request()->is('courier/active') ? 'active' : '' }}"><i class="bi bi-bicycle"></i> Tugas Aktif</a></li>
                <li><a href="/courier/history" class="{{ request()->is('courier/history') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Riwayat Selesai</a></li>
                @endrole
            </ul>

            <!-- Tambahan: Logout di bagian bawah sidebar untuk mobile -->
            <div class="d-md-none p-3 border-top border-secondary">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill btn-sm">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <div id="content">
            <nav class="navbar navbar-main navbar-expand-lg">
                <button type="button" id="sidebarCollapse" class="btn btn-light shadow-sm" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>

                <div class="ms-auto d-flex align-items-center">
                    <div class="me-3 text-end d-none d-md-block">
                        <p class="mb-0 fw-bold small text-dark">{{ Auth::user()->name }}</p>
                        <span class="badge bg-light text-primary border border-primary border-opacity-25" style="font-size: 9px;">
                            {{ strtoupper(Auth::user()->getRoleNames()->first() ?? 'User') }}
                        </span>
                    </div>

                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D6EFD&color=fff&bold=true"
                                 class="rounded-circle shadow-sm border border-2 border-white" width="40" height="40">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 rounded-3">
                            <li class="px-3 py-2 border-bottom">
                                <small class="text-muted d-block">Sesi Aktif sebagai:</small>
                                <span class="text-primary small fw-bold text-uppercase">{{ Auth::user()->getRoleNames()->first() }}</span>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                @yield('content')
            </div>

            <footer class="mt-auto py-3 bg-white border-top text-center">
                <small class="text-muted">&copy; 2024 E-Pharma Management System. Professional PBL Edition.</small>
            </footer>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const overlay = document.getElementById('sidebar-overlay');

        // FUNGSI TOGGLE SIDEBAR (Responsive)
        function toggleSidebar() {
            const isMobile = window.innerWidth <= 768;

            if (isMobile) {
                // Di HP: Gunakan class 'show' dan Overlay
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
                // Pastikan class desktop dimatikan
                sidebar.classList.remove('active');
                content.classList.remove('active');
            } else {
                // Di Desktop: Gunakan class 'active' untuk push content
                sidebar.classList.toggle('active');
                content.classList.toggle('active');
                // Pastikan class mobile dimatikan
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        }

        // Global Axios Configuration
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['Accept'] = 'application/json';

        // Auto-close sidebar jika ukuran layar berubah mendadak
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    </script>
</body>
</html>