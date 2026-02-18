@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Riwayat Selesai</h4>
            <p class="text-muted small mb-0">Daftar seluruh pengiriman yang telah Anda selesaikan dengan sukses.</p>
        </div>
    </div>

    <!-- TABEL RIWAYAT -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-light">
                        <tr class="text-muted small fw-bold text-uppercase">
                            <th class="ps-4 py-3">RESI & TANGGAL</th>
                            <th>PENERIMA (UNIT KESEHATAN)</th>
                            <th>BARANG</th>
                            <th class="text-center">FOTO BUKTI</th>
                            <th class="text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="historyTable">
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Memuat riwayat pengiriman...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rincian Barang (Re-use modal dari bursa) -->
<div class="modal fade" id="modalItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark">Rincian Barang Terkirim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalItemsBody"></div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchHistory() {
        axios.get('/api/deliveries/history')
            .then(res => {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-5 text-muted small italic">Anda belum memiliki riwayat pengiriman selesai.</td></tr>';
                } else {
                    res.data.forEach(d => {
                        const date = new Date(d.delivered_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
                        const time = new Date(d.delivered_at).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                        const itemsJson = JSON.stringify(d.request.items).replace(/"/g, '&quot;');

                        html += `
                        <tr class="border-bottom">
                            <td class="ps-4 py-3">
                                <span class="fw-bold text-primary d-block">#${d.tracking_number}</span>
                                <small class="text-muted">${date} - ${time}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${d.request.user.name}</div>
                                <small class="text-muted"><i class="bi bi-geo-alt"></i> ${d.request.user.address || '-'}</small>
                            </td>
                            <td>
                                <button onclick="showItems('${itemsJson}')" class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm">
                                    <i class="bi bi-box me-1"></i> ${d.request.items.length} Item
                                </button>
                            </td>
                            <td class="text-center">
                                <img src="${d.proof_image_url}" class="rounded-3 border shadow-sm" width="50" height="50"
                                     style="object-fit: cover; cursor: pointer;"
                                     onclick="window.open(this.src)" title="Klik untuk perbesar">
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success px-3 rounded-pill border border-success border-opacity-25">
                                    <i class="bi bi-check-circle-fill me-1"></i> DELIVERED
                                </span>
                            </td>
                        </tr>`;
                    });
                }
                document.getElementById('historyTable').innerHTML = html;
            });
    }

    // Fungsi showItems sama persis dengan yang ada di bursa
    function showItems(encoded) {
        const items = JSON.parse(encoded);
        let html = '<div class="list-group list-group-flush">';
        items.forEach(i => {
            const name = i.drug ? i.drug.name : `<span class="text-danger fw-bold">${i.custom_drug_name} (Manual)</span>`;
            const unit = i.drug ? i.drug.unit : (i.custom_unit || 'Unit');
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                    <div>
                        <div class="fw-bold text-dark small">${name}</div>
                        <small class="text-muted">Unit: ${unit}</small>
                    </div>
                    <span class="badge bg-light text-primary border rounded-pill">x${i.quantity}</span>
                </div>`;
        });
        html += '</div>';
        document.getElementById('modalItemsBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalItems')).show();
    }

    document.addEventListener('DOMContentLoaded', fetchHistory);
</script>

<style>
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .table thead th { font-size: 11px; letter-spacing: 0.5px; }
    .italic { font-style: italic; }
</style>
@endsection