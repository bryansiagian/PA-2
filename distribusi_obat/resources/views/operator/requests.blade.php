@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Manajemen Permintaan & Distribusi</h4>
            <p class="text-muted small mb-0">Tinjau pesanan dan siapkan lokasi pengambilan obat (Pick List).</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <!-- FILTER TIPE (REVISI DOSEN #2) -->
            <select id="filterType" class="form-select border-0 shadow-sm rounded-pill px-4" onchange="load()">
                <option value="all">Semua Tipe</option>
                <option value="delivery">🚚 Pengantaran Kurir</option>
                <option value="self_pickup">🏢 Ambil Sendiri</option>
            </select>
            <button onclick="load()" class="btn btn-white border shadow-sm rounded-pill px-3">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4 p-3 border-start border-primary border-4"><small class="text-muted fw-bold">TOTAL REQUEST</small><h3 class="fw-bold mb-0" id="statTotal">0</h3></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm rounded-4 p-3 border-start border-warning border-4"><small class="text-muted fw-bold">MENUNGGU</small><h3 class="fw-bold mb-0 text-warning" id="statPending">0</h3></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small fw-bold">
                        <th class="ps-4 py-3">ID</th>
                        <th>PEMOHON</th>
                        <th>TIPE</th>
                        <th>DETAIL ITEM</th>
                        <th>STATUS</th>
                        <th class="text-center pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody id="opRequestTable">
                    <tr><td colspan="6" class="text-center py-5">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: PICK LIST DENGAN RAK/BARIS (REVISI DOSEN #1) -->
<div class="modal fade" id="modalItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="bi bi-list-check me-2"></i>Daftar Petik (Pick List)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalItemsBody"></div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
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

                let actions = '';
                if (r.status === 'pending') {
                    actions = `<div class="btn-group shadow-sm rounded-pill overflow-hidden"><button onclick="approveRequest(${r.id})" class="btn btn-success btn-sm px-3">Approve</button><button onclick="rejectRequest(${r.id})" class="btn btn-outline-danger btn-sm px-3">Tolak</button></div>`;
                } else if (r.status === 'approved') {
                    actions = isDelivery ?
                        `<button onclick="makeReady(${r.id})" class="btn btn-primary btn-sm rounded-pill shadow-sm"><i class="bi bi-box-seam"></i> Siap Diambil</button>` :
                        `<button onclick="completePickup(${r.id})" class="btn btn-dark btn-sm rounded-pill shadow-sm"><i class="bi bi-check-circle"></i> Selesai Ambil</button>`;
                } else {
                    actions = `<span class="text-muted small">Arsip</span>`;
                }

                html += `
                <tr class="border-bottom">
                    <td class="ps-4 fw-bold text-primary">#REQ-${r.id}</td>
                    <td><div class="fw-bold">${r.user?.name}</div><small class="text-muted">${new Date(r.created_at).toLocaleDateString('id-ID')}</small></td>
                    <td><span class="badge ${isDelivery ? 'bg-light text-primary' : 'bg-light text-dark'} border small"><i class="bi ${isDelivery ? 'bi-truck' : 'bi-person-walking'}"></i> ${r.request_type.toUpperCase()}</span></td>
                    <td><button onclick="showItems('${itemsJson}')" class="btn btn-sm btn-light border text-primary rounded-pill px-3">Lihat Item</button></td>
                    <td><span class="badge bg-warning text-dark rounded-pill px-3">${r.status.toUpperCase()}</span></td>
                    <td class="text-center pe-4">${actions}</td>
                </tr>`;
            });
            tableBody.innerHTML = html || '<tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada data.</td></tr>';
            document.getElementById('statTotal').innerText = requests.length;
            document.getElementById('statPending').innerText = pending;
        });
    }

    function showItems(encoded) {
        const items = JSON.parse(encoded);
        let html = '<div class="list-group list-group-flush">';
        items.forEach(i => {
            const drug = i.drug || { name: i.custom_drug_name, rack_number: '?', row_number: '?' };
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                    <div>
                        <div class="fw-bold text-dark">${drug.name}</div>
                        <small class="text-primary fw-bold"><i class="bi bi-geo-fill"></i> ${drug.rack_number || 'N/A'} | ${drug.row_number || 'N/A'}</small>
                    </div>
                    <span class="badge bg-light text-primary border rounded-pill">x${i.quantity}</span>
                </div>`;
        });
        document.getElementById('modalItemsBody').innerHTML = html + '</div>';
        new bootstrap.Modal(document.getElementById('modalItems')).show();
    }

    function approveRequest(id) {
        Swal.fire({ title: 'Setujui?', text: "Stok akan terpotong & email struk terkirim.", icon: 'question', showCancelButton: true, confirmButtonColor: '#198754', showLoaderOnConfirm: true, preConfirm: () => {
            return axios.post(`/api/requests/${id}/approve`).catch(e => Swal.showValidationMessage(e.response.data.message))
        }}).then(res => { if(res.isConfirmed) { Swal.fire('Berhasil!', 'Request disetujui.', 'success'); load(); } });
    }

    function makeReady(id) {
        axios.post(`/api/deliveries/ready/${id}`).then(() => { Swal.fire('Siap!', 'Kurir bisa mengambil paket.', 'info'); load(); });
    }

    function rejectRequest(id) {
        axios.post(`/api/requests/${id}/reject`).then(() => { Swal.fire('Ditolak', 'Permintaan dibatalkan.', 'error'); load(); });
    }

    function completePickup(id) {
        Swal.fire({ title: 'Selesaikan Ambil Sendiri?', text: "Pastikan customer sudah menerima barang.", icon: 'warning', showCancelButton: true }).then(res => {
            if(res.isConfirmed) axios.post(`/api/requests/${id}/approve`).then(() => { load(); }); // Re-use approve logic or create separate endpt
        });
    }

    document.addEventListener('DOMContentLoaded', load);
</script>
@endsection