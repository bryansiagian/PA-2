@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Manajemen Permintaan & Distribusi</h4>
            <p class="text-muted small mb-0">Setujui permintaan stok dan siapkan paket untuk diambil oleh kurir.</p>
        </div>
        <button onclick="load()" class="btn btn-white border shadow-sm rounded-pill px-3">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
        </button>
    </div>

    <!-- Statistik Ringkas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-primary border-4">
                <small class="text-muted fw-bold d-block mb-1">TOTAL REQUEST</small>
                <h3 class="fw-bold mb-0" id="statTotal">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-warning border-4">
                <small class="text-muted fw-bold d-block mb-1">MENUNGGU (PENDING)</small>
                <h3 class="fw-bold mb-0 text-warning" id="statPending">0</h3>
            </div>
        </div>
    </div>

    <!-- Tabel Utama -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small fw-bold">
                        <th class="ps-4 py-3">ID REQUEST</th>
                        <th>PEMOHON (UNIT)</th>
                        <th>DETAIL ITEM</th>
                        <th>STATUS SISTEM</th>
                        <th class="text-center pe-4">AKSI OPERATOR</th>
                    </tr>
                </thead>
                <tbody id="opRequestTable">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Mengambil data dari server...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: RINCIAN ITEM -->
<div class="modal fade" id="modalItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Daftar Obat Diminta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalItemsBody">
                <!-- List diisi via JS -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Konfigurasi Header Axios Global (Token dari Session)
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function load() {
        const tableBody = document.getElementById('opRequestTable');

        axios.get('/api/requests')
            .then(res => {
                const requests = res.data;
                let html = '';
                let pending = 0;

                if (!requests || requests.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-5">Tidak ada permintaan yang masuk.</td></tr>';
                    return;
                }

                requests.forEach(r => {
                    if(r.status === 'pending') pending++;

                    // Logika Badge Status
                    let badgeClass = 'bg-warning text-dark';
                    if(r.status === 'approved') badgeClass = 'bg-success';
                    if(r.status === 'rejected') badgeClass = 'bg-danger';
                    if(r.status === 'cancelled') badgeClass = 'bg-secondary'; // Warna abu-abu untuk dibatalkan
                    if(r.status === 'shipping') badgeClass = 'bg-primary';

                    // Tombol Aksi Berdasarkan Alur Bisnis Baru
                    let actions = '';
                    if (r.status === 'pending') {
                        // Tahap 1: Approve atau Reject
                        actions = `
                            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                <button onclick="approveRequest(${r.id})" class="btn btn-success btn-sm px-3">Approve</button>
                                <button onclick="rejectRequest(${r.id})" class="btn btn-outline-danger btn-sm px-3">Tolak</button>
                            </div>`;
                    } else if (r.status === 'approved') {
                        // Tahap 2: Siapkan untuk diambil kurir (Self-Claim)
                        actions = `
                            <button onclick="makeReadyForPickup(${r.id})" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                <i class="bi bi-box-seam me-1"></i> Siap Diambil
                            </button>`;
                    } else if (r.status === 'shipping') {
                        // Tahap 3: Sedang diproses oleh kurir
                        actions = `
                            <a href="/operator/tracking/${r.delivery.id}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                                <i class="bi bi-geo-alt"></i> Lacak Kurir
                            </a>`;
                    } else {
                        // Selesai / Rejected
                        actions = `<span class="text-muted small italic">Arsip Selesai</span>`;
                    }

                    const itemsJson = JSON.stringify(r.items).replace(/"/g, '&quot;');
                    const requester = r.user ? r.user.name : 'User Terhapus';

                    html += `
                    <tr class="border-bottom">
                        <td class="ps-4 fw-bold text-primary">#REQ-${r.id}</td>
                        <td>
                            <div class="fw-bold text-dark">${requester}</div>
                            <small class="text-muted">${new Date(r.created_at).toLocaleDateString('id-ID')}</small>
                        </td>
                        <td>
                            <button onclick="showItems('${itemsJson}')" class="btn btn-sm btn-light border text-primary rounded-pill px-3">
                                <i class="bi bi-eye me-1"></i> ${r.items.length} Macam Obat
                            </button>
                        </td>
                        <td><span class="badge ${badgeClass} rounded-pill px-3">${r.status.toUpperCase()}</span></td>
                        <td class="text-center pe-4">${actions}</td>
                    </tr>`;
                });

                tableBody.innerHTML = html;
                document.getElementById('statTotal').innerText = requests.length;
                document.getElementById('statPending').innerText = pending;
            })
            .catch(err => {
                console.error(err);
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger small">Gagal mengambil data dari API.</td></tr>';
            });
    }

    // Fungsi Melihat Rincian Item (Katalog vs Manual)
    function showItems(encoded) {
        const items = JSON.parse(encoded);
        let html = '<div class="list-group list-group-flush">';
        items.forEach(i => {
            const name = i.drug ? i.drug.name : `<span class="text-danger fw-bold">${i.custom_drug_name} (Manual)</span>`;
            const unit = i.drug ? i.drug.unit : (i.custom_unit || 'Unit');
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                    <div>
                        <div class="fw-bold text-dark">${name}</div>
                        <small class="text-muted">Kemasan: ${unit}</small>
                    </div>
                    <span class="badge bg-light text-primary border rounded-pill">x${i.quantity}</span>
                </div>`;
        });
        html += '</div>';
        document.getElementById('modalItemsBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalItems')).show();
    }

    // Fungsi APPROVE
    function approveRequest(id) {
        Swal.fire({
            title: 'Setujui Permintaan?',
            text: "Stok akan dipotong dan email struk akan dikirim ke customer.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Ya, Setujui!'
        }).then((result) => {
            if (result.isConfirmed) {
                // --- PERBAIKAN DI SINI ---
                // Tampilkan loading dan kunci layar agar tidak bisa klik 2x
                Swal.fire({
                    title: 'Sedang Memproses...',
                    text: 'Mengirim email struk ke customer, mohon tunggu.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                axios.post(`/api/requests/${id}/approve`)
                    .then(res => {
                        Swal.fire('Berhasil!', res.data.message, 'success');
                        load(); // Refresh tabel
                    })
                    .catch(err => {
                        const msg = err.response ? err.response.data.message : 'Terjadi kesalahan';
                        Swal.fire('Gagal', msg, 'error');
                    });
            }
        });
    }

    // Fungsi SIAP DIAMBIL (Trigger untuk Kurir)
    function makeReadyForPickup(id) {
        Swal.fire({
            title: 'Siap Diambil Kurir?',
            text: "Pesanan ini akan muncul di Bursa Tugas Kurir untuk di-claim.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            confirmButtonText: 'Ya, Siapkan!'
        }).then(res => {
            if(res.isConfirmed) {
                // Endpoint untuk mengubah status menjadi 'ready' di tabel deliveries
                axios.post(`/api/deliveries/ready/${id}`)
                    .then(r => {
                        Swal.fire('Status Diperbarui', 'Menunggu kurir mengambil paket.', 'success');
                        load();
                    })
                    .catch(err => {
                        Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
                    });
            }
        });
    }

    // Fungsi REJECT
    function rejectRequest(id) {
        Swal.fire({
            title: 'Tolak Permintaan?',
            text: "Status akan berubah menjadi REJECTED.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Tolak!'
        }).then(res => {
            if(res.isConfirmed) {
                axios.post(`/api/requests/${id}/reject`).then(() => {
                    Swal.fire('Ditolak', 'Permintaan dibatalkan.', 'info');
                    load();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', load);
</script>

<style>
    /* Styling khusus tabel backoffice */
    .table thead th { font-size: 11px; letter-spacing: 0.5px; border-top: none; }
    .btn-group .btn { font-size: 12px; font-weight: 600; }
    .badge { font-weight: 600; letter-spacing: 0.3px; }
    .italic { font-style: italic; }
</style>
@endsection