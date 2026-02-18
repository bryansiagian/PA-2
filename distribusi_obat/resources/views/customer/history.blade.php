@extends('layouts.portal')

@section('content')
<div class="container mt-5 mb-5">
    <!-- Header Section -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark">Riwayat Permintaan Obat</h3>
            <p class="text-muted small">Kelola dan pantau status permintaan stok unit kesehatan Anda.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button onclick="fetchHistory()" class="btn btn-white border shadow-sm rounded-pill px-4">
                <i class="bi bi-arrow-clockwise me-1"></i> Perbarui Daftar
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingHistory" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted small fw-bold">MENYINKRONKAN DATA...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyHistory" class="text-center py-5 d-none bg-white rounded-4 shadow-sm border border-dashed">
        <div class="mb-3">
            <i class="bi bi-clipboard-x text-muted opacity-25" style="font-size: 5rem;"></i>
        </div>
        <h5 class="fw-bold">Tidak Ada Riwayat</h5>
        <p class="text-muted">Anda belum pernah melakukan permintaan obat.</p>
        <a href="/dashboard" class="btn btn-primary mt-2 rounded-pill px-4 shadow">Pesan Sekarang</a>
    </div>

    <!-- History List -->
    <div id="historyList" class="row">
        <!-- Data di-render di sini oleh JavaScript -->
    </div>
</div>

<!-- ==========================================
     MODAL: DETAIL PESANAN
     ========================================== -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark">Detail Permintaan Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detailContent">
                <!-- Rincian obat diisi via JS -->
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Konfigurasi Token Global
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
                        day: 'numeric', month: 'long', year: 'numeric'
                    });

                    // Logika tombol batalkan: Hanya jika status PENDING atau APPROVED
                    const canCancel = req.status === 'pending' || req.status === 'approved';
                    const deliveryId = req.delivery ? req.delivery.id : null;

                    html += `
                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden card-history">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-2 mb-3 mb-md-0">
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">No. Request</small>
                                        <span class="fw-bold text-primary">#REQ-${req.id}</span>
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0 border-start-md">
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Tanggal Pengajuan</small>
                                        <span class="fw-bold text-dark">${date}</span>
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 10px;">Status Saat Ini</small>
                                        <span class="badge ${statusConfig.class} rounded-pill px-3 py-2 shadow-sm">
                                            ${statusConfig.icon} ${req.status.toUpperCase()}
                                        </span>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <button onclick="viewDetail(${req.id})" class="btn btn-light rounded-pill px-3 me-1">Detail</button>

                                        <!-- Tombol Lacak -->
                                        ${(req.status === 'shipping' || req.status === 'completed') && deliveryId ? `
                                            <a href="/customer/tracking/${deliveryId}" class="btn btn-primary rounded-pill px-3">
                                                <i class="bi bi-geo-alt-fill"></i> Lacak
                                            </a>
                                        ` : ''}

                                        <!-- Tombol Batalkan -->
                                        ${canCancel ? `
                                            <button onclick="cancelRequest(${req.id})" class="btn btn-outline-danger rounded-pill px-3 ms-1">
                                                <i class="bi bi-x-circle"></i> Batal
                                            </button>
                                        ` : ''}
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
                Swal.fire('Error', 'Gagal mengambil data riwayat.', 'error');
            });
    }

    function getStatusBadge(status) {
        switch(status) {
            case 'pending':   return { class: 'bg-warning text-dark', icon: '<i class="bi bi-clock"></i>' };
            case 'approved':  return { class: 'bg-info text-white', icon: '<i class="bi bi-check-circle"></i>' };
            case 'rejected':  return { class: 'bg-danger text-white', icon: '<i class="bi bi-x-circle"></i>' };
            case 'shipping':  return { class: 'bg-primary text-white', icon: '<i class="bi bi-truck"></i>' };
            case 'completed': return { class: 'bg-success text-white', icon: '<i class="bi bi-house-check"></i>' };
            // TAMBAHKAN INI
            case 'cancelled': return { class: 'bg-secondary text-white', icon: '<i class="bi bi-x-square"></i>' };
            default:          return { class: 'bg-dark text-white', icon: '' };
        }
    }

    // FUNGSI BATALKAN PERMINTAAN
    function cancelRequest(id) {
        Swal.fire({
            title: 'Batalkan Permintaan?',
            text: "Jika permintaan sudah disetujui, stok akan dikembalikan ke gudang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Kembali',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan Loading
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

                axios.post(`/api/requests/${id}/cancel`)
                    .then(res => {
                        Swal.fire('Berhasil!', res.data.message, 'success');
                        fetchHistory(); // Refresh data tanpa reload
                    })
                    .catch(err => {
                        const msg = err.response ? err.response.data.message : 'Gagal menghubungi server';
                        Swal.fire('Gagal', msg, 'error');
                    });
            }
        });
    }

    function viewDetail(id) {
        const modalBody = document.getElementById('detailContent');
        modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';
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
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                        <div>
                            <div class="fw-bold text-dark">${name}</div>
                            <small class="text-muted">Unit: ${unit}</small>
                        </div>
                        <span class="badge bg-light text-primary rounded-pill border">x${item.quantity}</span>
                    </div>`;
                });
                itemsHtml += '</div>';

                if(request.notes) {
                    itemsHtml += `<div class="mt-3 p-3 bg-light rounded-4 small"><b>Catatan:</b> ${request.notes}</div>`;
                }
                modalBody.innerHTML = itemsHtml;
            });
    }

    document.addEventListener('DOMContentLoaded', fetchHistory);
</script>

<style>
    .card-history { transition: all 0.2s; border: 1px solid rgba(0,0,0,0.03) !important; }
    .card-history:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.06) !important; }
    @media (min-width: 768px) {
        .border-start-md { border-left: 1px solid #eee !important; padding-left: 1.5rem !important; }
    }
    .badge { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; }
</style>
@endsection