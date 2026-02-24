@extends('layouts.portal')

@section('content')
<style>
    /* Styling khusus menyelaraskan dengan MediNest */
    .page-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 40px 0;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 40px;
    }
    .section-heading {
        color: #2c4964;
        font-weight: 700;
        position: relative;
        padding-bottom: 15px;
        margin-bottom: 0;
    }
    .section-heading::after {
        content: "";
        position: absolute;
        display: block;
        width: 50px;
        height: 3px;
        background: #3fbbc0;
        bottom: 0;
        left: 0;
    }
    .card-cart {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        background: #fff;
        border-left: 5px solid #3fbbc0;
    }
    .card-summary {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .summary-header {
        background: #f8f9fa;
        border-radius: 20px 20px 0 0;
        padding: 20px;
        border-bottom: 1px solid #eee;
    }
    .qty-control {
        background: #f1f7f8;
        border-radius: 25px;
        padding: 5px;
        border: 1px solid #deebec;
    }
    .btn-qty {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #deebec;
        color: #3fbbc0;
        transition: 0.3s;
    }
    .btn-qty:hover {
        background: #3fbbc0;
        color: #fff;
    }
    .btn-medinest {
        background: #3fbbc0;
        color: white;
        border-radius: 25px;
        padding: 12px 20px;
        font-weight: 600;
        transition: 0.3s;
        border: none;
    }
    .btn-medinest:hover {
        background: #329ea2;
        color: white;
        box-shadow: 0 5px 15px rgba(63, 187, 192, 0.3);
    }
    .btn-medinest:disabled {
        background: #ccc;
    }
    .empty-cart-icon {
        color: #3fbbc0;
        opacity: 0.15;
    }
</style>

<!-- Header Section -->
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="section-heading">Keranjang Permintaan</h2>
                <p class="text-muted">Tinjau daftar obat yang akan diajukan ke gudang farmasi pusat.</p>
            </div>
            <div class="col-md-5 text-md-end">
                <button id="btnClearCart" onclick="clearCart()" class="btn btn-outline-danger rounded-pill px-4" style="display: none;">
                    <i class="bi bi-trash3 me-1"></i> Kosongkan Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <!-- SISI KIRI: DAFTAR ITEM -->
        <div class="col-lg-8">
            <div id="cartItemsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="text-muted small mt-3">Menyinkronkan data medis...</p>
                </div>
            </div>
        </div>

        <!-- SISI KANAN: RINGKASAN & CHECKOUT -->
        <div class="col-lg-4">
            <div class="card card-summary sticky-top" style="top: 100px;">
                <div class="summary-header">
                    <h5 class="fw-bold m-0 text-dark"><i class="bi bi-receipt me-2 text-info"></i>Ringkasan Pesanan</h5>
                </div>
                <div class="card-body p-4">
                    <!-- PILIHAN METODE PENGAMBILAN -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Metode Pengambilan</label>
                        <select id="request_type" class="form-select rounded-3 py-2 shadow-none border-1">
                            <option value="delivery" selected>🚚 Kirim via Kurir Logistik</option>
                            <option value="self_pickup">🏢 Ambil Mandiri ke Gudang</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Jenis Item</span>
                        <span class="fw-bold" id="totalKinds">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Total Kuantitas</span>
                        <span class="fw-bold text-info fs-5" id="totalQty">0</span>
                    </div>

                    <hr class="my-4 opacity-25">

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Catatan Tambahan</label>
                        <textarea id="checkoutNotes" class="form-control rounded-3 bg-light border-0 small" rows="3" placeholder="Contoh: Unit Gawat Darurat, Gedung B..."></textarea>
                    </div>

                    <button id="btnCheckout" onclick="processCheckout()" class="btn btn-medinest w-100 shadow-sm mb-3" disabled>
                        Kirim Permintaan <i class="bi bi-send-fill ms-2"></i>
                    </button>

                    <a href="/#katalog" class="btn btn-light w-100 rounded-pill text-muted small">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Obat Lain
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchCart() {
        const container = document.getElementById('cartItemsContainer');
        const btnClear = document.getElementById('btnClearCart');
        const btnCheckout = document.getElementById('btnCheckout');

        axios.get('/api/cart')
            .then(res => {
                const carts = res.data;
                let html = '';
                let totalQuantity = 0;

                if (!carts || carts.length === 0) {
                    btnClear.style.display = 'none';
                    btnCheckout.disabled = true;
                    html = `
                        <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-dashed">
                            <i class="bi bi-cart-x empty-cart-icon" style="font-size: 6rem;"></i>
                            <h4 class="fw-bold mt-4">Keranjang Masih Kosong</h4>
                            <p class="text-muted mx-auto" style="max-width: 350px;">Anda belum memilih obat apapun untuk diajukan permintaan stoknya.</p>
                            <a href="/#katalog" class="btn btn-medinest px-5 mt-3 shadow">Lihat Katalog</a>
                        </div>`;
                } else {
                    btnClear.style.display = 'inline-block';
                    btnCheckout.disabled = false;

                    carts.forEach(item => {
                        const drug = item.drug || {};
                        const img = drug.image ? `/${drug.image}` : 'https://placehold.co/400x300?text=Produk+Obat';
                        totalQuantity += item.quantity;

                        html += `
                        <div class="card card-cart mb-3 shadow-sm overflow-hidden">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-3 col-md-2">
                                        <img src="${img}" class="rounded-3 border" style="width: 100%; height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col-9 col-md-5">
                                        <h6 class="fw-bold text-dark mb-1" style="font-family: 'Poppins', sans-serif;">${drug.name || 'Obat Tidak Diketahui'}</h6>
                                        <div class="badge bg-light text-info border px-2 py-1 mb-1" style="font-size: 10px;">
                                            Stok Tersedia: ${drug.stock} ${drug.unit || ''}
                                        </div>
                                        <div class="text-muted small">Kemasan: ${drug.unit || '-'}</div>
                                    </div>
                                    <div class="col-7 col-md-3 mt-3 mt-md-0">
                                        <div class="qty-control d-flex align-items-center justify-content-between">
                                            <button class="btn btn-qty" onclick="changeQty(${item.id}, ${parseInt(item.quantity) - 1}, ${drug.stock})">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number"
                                                   class="form-control text-center border-0 bg-transparent fw-bold p-0 shadow-none"
                                                   style="width: 50px;"
                                                   value="${item.quantity}"
                                                   onblur="changeQty(${item.id}, this.value, ${drug.stock})"
                                                   onkeyup="if(event.keyCode === 13) changeQty(${item.id}, this.value, ${drug.stock})">
                                            <button class="btn btn-qty" onclick="changeQty(${item.id}, ${parseInt(item.quantity) + 1}, ${drug.stock})">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-5 col-md-2 mt-3 mt-md-0 text-end">
                                        <button class="btn btn-sm text-danger fw-600" onclick="deleteItem(${item.id})">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                }

                container.innerHTML = html;
                document.getElementById('totalKinds').innerText = carts.length;
                document.getElementById('totalQty').innerText = totalQuantity;
                if(typeof updateCartBadge === 'function') updateCartBadge();
            });
    }

    function changeQty(cartId, newQty, maxStock) {
        const qty = parseInt(newQty);
        if (qty > maxStock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Terbatas',
                text: `Persediaan di gudang saat ini hanya ${maxStock} item.`,
                confirmButtonColor: '#3fbbc0'
            });
            fetchCart(); return;
        }
        if (isNaN(qty) || qty < 1) return deleteItem(cartId);

        axios.put(`/api/cart/${cartId}`, { quantity: qty }).then(() => fetchCart());
    }

    function deleteItem(cartId) {
        axios.delete(`/api/cart/${cartId}`).then(() => {
            fetchCart();
            if(typeof updateCartBadge === 'function') updateCartBadge();
        });
    }

    function clearCart() {
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            text: 'Semua item yang Anda pilih akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3fbbc0',
            confirmButtonText: 'Ya, Kosongkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete('/api/cart-clear').then(() => {
                    fetchCart();
                    if(typeof updateCartBadge === 'function') updateCartBadge();
                });
            }
        });
    }

    function processCheckout() {
        const notes = document.getElementById('checkoutNotes').value;
        const reqType = document.getElementById('request_type').value;

        Swal.fire({
            title: 'Kirim Permintaan?',
            text: "Daftar permintaan akan dikirimkan ke petugas gudang farmasi pusat untuk diproses.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3fbbc0',
            confirmButtonText: 'Ya, Kirim Sekarang'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                axios.post('/api/requests', { notes: notes, request_type: reqType })
                    .then(res => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Terkirim',
                            text: 'Permintaan stok sedang dalam proses verifikasi petugas.',
                            confirmButtonColor: '#3fbbc0'
                        }).then(() => window.location.href = '/customer/history');
                    })
                    .catch(err => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan pada sistem logistik.', confirmButtonColor: '#3fbbc0' }));
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchCart);
</script>

<style>
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>
@endsection
