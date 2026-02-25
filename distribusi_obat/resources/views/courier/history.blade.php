@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Riwayat Pengiriman Selesai</h4>
            <div class="text-muted">Daftar seluruh paket yang telah Anda selesaikan dengan sukses</div>
        </div>
        <div class="ms-3">
            <button onclick="fetchHistory()" class="btn btn-light btn-icon shadow-sm rounded-circle" title="Refresh Riwayat">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- TABEL RIWAYAT (Limitless Style) -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-clock-counter-clockwise me-2 text-primary"></i>Log Aktivitas Pengiriman</h5>
            <div class="ms-auto">
                <span class="badge bg-success bg-opacity-10 text-success fw-bold">All Jobs Completed</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3" style="width: 180px;">Resi & Tanggal</th>
                        <th>Penerima (Lokasi)</th>
                        <th>Barang</th>
                        <th class="text-center">Bukti Foto</th>
                        <th class="text-center pe-3">Status</th>
                    </tr>
                </thead>
                <tbody id="historyTable">
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-muted me-2"></div>
                            Menarik data riwayat pengiriman...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: RINCIAN BARANG (Limitless Style)
     ========================================== -->
<div class="modal fade" id="modalItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-package me-2"></i>Rincian Barang Terkirim</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modalItemsBody">
                <!-- List diisi via JS -->
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchHistory() {
        const tableBody = document.getElementById('historyTable');
        axios.get('/api/deliveries/history')
            .then(res => {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-5 text-muted small">Anda belum memiliki riwayat pengiriman selesai.</td></tr>';
                } else {
                    res.data.forEach(d => {
                        const deliveredAt = new Date(d.delivered_at);
                        const date = deliveredAt.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
                        const time = deliveredAt.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                        const itemsJson = JSON.stringify(d.request.items).replace(/"/g, '&quot;');

                        html += `
                        <tr>
                            <td class="ps-3">
                                <div class="font-monospace fw-bold text-indigo">#${d.tracking_number}</div>
                                <div class="fs-xs text-muted"><i class="ph-calendar-check me-1"></i>${date} - ${time}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${d.request.user.name}</div>
                                <div class="fs-xs text-muted text-truncate" style="max-width: 250px;">
                                    <i class="ph-map-pin me-1"></i>${d.request.user.address || '-'}
                                </div>
                            </td>
                            <td>
                                <button onclick="showItems('${itemsJson}')" class="btn btn-sm btn-light border-0 text-indigo fw-bold rounded-pill px-3">
                                    <i class="ph-package me-1"></i> ${d.request.items.length} Item
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-block position-relative">
                                    <img src="${d.proof_image_url}" class="rounded shadow-sm border" width="45" height="45"
                                         style="object-fit: cover; cursor: pointer; transition: 0.2s;"
                                         onclick="window.open(this.src)"
                                         onmouseover="this.style.transform='scale(1.1)'"
                                         onmouseout="this.style.transform='scale(1)'"
                                         title="Klik untuk perbesar">
                                </div>
                            </td>
                            <td class="text-center pe-3">
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">
                                    <i class="ph-check-circle me-1"></i>DELIVERED
                                </span>
                            </td>
                        </tr>`;
                    });
                }
                tableBody.innerHTML = html;
            });
    }

    function showItems(encoded) {
        const items = JSON.parse(encoded);
        let html = '<div class="list-group list-group-flush">';
        items.forEach(i => {
            const name = i.drug ? i.drug.name : `<span class="text-danger fw-bold">${i.custom_drug_name} (Manual)</span>`;
            const unit = i.drug ? i.drug.unit : (i.custom_unit || 'Unit');
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-light p-2 rounded me-3 text-indigo">
                            <i class="ph-pill fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">${name}</div>
                            <div class="fs-xs text-muted">Satuan: ${unit}</div>
                        </div>
                    </div>
                    <span class="badge bg-light text-indigo border rounded-pill px-3 fs-base">x${i.quantity}</span>
                </div>`;
        });
        html += '</div>';
        document.getElementById('modalItemsBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalItems')).show();
    }

    document.addEventListener('DOMContentLoaded', fetchHistory);
</script>

<style>
    /* Styling Dasar Limitless */
    .bg-indigo { background-color: #5c68e2 !important; }
    .text-indigo { color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }

    .table td { padding: 0.85rem 1rem; }
    .fs-xs { font-size: 0.75rem; }
    .fs-base { font-size: 1rem; }
    .font-monospace { font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

    /* Modal List Scroll */
    #modalItemsBody { max-height: 400px; overflow-y: auto; }

    /* Hover effect row */
    .table-hover tbody tr:hover {
        background-color: rgba(92, 104, 226, 0.02);
    }
</style>
@endsection
