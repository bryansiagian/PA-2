@extends('layouts.portal')

@section('content')
<style>
    /* Custom Styling untuk menyelaraskan dengan MediNest */
    .page-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 40px 0;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 40px;
    }
    .section-heading {
        color: #2c4964; /* Warna biru MediNest */
        font-weight: 700;
        position: relative;
        padding-bottom: 15px;
    }
    .section-heading::after {
        content: "";
        position: absolute;
        display: block;
        width: 50px;
        height: 3px;
        background: #3fbbc0; /* Warna aksen MediNest */
        bottom: 0;
        left: 0;
    }
    .card-history {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        background: #fff;
        border-left: 5px solid #3fbbc0; /* Aksen kiri */
    }
    .card-history:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    .req-number {
        color: #3fbbc0;
        font-family: 'Ubuntu', sans-serif;
    }
    .badge-status {
        font-size: 0.75rem;
        padding: 6px 15px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .btn-medinest {
        background: #3fbbc0;
        color: white;
        border-radius: 25px;
        padding: 8px 20px;
        transition: 0.3s;
    }
    .btn-medinest:hover {
        background: #329ea2;
        color: white;
    }
    .empty-state-icon {
        color: #3fbbc0;
        opacity: 0.2;
    }
    .modal-content {
        border-radius: 20px;
        border: none;
    }
    .modal-header {
        background: #f8f9fa;
        border-radius: 20px 20px 0 0;
        border-bottom: 1px solid #eee;
    }
</style>

<!-- Header Section -->
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="section-heading">Riwayat Permintaan Obat</h2>
                <p class="text-muted">Pantau status distribusi logistik unit kesehatan Anda secara real-time.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <nav aria-label="breadcrumb" class="d-inline-block">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Portal</a></li>
                        <li class="breadcrumb-item active">Riwayat</li>
                    </ol>
                </nav>
                <button onclick="fetchHistory()" class="btn btn-outline-secondary rounded-pill px-4 ms-2 shadow-sm bg-white">
                    <i class="bi bi-arrow-clockwise me-1"></i> Sinkronisasi
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Loading State -->
    <div id="loadingHistory" class="text-center py-5">
        <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted fw-bold">Menghubungkan ke Server E-Pharma...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyHistory" class="text-center py-5 d-none bg-white rounded-4 shadow-sm border">
        <div class="mb-4">
            <i class="bi bi-clipboard2-pulse empty-state-icon" style="font-size: 6rem;"></i>
        </div>
        <h4 class="fw-bold">Belum Ada Permintaan</h4>
        <p class="text-muted mx-auto" style="max-width: 400px;">Unit Anda belum melakukan pemesanan stok obat. Silakan buat permintaan baru melalui katalog produk.</p>
        <a href="/#katalog" class="btn btn-medinest mt-3 px-5 shadow">Mulai Pesan</a>
    </div>

    <!-- History List -->
    <div id="historyList" class="row">
        <!-- JS Render -->
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-medical me-2 text-info"></i>Rincian Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detailContent">
                <!-- Data item via JS -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchHistory() {
        const listContainer = document.getElementById('historyList');
        const loading = document.getElementById('loadingHistory');
        const empty = document.getElementById('emptyHistory');

        loading.classList.remove('d-none');
        listContainer.innerHTML = '';
        empty.classList.add('d-none');

        axios.get('/api/requests')
            .then(res => {
                loading.classList.add('d-none');
                const data = res.data;

                if (!data || data.length === 0) {
                    empty.classList.remove('d-none');
                    return;
                }

                let html = '';
                data.forEach(req => {
                    const statusConfig = getStatusBadge(req.status);
                    const date = new Date(req.created_at).toLocaleDateString('id-ID', {
                        day: 'numeric', month: 'short', year: 'numeric'
                    });

                    const canCancel = req.status === 'pending' || req.status === 'approved';
                    const deliveryId = req.delivery ? req.delivery.id : null;

                    html += `
                    <div class="col-12 mb-4">
                        <div class="card card-history shadow-sm">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-2 mb-3 mb-md-0">
                                        <div class="small text-muted text-uppercase mb-1 fw-bold" style="font-size: 11px;">ID Transaksi</div>
                                        <h5 class="req-number fw-bold mb-0">#REQ-${req.id}</h5>
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <div class="small text-muted text-uppercase mb-1 fw-bold" style="font-size: 11px;">Waktu Pengajuan</div>
                                        <div class="text-dark fw-semibold"><i class="bi bi-calendar3 me-2 text-info"></i>${date}</div>
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0 text-center text-md-start">
                                        <div class="small text-muted text-uppercase mb-2 fw-bold" style="font-size: 11px;">Status Logistik</div>
                                        <span class="badge rounded-pill badge-status ${statusConfig.class}">
                                            ${statusConfig.icon} ${req.status.toUpperCase()}
                                        </span>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="btn-group">
                                            <button onclick="viewDetail(${req.id})" class="btn btn-outline-info rounded-pill px-3 me-2">
                                                <i class="bi bi-eye"></i> Detail
                                            </button>

                                            ${(req.status === 'shipping' || req.status === 'completed') && deliveryId ? `
                                                <a href="/customer/tracking/${deliveryId}" class="btn btn-medinest shadow-sm">
                                                    <i class="bi bi-geo-alt"></i> Lacak
                                                </a>
                                            ` : ''}

                                            ${canCancel ? `
                                                <button onclick="cancelRequest(${req.id})" class="btn btn-outline-danger rounded-pill px-3 ms-1">
                                                    <i class="bi bi-trash"></i> Batal
                                                </button>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
                listContainer.innerHTML = html;
            })
            .catch(err => {
                loading.classList.add('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Terputus',
                    text: 'Gagal sinkronisasi data dengan server logistik.',
                    confirmButtonColor: '#3fbbc0'
                });
            });
    }

    function getStatusBadge(status) {
        switch(status) {
            case 'pending':   return { class: 'bg-warning text-dark', icon: '<i class="bi bi-hourglass-split me-1"></i>' };
            case 'approved':  return { class: 'bg-info text-white', icon: '<i class="bi bi-check2-all me-1"></i>' };
            case 'rejected':  return { class: 'bg-danger text-white', icon: '<i class="bi bi-x-octagon me-1"></i>' };
            case 'shipping':  return { class: 'bg-primary text-white', icon: '<i class="bi bi-truck me-1"></i>' };
            case 'completed': return { class: 'bg-success text-white', icon: '<i class="bi bi-check-circle-fill me-1"></i>' };
            case 'cancelled': return { class: 'bg-secondary text-white', icon: '<i class="bi bi-slash-circle me-1"></i>' };
            default:          return { class: 'bg-dark text-white', icon: '' };
        }
    }

    function cancelRequest(id) {
        Swal.fire({
            title: 'Batalkan Pesanan?',
            text: "Permintaan stok akan dibatalkan secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3fbbc0',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Kembali',
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                axios.post(`/api/requests/${id}/cancel`)
                    .then(res => {
                        Swal.fire({ icon: 'success', title: 'Dibatalkan', text: res.data.message, confirmButtonColor: '#3fbbc0' });
                        fetchHistory();
                    })
                    .catch(err => {
                        Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
                    });
            }
        });
    }

    function viewDetail(id) {
        const modalBody = document.getElementById('detailContent');
        modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-info"></div></div>';
        new bootstrap.Modal(document.getElementById('modalDetail')).show();

        axios.get(`/api/requests`)
            .then(res => {
                const request = res.data.find(r => r.id === id);
                if (!request) return;

                let itemsHtml = '<div class="list-group list-group-flush">';
                request.items.forEach(item => {
                    const name = item.drug ? item.drug.name : `<span class="text-danger font-italic">${item.custom_drug_name} (Manual)</span>`;
                    const unit = item.drug ? item.drug.unit : (item.custom_unit || '-');
                    itemsHtml += `
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-0">
                        <div>
                            <div class="fw-bold text-dark" style="font-family: 'Poppins', sans-serif;">${name}</div>
                            <small class="text-muted">Kemasan: ${unit}</small>
                        </div>
                        <span class="badge bg-light text-info rounded-pill border px-3">Qty: ${item.quantity}</span>
                    </div>`;
                });
                itemsHtml += '</div>';

                if(request.notes) {
                    itemsHtml += `<div class="mt-3 p-3 bg-light rounded-3 small border-start border-info border-3"><b>Catatan:</b> ${request.notes}</div>`;
                }
                modalBody.innerHTML = itemsHtml;
            });
    }

    document.addEventListener('DOMContentLoaded', fetchHistory);
</script>
@endsection
