@extends('layouts.portal')

@section('content')
<div class="container mt-4">
    <!-- Hero Banner -->
    <div class="p-5 mb-5 bg-dark text-white rounded-4 shadow-lg position-relative overflow-hidden"
         style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1586015555751-63bb77f4322a?w=1200'); background-size: cover; background-position: center;">
        <div class="position-relative">
            <h1 class="display-5 fw-bold text-white">Katalog Logistik Farmasi</h1>
            <p class="lead col-md-8 text-white-50">Gunakan kolom pencarian di navbar atas untuk menemukan stok obat Anda.</p>
        </div>
    </div>

    <!-- Filter & Sort UI -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div id="searchStatus" class="text-muted small">
            <!-- Info pencarian akan muncul di sini -->
        </div>
        <div class="dropdown shadow-sm">
            <button class="btn btn-white border dropdown-toggle fw-bold bg-white" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-sort-down me-1"></i> <span id="sortLabel">Urutkan</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item" href="#" onclick="applySort('default', 'Urutkan')">Default</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="applySort('name-az', 'Nama (A-Z)')">Nama (A-Z)</a></li>
                <li><a class="dropdown-item" href="#" onclick="applySort('stock-high', 'Stok Terbanyak')">Stok Terbanyak</a></li>
                <li><a class="dropdown-item" href="#" onclick="applySort('stock-low', 'Stok Terendah')">Stok Terendah</a></li>
            </ul>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
    </div>

    <!-- Grid Katalog -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="drugCatalog">
        <!-- Data dimuat oleh renderCatalog() -->
    </div>
</div>

<script>
    let allDrugs = [];
    let currentSort = 'default';
    let debounceTimer;

    /**
     * 1. FUNGSI UTAMA: TAMBAH KE KERANJANG
     * Fungsi ini harus ada agar tombol "Pesan" berfungsi
     */
     function addToCart(id, name, unit, img) {
        // 1. Kirim ke Database via API
        axios.post('/api/cart', { drug_id: id })
            .then(res => {
                // 2. Update Badge di Navbar (ambil dari DB)
                updateCartBadge();

                // 3. Notifikasi
                const Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2000 });
                Toast.fire({ icon: 'success', title: name + ' masuk keranjang database' });
            })
            .catch(err => Swal.fire('Error', 'Gagal menambah keranjang', 'error'));
    }

    /**
     * 2. PENGAMBILAN DATA AWAL
     */
    function fetchInitialData() {
        axios.get('/api/drugs')
            .then(res => {
                allDrugs = res.data;
                renderCatalog(allDrugs);
                document.getElementById('loading').classList.add('d-none');
            })
            .catch(err => {
                console.error("Gagal memuat data:", err);
                document.getElementById('loading').innerHTML = '<p class="text-danger">Gagal memuat katalog.</p>';
            });
    }

    /**
     * 3. LOGIKA PENCARIAN (DEBOUNCE)
     */
    const navbarSearch = document.getElementById('globalSearchInput');
    if (navbarSearch) {
        navbarSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value;
            document.getElementById('searchStatus').innerHTML = `<small class="text-muted"><i class="spinner-border spinner-border-sm"></i> Mencari...</small>`;

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterAndSortData();
            }, 500);
        });
    }

    function filterAndSortData() {
        const searchTerm = navbarSearch ? navbarSearch.value.toLowerCase() : '';
        const statusDiv = document.getElementById('searchStatus');

        // Filter
        let filtered = allDrugs.filter(d =>
            d.name.toLowerCase().includes(searchTerm) ||
            d.sku.toLowerCase().includes(searchTerm)
        );

        // Sort
        if (currentSort === 'name-az') filtered.sort((a,b) => a.name.localeCompare(b.name));
        else if (currentSort === 'stock-high') filtered.sort((a,b) => b.stock - a.stock);
        else if (currentSort === 'stock-low') filtered.sort((a,b) => a.stock - b.stock);

        // UI Update
        statusDiv.innerHTML = searchTerm ? `Menampilkan hasil untuk: <b>"${searchTerm}"</b>` : '';
        renderCatalog(filtered);
    }

    /**
     * 4. RENDER DATA KE HTML
     */
    function renderCatalog(data) {
        const container = document.getElementById('drugCatalog');
        let html = '';

        if (data.length === 0) {
            container.innerHTML = `<div class="col-12 text-center py-5 text-muted">Obat tidak ditemukan.</div>`;
            return;
        }

        data.forEach(d => {
            const img = d.image ? d.image : 'https://placehold.co/400x300?text=No+Image';

            // Kita bungkus pemanggilan fungsi dengan string escape agar aman jika ada nama obat pake tanda kutip
            const safeName = d.name.replace(/'/g, "\\'");

            html += `
            <div class="col">
                <div class="card h-100 product-card border-0 shadow-sm rounded-4 overflow-hidden">
                    <img src="${img}" class="card-img-top" style="height: 180px; object-fit: cover;">
                    <div class="card-body p-3 d-flex flex-column">
                        <small class="text-primary fw-bold mb-1">${d.category ? d.category.name : 'Umum'}</small>
                        <h6 class="fw-bold mb-1 text-truncate" title="${d.name}">${d.name}</h6>
                        <p class="text-muted small mb-3">Unit: ${d.unit}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem">Stok</small>
                                <span class="fw-bold fs-5">${d.stock}</span>
                            </div>
                            <button onclick="addToCart(${d.id}, '${safeName}', '${d.unit}', '${img}')"
                                    class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                <i class="bi bi-cart-plus"></i> Pesan
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        container.innerHTML = html;
    }

    function applySort(type, label) {
        currentSort = type;
        document.getElementById('sortLabel').innerText = label;
        filterAndSortData();
    }

    document.addEventListener('DOMContentLoaded', fetchInitialData);
</script>

<style>
    .product-card { transition: all 0.3s ease; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>
@endsection