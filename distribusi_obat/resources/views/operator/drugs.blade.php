@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Manajemen Inventaris Obat</h4>
            <div class="text-muted">Kelola stok, katalog produk, dan kategori obat secara real-time</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <button class="btn btn-indigo shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddDrug">
                <i class="ph-plus-circle me-2"></i> Tambah Obat
            </button>
            <button class="btn btn-teal shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#modalStockIn">
                <i class="ph-arrows-down-up me-2"></i> Stock In
            </button>
        </div>
    </div>

    <!-- Statistik Row (Limitless Style) -->
    <div class="row mb-3">
        <div class="col-lg-4">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statTotalItems">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Total Item Terdaftar</div>
                    </div>
                    <i class="ph-capsule ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-body bg-warning text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statLowStock">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Stok Hampir Habis</div>
                    </div>
                    <i class="ph-warning-circle ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-body bg-pink text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statOutOfStock">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Stok Kosong</div>
                    </div>
                    <i class="ph-x-circle ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-package me-2 text-primary"></i>Katalog Gudang Obat</h5>
            <div class="ms-auto">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <span class="input-group-text bg-light border-0"><i class="ph-magnifying-glass"></i></span>
                    <input type="text" class="form-control bg-light border-0" placeholder="Cari SKU/Nama...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">Obat / SKU</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok</th>
                        <th>Satuan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="drugTableBody">
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-muted me-2"></div>
                            Menghubungkan ke database gudang...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: TAMBAH OBAT BARU
     ========================================== -->
<div class="modal fade" id="modalAddDrug" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-plus-circle me-2"></i>Daftarkan Obat Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddDrug" onsubmit="submitNewDrug(event)">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Nama Obat</label>
                            <input type="text" name="name" class="form-control border-light-subtle" placeholder="Amoxicillin 500mg" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">SKU / Kode Obat</label>
                            <input type="text" name="sku" class="form-control border-light-subtle bg-light" placeholder="AMX-001" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Kategori</label>
                            <select name="category_id" id="add_category_id" class="form-select border-light-subtle" required>
                                <option value="" selected disabled>Memuat...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Satuan (Unit)</label>
                            <input type="text" name="unit" class="form-control border-light-subtle" placeholder="Tablet / Botol" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Stok Awal</label>
                            <input type="number" name="stock" class="form-control border-light-subtle" value="0" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Minimal Stok</label>
                            <input type="number" name="min_stock" class="form-control border-light-subtle" value="10" min="0" required>
                        </div>
                        <div class="col-12 mb-0">
                            <label class="form-label fw-bold small">Foto Produk</label>
                            <input type="file" name="image" class="form-control border-light-subtle" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSaveDrug" class="btn btn-indigo px-4 fw-bold">SIMPAN PRODUK</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: STOCK IN (TAMBAH STOK)
     ========================================== -->
<div class="modal fade" id="modalStockIn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-teal text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-arrows-down-up me-2"></i>Stok Masuk</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="stockInForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih Produk Obat</label>
                        <select id="selectDrug" class="form-select border-light-subtle" required></select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Jumlah Penambahan</label>
                        <input type="number" id="inputQty" class="form-control border-light-subtle" min="1" placeholder="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                <button type="button" id="btnSubmitStock" onclick="submitStockIn()" class="btn btn-teal text-white px-4 fw-bold shadow-sm">SIMPAN STOK</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: EDIT DATA OBAT
     ========================================== -->
<div class="modal fade" id="modalEditDrug" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-note-pencil me-2"></i>Edit Informasi Obat</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditDrug" onsubmit="updateDrug(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Nama Obat</label>
                            <input type="text" id="edit_name" name="name" class="form-control border-light-subtle" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">SKU</label>
                            <input type="text" id="edit_sku" name="sku" class="form-control border-light-subtle bg-light" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Kategori</label>
                            <select id="edit_category_id" name="category_id" class="form-select border-light-subtle" required></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Satuan</label>
                            <input type="text" id="edit_unit" name="unit" class="form-control border-light-subtle" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Minimal Stok</label>
                            <input type="number" id="edit_min_stock" name="min_stock" class="form-control border-light-subtle" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Ganti Foto (Opsional)</label>
                            <input type="file" name="image" class="form-control border-light-subtle" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnUpdateSave" class="btn btn-indigo px-4 fw-bold">SIMPAN PERUBAHAN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function initData() {
        const addCatSelect = document.getElementById('add_category_id');
        const editCatSelect = document.getElementById('edit_category_id');

        axios.get('/api/categories').then(res => {
            let options = '<option value="" selected disabled>-- Pilih Kategori --</option>';
            res.data.forEach(cat => { options += `<option value="${cat.id}">${cat.name}</option>`; });
            addCatSelect.innerHTML = options;
            editCatSelect.innerHTML = options;
            fetchDrugs();
        });
    }

    function fetchDrugs() {
        const tableBody = document.getElementById('drugTableBody');
        const stockSelect = document.getElementById('selectDrug');

        axios.get('/api/drugs').then(res => {
            const drugs = res.data;
            let html = '';
            let options = '<option value="" selected disabled>-- Pilih Produk Obat --</option>';
            let low = 0, out = 0;

            if (drugs.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-5">Katalog masih kosong.</td></tr>';
                return;
            }

            drugs.forEach(d => {
                if (d.stock <= 0) out++;
                else if (d.stock <= d.min_stock) low++;

                let badgeHtml = "";
                if (d.stock <= 0) {
                    badgeHtml = '<span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-2 py-1">STOK HABIS</span>';
                } else if (d.stock <= d.min_stock) {
                    badgeHtml = '<span class="badge bg-warning bg-opacity-10 text-warning fw-bold px-2 py-1">STOK RENDAH</span>';
                } else {
                    badgeHtml = '<span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">STOK AMAN</span>';
                }

                let imgUrl = d.image ? (d.image.startsWith('http') ? d.image : `/${d.image}`) : 'https://placehold.co/200x200?text=No+Image';

                html += `
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center">
                            <img src="${imgUrl}" class="rounded shadow-sm me-3" style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <div class="fw-bold text-dark">${d.name}</div>
                                <div class="fs-xs text-muted font-monospace">${d.sku}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-muted border-0">${d.category ? d.category.name : '-'}</span></td>
                    <td class="text-center fw-bold text-indigo fs-base">${d.stock}</td>
                    <td><small class="text-muted text-uppercase">${d.unit}</small></td>
                    <td class="text-center">${badgeHtml}</td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex">
                            <button onclick="openEditModal(${d.id})" class="btn btn-sm btn-light text-primary border-0 me-2" title="Edit">
                                <i class="ph-note-pencil"></i>
                            </button>
                            <button onclick="confirmDelete(${d.id}, '${d.name}')" class="btn btn-sm btn-light text-danger border-0" title="Hapus">
                                <i class="ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
                options += `<option value="${d.id}">${d.name} (Sisa: ${d.stock})</option>`;
            });

            tableBody.innerHTML = html;
            if(stockSelect) stockSelect.innerHTML = options;
            document.getElementById('statTotalItems').innerText = drugs.length;
            document.getElementById('statLowStock').innerText = low;
            document.getElementById('statOutOfStock').innerText = out;
        });
    }

    // Logic Submit/Update/Delete tetap sama namun dengan spinner bertema Limitless
    function submitNewDrug(event) {
        event.preventDefault();
        const btn = document.getElementById('btnSaveDrug');
        const formData = new FormData(document.getElementById('formAddDrug'));
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        axios.post('/api/drugs', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
            .then(res => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddDrug')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Obat baru telah ditambahkan', confirmButtonColor: '#4f46e5' });
                document.getElementById('formAddDrug').reset();
                fetchDrugs();
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'SIMPAN PRODUK';
            });
    }

    function submitStockIn() {
        const drugId = document.getElementById('selectDrug').value;
        const qty = document.getElementById('inputQty').value;
        const btn = document.getElementById('btnSubmitStock');
        if(!drugId || qty <= 0) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i>...';

        axios.post('/api/drugs/stock-in', { drug_id: drugId, quantity: qty })
            .then(res => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalStockIn')).hide();
                Swal.fire({ icon: 'success', title: 'Stok Ditambah', text: 'Persediaan telah diperbarui', confirmButtonColor: '#0d9488' });
                document.getElementById('stockInForm').reset();
                fetchDrugs();
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'SIMPAN STOK';
            });
    }

    function openEditModal(id) {
        axios.get(`/api/drugs/${id}`).then(res => {
            const d = res.data;
            document.getElementById('edit_id').value = d.id;
            document.getElementById('edit_name').value = d.name;
            document.getElementById('edit_sku').value = d.sku;
            document.getElementById('edit_unit').value = d.unit;
            document.getElementById('edit_min_stock').value = d.min_stock;
            document.getElementById('edit_category_id').value = d.category_id;
            new bootstrap.Modal(document.getElementById('modalEditDrug')).show();
        });
    }

    function updateDrug(event) {
        event.preventDefault();
        const id = document.getElementById('edit_id').value;
        const btn = document.getElementById('btnUpdateSave');
        const formData = new FormData(document.getElementById('formEditDrug'));
        formData.append('_method', 'PUT');

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i>...';

        axios.post(`/api/drugs/${id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
            .then(res => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditDrug')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data obat telah diperbarui', confirmButtonColor: '#4f46e5' });
                fetchDrugs();
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'SIMPAN PERUBAHAN';
            });
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Obat?',
            text: `Data "${name}" akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            confirmButtonColor: '#ef4444',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light' }
        }).then(result => {
            if (result.isConfirmed) {
                axios.delete(`/api/drugs/${id}`).then(res => {
                    Swal.fire('Terhapus!', 'Data telah dikeluarkan dari katalog.', 'success');
                    fetchDrugs();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initData);
</script>

<style>
    .bg-indigo { background-color: #5c68e2 !important; }
    .bg-teal { background-color: #0d9488 !important; }
    .bg-pink { background-color: #db2777 !important; }
    .text-indigo { color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }

    .table td { padding: 0.75rem 1rem; }
    .ph-2x { font-size: 2.2rem; }
    .fs-base { font-size: 1rem; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
