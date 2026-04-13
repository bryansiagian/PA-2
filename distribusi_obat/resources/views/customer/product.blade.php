<script>
axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

let allProducts = [];

document.addEventListener('DOMContentLoaded', () => {
    fetchCategories();
    fetchProducts();
});

function fetchCategories() {
    axios.get('/api/product-categories').then(res => {
        let opt = '<option value="">Semua Kategori</option>';
        res.data.forEach(c => opt += `<option value="${c.name}">${c.name}</option>`);
        document.getElementById('categoryFilter').innerHTML = opt;
    });
}

function fetchProducts() {
    axios.get('/api/public/products').then(res => {
        allProducts = res.data;
        renderProducts(allProducts);
    });
}

/**
 * ✅ RENDER (SUDAH HANDLE STOK)
 */
function renderProducts(data) {
    let html = '';
    data.forEach(p => {
        const img = p.image ? `/${p.image}` : 'https://placehold.co/400x300';

        html += `
        <div class="col-lg-3 col-md-4 col-6 product-item">
            <div class="product-card bg-white p-3 d-flex flex-column justify-content-between shadow-sm">

                <div class="cursor-pointer" onclick="openDetail('${p.id}')">
                    <div class="bg-light rounded-4 mb-2 p-1 text-center">
                        <img src="${img}" class="img-fluid rounded-3" style="height:140px; object-fit:contain;">
                    </div>
                    <span class="category-badge">${p.category?.name || 'Umum'}</span>
                    <h6 class="fw-bold text-dark text-truncate">${p.name}</h6>
                    <p class="text-muted small">Stok: ${p.stock} ${p.unit}</p>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <span class="price-text small">Rp${Number(p.price).toLocaleString()}</span>

                    ${
                        p.stock > 0
                        ? `<button onclick="addToCart('${p.id}', '${p.name}', ${p.stock})" class="btn-cart">
                                <i class="bi bi-cart-plus"></i>
                           </button>`
                        : `<button class="btn-cart" disabled style="opacity:0.5">
                                <i class="bi bi-x"></i>
                           </button>`
                    }
                </div>

            </div>
        </div>`;
    });

    document.getElementById('productGrid').innerHTML = html;
}

/**
 * ✅ FILTER
 */
function filterProducts() {
    const keyword = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;

    const filtered = allProducts.filter(p => {
        const matchName = p.name.toLowerCase().includes(keyword) || p.sku.toLowerCase().includes(keyword);
        const matchCat = category === "" || (p.category && p.category.name === category);
        return matchName && matchCat;
    });

    renderProducts(filtered);
}

/**
 * ✅ MODAL DETAIL (FIXED)
 */
function openDetail(id) {
    const p = allProducts.find(item => item.id === id);
    if(!p) return;

    document.getElementById('modalDetailImg').src = p.image ? `/${p.image}` : 'https://placehold.co/400x300';
    document.getElementById('modalDetailName').innerText = p.name;
    document.getElementById('modalDetailSku').innerText = `SKU: ${p.sku}`;
    document.getElementById('modalDetailCategory').innerText = p.category?.name || 'Umum';
    document.getElementById('modalDetailStock').innerText = `${p.stock} ${p.unit}`;
    document.getElementById('modalDetailUnit').innerText = p.unit;
    document.getElementById('modalDetailPrice').innerText = `Rp${Number(p.price).toLocaleString()}`;
    document.getElementById('modalDetailDesc').innerText = p.description || '-';

    // ✅ tombol modal ikut cek stok
    document.getElementById('modalAddToCartBtn').onclick = () => addToCart(p.id, p.name, p.stock);
    document.getElementById('modalQuickOrderBtn').onclick = () => quickOrder(p.id, p.name, p.stock);

    new bootstrap.Modal(document.getElementById('productDetailModal')).show();
}

/**
 * ✅ ADD TO CART (FIXED)
 */
function addToCart(id, name, stock) {

    if (stock <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Stok Kosong',
            text: 'Tidak bisa menambahkan karena stok habis'
        });
        return;
    }

    axios.post('/api/cart', { product_id: id }).then(() => {
        Swal.fire({
            toast:true,
            position:'bottom-end',
            icon:'success',
            title: name + ' masuk keranjang',
            showConfirmButton:false,
            timer:2000
        });

        if(window.updateCartBadge) window.updateCartBadge();
    });
}

/**
 * ✅ QUICK ORDER (FIXED)
 */
function quickOrder(id, name, stock) {

    if (stock <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Stok Kosong',
            text: 'Tidak bisa memesan karena stok habis'
        });
        return;
    }

    Swal.fire({
        title: 'Pesan Sekarang?',
        text: `Kirim permintaan 1 unit ${name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3fbbc0',
        confirmButtonText: 'Ya, Kirim'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post('/api/orders/quick', { product_id: id })
                .then(() => {
                    Swal.fire('Berhasil!', 'Pesanan diproses.', 'success')
                        .then(() => window.location.href = '/customer/history');
                });
        }
    });
}
</script>
