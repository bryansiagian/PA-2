@extends('layouts.backoffice')

@section('page_title', 'Antrian Pesanan Produk')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Antrian Pesanan Logistik</h4>
            <div class="text-muted small">Validasi pengajuan obat dari unit kesehatan dan faskes mitra.</div>
        </div>
        <div class="ms-3">
            <button onclick="fetchOrders()" class="btn btn-light shadow-sm rounded-pill px-4">
                <i class="ph-arrow-clockwise me-2"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">ID & Waktu</th>
                        <th>Fasilitas Kesehatan (Unit)</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Menyinkronkan database...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: DETAIL PESANAN & PICKING LIST -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0">
                <h6 class="modal-title fw-bold"><i class="ph-list-checks me-2"></i>Rincian Item & Lokasi Gudang</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="detailContent" class="p-3">
                    <!-- JS Render -->
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-link text-body fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchOrders() {
        const tableBody = document.getElementById('orderTableBody');

        axios.get('/api/orders').then(res => {
            const orders = res.data;
            let html = '';

            if (orders.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">Belum ada antrian pesanan.</td></tr>';
                return;
            }

            orders.forEach(o => {
                const date = new Date(o.created_at).toLocaleString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'});

                // Ambil Nama Status dari Lookup Table
                const statusName = o.status ? o.status.name : 'Unknown';
                const statusConfig = getStatusBadge(statusName.toLowerCase());

                const isPickup = o.product_order_delivery_id == 2; // Sesuai Seeder: 1=Delivery, 2=Self Pickup
                const deliveryId = o.delivery ? o.delivery.id : null;

                html += `
                <tr>
                    <td class="ps-3">
                        <div class="fw-bold text-indigo">#${o.id.substring(0,8)}</div>
                        <div class="fs-xs text-muted"><i class="ph-clock me-1"></i>${date}</div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${o.user?.name || 'User'}</div>
                        <div class="fs-xs text-muted text-uppercase">
                            ${isPickup ? '<span class="text-orange fw-bold"><i class="ph-storefront me-1"></i>Ambil Sendiri</span>' : '<span class="text-info"><i class="ph-truck me-1"></i>Kirim via Kurir</span>'}
                        </div>
                    </td>
                    <td class="text-center fw-bold">${o.items.length} Jenis</td>
                    <td class="text-center">
                        <span class="badge ${statusConfig.class} rounded-pill px-2 py-1">${statusName.toUpperCase()}</span>
                    </td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex gap-2">
                            <!-- Tombol Detail -->
                            <button onclick="viewDetail('${o.id}')" class="btn btn-sm btn-light text-indigo border-0 shadow-sm" title="Lihat Rincian">
                                <i class="ph-eye"></i>
                            </button>

                            <!-- Tombol Lacak (Hanya muncul jika sudah ada data delivery) -->
                            ${deliveryId ? `
                                <a href="/operator/tracking/${deliveryId}" class="btn btn-sm btn-light text-success border-0 shadow-sm" title="Lacak Pengiriman">
                                    <i class="ph-map-pin-line"></i>
                                </a>
                            ` : ''}

                            <!-- Tombol Aksi berdasarkan Status -->
                            ${statusName === 'Pending' ? `
                                <button onclick="approveOrder('${o.id}')" class="btn btn-sm btn-indigo rounded-pill px-3">Setujui</button>
                                <button onclick="rejectOrder('${o.id}')" class="btn btn-sm btn-light text-danger border-0" title="Tolak"><i class="ph-x"></i></button>
                            ` : ''}

                            ${statusName === 'Processed' && !isPickup ? `
                                <button onclick="readyForShipping('${o.id}')" class="btn btn-sm btn-teal text-white rounded-pill px-3">Siap Kirim</button>
                            ` : ''}

                            ${statusName === 'Processed' && isPickup ? `
                                <button onclick="completePickup('${o.id}')" class="btn btn-sm btn-success rounded-pill px-3">Selesai Ambil</button>
                            ` : ''}
                        </div>
                    </td>
                </tr>`;
            });
            tableBody.innerHTML = html;
        });
    }

    function getStatusBadge(status) {
        switch(status) {
            case 'pending':   return { class: 'bg-warning text-dark' };
            case 'processed': return { class: 'bg-info text-white' };
            case 'shipping':  return { class: 'bg-primary text-white' };
            case 'completed': return { class: 'bg-success text-white' };
            case 'rejected':  return { class: 'bg-danger text-white' };
            case 'cancelled': return { class: 'bg-secondary text-white' };
            default:          return { class: 'bg-dark text-white' };
        }
    }

    function viewDetail(id) {
        const modalBody = document.getElementById('detailContent');
        modalBody.innerHTML = '<div class="text-center p-4"><i class="ph-spinner spinner fs-2 text-indigo"></i></div>';
        new bootstrap.Modal(document.getElementById('modalDetail')).show();

        axios.get('/api/orders').then(res => {
            const order = res.data.find(o => o.id === id);
            if (!order) return;

            let itemsHtml = '<div class="list-group list-group-flush">';
            order.items.forEach(item => {
                const prod = item.product || {};
                itemsHtml += `
                <div class="list-group-item d-flex justify-content-between align-items-start py-3">
                    <div class="me-auto">
                        <div class="fw-bold text-dark">${prod.name || 'Produk'}</div>
                        <div class="fs-xs text-muted">Gudang: ${prod.warehouse?.name || 'Pusat'} | SKU: ${prod.sku}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-indigo rounded-pill">QTY: ${item.quantity}</span>
                        <div class="fs-xs text-muted mt-1">Rp${Number(item.price_at_order).toLocaleString()}</div>
                    </div>
                </div>`;
            });

            itemsHtml += `
                <div class="p-3 bg-light border-top">
                    <div class="d-flex justify-content-between fw-bold text-indigo fs-base">
                        <span>TOTAL ORDER</span>
                        <span>Rp${Number(order.total).toLocaleString()}</span>
                    </div>
                </div>
            `;
            itemsHtml += '</div>';
            modalBody.innerHTML = itemsHtml;
        });
    }

    function approveOrder(id) {
        Swal.fire({ title: 'Setujui Pesanan?', text: "Stok akan otomatis terpotong dari gudang.", icon: 'question', showCancelButton: true, confirmButtonColor: '#5c6bc0' })
        .then(result => {
            if (result.isConfirmed) {
                axios.post(`/api/orders/${id}/approve`).then(() => {
                    Swal.fire('Berhasil', 'Pesanan disetujui.', 'success');
                    fetchOrders();
                }).catch(err => Swal.fire('Gagal', err.response.data.message, 'error'));
            }
        });
    }

    function readyForShipping(id) {
        axios.post(`/api/deliveries/ready/${id}`).then(() => {
            Swal.fire('Siap Kirim!', 'Nomor resi telah diterbitkan.', 'success');
            fetchOrders();
        });
    }

    function completePickup(id) {
        axios.post(`/api/orders/${id}/complete-pickup`).then(() => {
            Swal.fire('Selesai', 'Pengambilan barang dikonfirmasi.', 'success');
            fetchOrders();
        });
    }

    function rejectOrder(id) {
        Swal.fire({ title: 'Tolak Pesanan?', text: 'Alasan penolakan akan dicatat.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444' })
        .then(result => { if (result.isConfirmed) axios.post(`/api/orders/${id}/reject`).then(() => fetchOrders()); });
    }

    document.addEventListener('DOMContentLoaded', fetchOrders);
</script>

<style>
    .btn-indigo { background-color: #5c6bc0; color: #fff; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }
    .text-orange { color: #f59e0b; }
    .text-teal { color: #26a69a; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection 