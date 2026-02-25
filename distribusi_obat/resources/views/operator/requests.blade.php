@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Manajemen Permintaan & Distribusi</h4>
            <div class="text-muted">Tinjau pesanan, cek kebutuhan armada, dan siapkan lokasi pengambilan obat.</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <!-- FILTER TIPE -->
            <select id="filterType" class="form-select border-0 shadow-sm rounded-pill px-3 bg-white" onchange="load()" style="min-width: 180px;">
                <option value="all">Semua Tipe</option>
                <option value="delivery">🚚 Pengantaran Kurir</option>
                <option value="self_pickup">🏢 Ambil Sendiri</option>
            </select>
            <button onclick="load()" class="btn btn-light btn-icon shadow-sm rounded-circle" title="Refresh Data">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Statistik Row (Limitless Style) -->
    <div class="row mb-3">
        <div class="col-lg-3">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statTotal">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Total Request</div>
                    </div>
                    <i class="ph-clipboard-text ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card card-body bg-warning text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statPending">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Menunggu (Pending)</div>
                    </div>
                    <i class="ph-hourglass-medium ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-list-bullets me-2 text-primary"></i>Antrean Distribusi Obat</h5>
            <div class="ms-auto">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">Live Request List</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3" style="width: 100px;">ID</th>
                        <th>Pemohon</th>
                        <th>Tipe</th>
                        <th>Kendaraan</th> <!-- KOLOM BARU -->
                        <th>Detail Item</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="opRequestTable">
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-muted me-2"></div>
                            Sinkronisasi data permintaan...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: PICK LIST DENGAN RAK/BARIS -->
<div class="modal fade" id="modalItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-list-checks me-2"></i>Daftar Petik (Pick List)</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modalItemsBody">
                <!-- Content injected via JS -->
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function load() {
        const tableBody = document.getElementById('opRequestTable');
        const filterVal = document.getElementById('filterType').value;

        axios.get('/api/requests').then(res => {
            const requests = res.data;
            let html = '';
            let pending = 0;

            const filtered = filterVal === 'all' ? requests : requests.filter(r => r.request_type === filterVal);

            filtered.forEach(r => {
                if(r.status === 'pending') pending++;
                const isDelivery = r.request_type === 'delivery';
                const itemsJson = JSON.stringify(r.items).replace(/"/g, '&quot;');

                // --- LOGIKA KENDARAAN (REVISI) ---
                let vehicleHtml = '';
                if (!isDelivery) {
                    vehicleHtml = '<span class="text-muted fs-xs italic">N/A (Pickup)</span>';
                } else {
                    const isCar = r.required_vehicle === 'car';
                    const vIcon = isCar ? 'ph-car' : 'ph-moped';
                    const vClass = isCar ? 'bg-orange text-orange' : 'bg-slate text-slate';
                    const vText = isCar ? 'MOBIL / VAN' : 'SEPEDA MOTOR';

                    vehicleHtml = `
                        <span class="badge ${vClass} bg-opacity-10 fw-bold px-2 py-1">
                            <i class="${vIcon} me-1"></i>${vText}
                        </span>`;
                }

                // Logika Status Badge
                let statusBadge = '';
                if(r.status === 'pending') statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning fw-bold px-2 py-1">PENDING</span>';
                else if(r.status === 'approved') statusBadge = '<span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">APPROVED</span>';
                else if(r.status === 'shipping') statusBadge = '<span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">SHIPPING</span>';
                else statusBadge = '<span class="badge bg-light text-muted fw-bold px-2 py-1">COMPLETED</span>';

                // Logika Aksi
                let actions = '';
                if (r.status === 'pending') {
                    actions = `
                        <div class="d-inline-flex">
                            <button onclick="approveRequest(${r.id})" class="btn btn-sm btn-success border-0 me-1" title="Approve">
                                <i class="ph-check"></i>
                            </button>
                            <button onclick="rejectRequest(${r.id})" class="btn btn-sm btn-light text-danger border-0" title="Tolak">
                                <i class="ph-x"></i>
                            </button>
                        </div>`;
                } else if (r.status === 'approved') {
                    actions = isDelivery ?
                        `<button onclick="makeReady(${r.id})" class="btn btn-sm btn-indigo px-3 shadow-sm rounded-pill fw-bold"><i class="ph-package me-1"></i> Siap Diambil</button>` :
                        `<button onclick="completePickup(${r.id})" class="btn btn-sm btn-teal text-white px-3 shadow-sm rounded-pill fw-bold"><i class="ph-check-circle me-1"></i> Selesai Ambil</button>`;
                } else {
                    actions = `<span class="fs-xs text-muted">No Action</span>`;
                }

                html += `
                <tr>
                    <td class="ps-3"><span class="font-monospace fw-bold text-indigo">#REQ-${r.id}</span></td>
                    <td>
                        <div class="fw-bold text-dark">${r.user?.name}</div>
                        <div class="fs-xs text-muted"><i class="ph-calendar me-1"></i>${new Date(r.created_at).toLocaleDateString('id-ID')}</div>
                    </td>
                    <td>
                        <span class="badge ${isDelivery ? 'bg-primary bg-opacity-10 text-primary' : 'bg-teal bg-opacity-10 text-teal'} px-2 py-1 border-0">
                            <i class="${isDelivery ? 'ph-truck' : 'ph-storefront'} me-1"></i> ${r.request_type.toUpperCase()}
                        </span>
                    </td>
                    <td>${vehicleHtml}</td> <!-- TAMPILAN KENDARAAN -->
                    <td>
                        <button onclick="showItems('${itemsJson}')" class="btn btn-sm btn-light border-0 text-indigo fw-bold">
                            <i class="ph-magnifying-glass me-1"></i> Lihat Item
                        </button>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center pe-3">${actions}</td>
                </tr>`;
            });
            tableBody.innerHTML = html || '<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada permintaan.</td></tr>';
            document.getElementById('statTotal').innerText = requests.length;
            document.getElementById('statPending').innerText = pending;
        });
    }

    function showItems(encoded) {
        const items = JSON.parse(encoded);
        let html = '<div class="list-group list-group-flush">';
        items.forEach(i => {
            const drug = i.drug || { name: i.custom_drug_name, rack: { name: '?', storage: { name: '?' } } };
            const rackName = drug.rack ? drug.rack.name : 'N/A';
            const storageName = drug.rack && drug.rack.storage ? drug.rack.storage.name : 'N/A';

            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 border-bottom px-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-light p-2 rounded me-3 text-indigo">
                            <i class="ph-pill fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">${drug.name}</div>
                            <div class="fs-xs text-muted">
                                <span class="badge bg-primary bg-opacity-10 text-primary me-1">Gudang: ${storageName}</span>
                                <span class="badge bg-teal bg-opacity-10 text-teal text-uppercase">Rak: ${rackName}</span>
                            </div>
                        </div>
                    </div>
                    <span class="badge bg-light text-indigo border rounded-pill px-3 fs-base">x${i.quantity}</span>
                </div>`;
        });
        document.getElementById('modalItemsBody').innerHTML = html + '</div>';
        new bootstrap.Modal(document.getElementById('modalItems')).show();
    }

    function approveRequest(id) {
        Swal.fire({
            title: 'Setujui Permintaan?',
            text: "Stok akan dipotong dan email struk terkirim.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            confirmButtonColor: '#059669',
        }).then(res => {
            if(res.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                axios.post(`/api/requests/${id}/approve`).then(() => {
                    Swal.fire('Berhasil!', 'Request telah disetujui.', 'success');
                    load();
                }).catch(e => Swal.fire('Gagal', e.response.data.message, 'error'));
            }
        });
    }

    function makeReady(id) {
        axios.post(`/api/deliveries/ready/${id}`).then(() => {
            Swal.fire({ icon: 'info', title: 'Pesanan Siap', text: 'Barang masuk ke bursa kurir.' });
            load();
        });
    }

    function rejectRequest(id) {
        Swal.fire({
            title: 'Tolak Permintaan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Tolak'
        }).then(res => {
            if(res.isConfirmed) {
                axios.post(`/api/requests/${id}/reject`).then(() => {
                    Swal.fire('Ditolak', 'Permintaan telah dibatalkan.', 'error');
                    load();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', load);
</script>

<style>
    .bg-indigo { background-color: #5c68e2 !important; }
    .bg-teal { background-color: #0d9488 !important; }
    .bg-orange { background-color: #f59e0b !important; }
    .bg-slate { background-color: #64748b !important; }
    .text-indigo { color: #5c68e2 !important; }
    .text-orange { color: #d97706 !important; }
    .text-slate { color: #475569 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }

    .table td { padding: 0.85rem 1rem; }
    .ph-2x { font-size: 2.2rem; }
    .fs-xs { font-size: 0.7rem; }
    .italic { font-style: italic; }
    #modalItemsBody { max-height: 450px; overflow-y: auto; }
</style>
@endsection