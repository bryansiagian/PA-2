@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Manajemen Lokasi Penyimpanan</h4>
            <div class="text-muted">Kelola koordinat Rak dan Baris untuk mempermudah proses picking obat</div>
        </div>
        <div class="ms-3">
            <button class="btn btn-indigo shadow-sm" onclick="openAddModal()">
                <i class="ph-map-pin-line me-2"></i> Tambah Lokasi
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-layout me-2 text-primary"></i>Daftar Rak & Baris</h5>
            <div class="ms-auto">
                <span class="badge bg-indigo text-white fw-bold px-3 shadow-sm" id="totalLocationBadge">
                <i class="ph-map-pin me-1"></i> 0 Titik Lokasi
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">Nama Rak</th>
                        <th class="text-center">Nomor Baris</th>
                        <th class="text-center">Total Obat Disimpan</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="locationTableBody">
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-muted me-2"></div>
                            Sinkronisasi data denah gudang...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT LOKASI (Limitless Style) -->
<div class="modal fade" id="modalLocation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold" id="modalTitle"><i class="ph-map-pin-line me-2"></i>Data Lokasi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="locationForm" onsubmit="event.preventDefault(); saveLocation();">
                    <input type="hidden" id="loc_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama / Kode Rak</label>
                        <div class="form-control-feedback form-control-feedback-start">
                            <input type="text" id="rack_name" class="form-control border-light-subtle" placeholder="Contoh: RAK-A, RAK-B, atau Narkotika" required>
                            <div class="form-control-feedback-icon"><i class="ph-package text-muted"></i></div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Nomor / Kode Baris</label>
                        <div class="form-control-feedback form-control-feedback-start">
                            <input type="text" id="row_number" class="form-control border-light-subtle" placeholder="Contoh: 01, 02, atau Atas" required>
                            <div class="form-control-feedback-icon"><i class="ph-list-numbers text-muted"></i></div>
                        </div>
                        <div class="form-text fs-xs text-muted">Kombinasi Rak dan Baris akan muncul di daftar petik kurir/operator.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                <button type="button" id="btnSave" onclick="saveLocation()" class="btn btn-indigo px-4 fw-bold shadow-sm">
                    SIMPAN LOKASI
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchLocations() {
        const tableBody = document.getElementById('locationTableBody');
        // Ganti endpoint sesuai API Anda, contoh: /api/warehouse-locations
        axios.get('/api/locations').then(res => {
            let html = '';
            const locations = res.data;

            document.getElementById('totalLocationBadge').innerText = `${locations.length} Titik Lokasi`;

            locations.forEach(loc => {
                html += `
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-2 rounded me-3 text-indigo">
                                <i class="ph-package fs-base"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">${loc.rack_name}</div>
                                <div class="fs-xs text-muted">Area Gudang Utama</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-indigo border py-1 px-3 fw-bold">
                            Baris: ${loc.row_number}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="fw-bold text-primary">${loc.drugs_count || 0} Item</div>
                    </td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex">
                            <button onclick="openEditModal(${loc.id}, '${loc.rack_name}', '${loc.row_number}')" class="btn btn-sm btn-light text-primary border-0 me-2">
                                <i class="ph-note-pencil"></i>
                            </button>
                            <button onclick="confirmDelete(${loc.id}, '${loc.rack_name} - ${loc.row_number}')" class="btn btn-sm btn-light text-danger border-0">
                                <i class="ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
            tableBody.innerHTML = html || '<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada denah lokasi penyimpanan.</td></tr>';
        });
    }

    function openAddModal() {
        document.getElementById('loc_id').value = '';
        document.getElementById('rack_name').value = '';
        document.getElementById('row_number').value = '';
        document.getElementById('modalTitle').innerHTML = '<i class="ph-map-pin-line me-2"></i>Tambah Titik Lokasi';
        new bootstrap.Modal(document.getElementById('modalLocation')).show();
    }

    function openEditModal(id, rack, row) {
        document.getElementById('loc_id').value = id;
        document.getElementById('rack_name').value = rack;
        document.getElementById('row_number').value = row;
        document.getElementById('modalTitle').innerHTML = '<i class="ph-note-pencil me-2"></i>Edit Titik Lokasi';
        new bootstrap.Modal(document.getElementById('modalLocation')).show();
    }

    function saveLocation() {
        const id = document.getElementById('loc_id').value;
        const rack = document.getElementById('rack_name').value;
        const row = document.getElementById('row_number').value;
        const btn = document.getElementById('btnSave');

        if(!rack || !row) return Swal.fire('Error', 'Data Rak dan Baris wajib diisi', 'error');

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        const request = id ? axios.put(`/api/locations/${id}`, { rack_name: rack, row_number: row })
                           : axios.post('/api/locations', { rack_name: rack, row_number: row });

        request.then(res => {
            bootstrap.Modal.getInstance(document.getElementById('modalLocation')).hide();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Denah lokasi diperbarui', confirmButtonColor: '#4f46e5' });
            fetchLocations();
        }).catch(err => {
            Swal.fire('Gagal', err.response.data.message || 'Terjadi kesalahan sistem', 'error');
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'SIMPAN LOKASI';
        });
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Lokasi?',
            text: `Lokasi "${name}" akan dihapus. Pastikan tidak ada obat yang terhubung ke lokasi ini.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            confirmButtonColor: '#ef4444'
        }).then(result => {
            if(result.isConfirmed) {
                axios.delete(`/api/locations/${id}`).then(res => {
                    Swal.fire('Terhapus!', 'Titik lokasi telah dihapus.', 'success');
                    fetchLocations();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchLocations);
</script>

<style>
    .bg-indigo { background-color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }
    .text-indigo { color: #5c68e2 !important; }
    .card { border-radius: 0.5rem; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
