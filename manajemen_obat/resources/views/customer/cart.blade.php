@extends('layouts.portal')

@section('content')
<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark m-0">Keranjang Permintaan</h3>
        <button id="btnClearCart" onclick="clearCart()" class="btn btn-sm btn-outline-danger px-3 rounded-pill" style="display: none;">
            <i class="bi bi-trash3"></i> Kosongkan Keranjang
        </button>
    </div>

    <div class="row g-4">
        <!-- SISI KIRI: DAFTAR ITEM -->
        <div class="col-lg-8">
            <div id="cartItemsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted small mt-2">Sinkronisasi keranjang...</p>
                </div>
            </div>
        </div>

        <!-- SISI KANAN: RINGKASAN & CHECKOUT -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                <h5 class="fw-bold mb-3 text-dark">Ringkasan Pesanan</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Macam Obat</span>
                    <span class="fw-bold" id="totalKinds">0</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-muted small">Total Kuantitas</span>
                    <span class="fw-bold text-primary fs-5" id="totalQty">0</span>
                </div>
                <hr class="opacity-50">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Catatan Tambahan (Opsional)</label>
                    <textarea id="checkoutNotes" class="form-control border-0 bg-light small" rows="2" placeholder="Contoh: Kirim sebelum jam 2 siang..."></textarea>
                </div>
                <button id="btnCheckout" onclick="processCheckout()" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm" disabled>
                    Kirim Permintaan Sekarang
                </button>
                <a href="/dashboard" class="btn btn-link w-100 mt-2 text-decoration-none text-muted small text-center">
                    <i class="bi bi-arrow-left"></i> Kembali ke Katalog
                </a>
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
                            <i class="bi bi-cart-x text-muted opacity-25" style="font-size: 5rem;"></i>
                            <h5 class="fw-bold mt-3">Keranjang Database Kosong</h5>
                            <p class="text-muted small">Anda belum menambahkan obat apa pun ke keranjang.</p>
                            <a href="/dashboard" class="btn btn-primary rounded-pill px-4 mt-2">Lihat Katalog Obat</a>
                        </div>`;
                } else {
                    btnClear.style.display = 'block';
                    btnCheckout.disabled = false;

                    carts.forEach(item => {
                        const drug = item.drug || {};
                        const img = drug.image || 'https://placehold.co/400x300?text=No+Image';
                        totalQuantity += item.quantity;

                        html += `
                        <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-3 col-md-2">
                                        <img src="${img}" class="rounded-3 shadow-sm" style="width: 100%; height: 70px; object-fit: cover;">
                                    </div>
                                    <div class="col-9 col-md-5">
                                        <h6 class="fw-bold text-dark mb-0">${drug.name || 'Obat Tidak Diketahui'}</h6>
                                        <small class="text-muted">SKU: ${drug.sku || '-'} | Unit: ${drug.unit || '-'}</small>
                                    </div>
                                    <div class="col-7 col-md-3 mt-3 mt-md-0">
                                        <div class="input-group input-group-sm border rounded-pill overflow-hidden bg-light">
                                            <button class="btn btn-light border-0 px-3" onclick="changeQty(${item.id}, ${parseInt(item.quantity) - 1})">-</button>

                                            <!-- MODIFIKASI: Input bisa diketik langsung -->
                                            <input type="number"
                                                   class="form-control text-center border-0 bg-transparent fw-bold"
                                                   value="${item.quantity}"
                                                   min="1"
                                                   onblur="changeQty(${item.id}, this.value)"
                                                   onkeyup="if(event.keyCode === 13) changeQty(${item.id}, this.value)">

                                            <button class="btn btn-light border-0 px-3" onclick="changeQty(${item.id}, ${parseInt(item.quantity) + 1})">+</button>
                                        </div>
                                    </div>
                                    <div class="col-5 col-md-2 mt-3 mt-md-0 text-end">
                                        <button class="btn btn-sm btn-link text-danger text-decoration-none" onclick="deleteItem(${item.id})">
                                            <i class="bi bi-trash"></i> Hapus
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
            })
            .catch(err => {
                container.innerHTML = '<div class="alert alert-danger">Gagal memuat data keranjang. Silakan login ulang.</div>';
            });
    }

    function changeQty(cartId, newQty, maxStock) {
        const qty = parseInt(newQty);
        maxStock = parseInt(maxStock);

        // 1. Jika user mengetik angka lebih besar dari stok
        if (qty > maxStock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Terbatas',
                text: `Maaf, stok maksimal obat ini hanya ${maxStock} unit.`,
                confirmButtonColor: '#0d6efd'
            });
            fetchCart(); // Refresh untuk mengembalikan angka ke jumlah maksimal yang valid
            return;
        }

        // 2. Validasi standar lainnya
        if (isNaN(qty) || qty < 1) {
            fetchCart();
            return;
        }

        axios.put(`/api/cart/${cartId}`, { quantity: qty })
            .then(() => {
                fetchCart();
                updateCartBadge();
            })
            .catch(err => {
                // Jika ada error dari backend (misal stok tiba-tiba berubah)
                const msg = err.response ? err.response.data.message : 'Terjadi kesalahan';
                Swal.fire('Gagal', msg, 'error');
                fetchCart();
            });
    }

    function deleteItem(cartId) {
        axios.delete(`/api/cart/${cartId}`).then(() => {
            fetchCart();
            updateCartBadge();
        });
    }

    function clearCart() {
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            text: "Semua item akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Kosongkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete('/api/cart-clear').then(() => {
                    Swal.fire({ icon: 'success', title: 'Keranjang dikosongkan', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                    fetchCart();
                    updateCartBadge();
                });
            }
        });
    }

    function processCheckout() {
        const notes = document.getElementById('checkoutNotes').value;
        Swal.fire({
            title: 'Kirim Permintaan Stok?',
            text: "Data akan diteruskan ke tim gudang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim Sekarang',
            confirmButtonColor: '#0d6efd',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                axios.post('/api/requests', { notes: notes })
                    .then(res => {
                        Swal.fire('Berhasil!', 'Permintaan stok sedang diproses.', 'success')
                            .then(() => window.location.href = '/customer/history');
                    })
                    .catch(err => {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat memproses checkout.', 'error');
                    });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchCart);
</script>

<style>
    /* Menghilangkan spinner default pada input number agar lebih bersih */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    .border-dashed { border-style: dashed !important; }
    #cartItemsContainer .card:hover { transform: translateY(-2px); transition: 0.2s; }
</style>
@endsection