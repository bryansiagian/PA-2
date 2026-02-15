@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Manajemen Inventaris Obat</h4>
            <p class="text-muted small mb-0">Kelola stok, katalog produk, dan kategori obat secara real-time.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddDrug">
                <i class="bi bi-plus-lg me-2"></i> Tambah Obat
            </button>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalStockIn">
                <i class="bi bi-arrow-down-up me-2"></i> Stock In
            </button>
        </div>
    </div>

    <!-- Statistik Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-primary border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-3"><i class="bi bi-capsule text-primary fs-4"></i></div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">TOTAL ITEM</small>
                        <h3 class="fw-bold mb-0" id="statTotalItems">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-warning border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning bg-opacity-10 p-3 rounded-3"><i class="bi bi-exclamation-triangle text-warning fs-4"></i></div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">STOK RENDAH</small>
                        <h3 class="fw-bold mb-0 text-warning" id="statLowStock">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-danger border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-danger bg-opacity-10 p-3 rounded-3"><i class="bi bi-x-octagon text-danger fs-4"></i></div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">STOK HABIS</small>
                        <h3 class="fw-bold mb-0 text-danger" id="statOutOfStock">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Obat / SKU</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="drugTableBody">
                    <tr><td colspan="6" class="text-center py-5 text-muted">Mengambil data dari gudang...</td></tr>
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
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i> Daftarkan Obat Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddDrug" onsubmit="submitNewDrug(event)">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Nama Obat</label>
                            <input type="text" name="name" class="form-control border-0 bg-light py-2" placeholder="Amoxicillin 500mg" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">SKU / Kode Obat</label>
                            <input type="text" name="sku" class="form-control border-0 bg-light py-2" placeholder="AMX-001" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Kategori</label>
                            <select name="category_id" id="add_category_id" class="form-select border-0 bg-light py-2" required>
                                <option value="" selected disabled>Memuat Kategori...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Satuan (Unit)</label>
                            <input type="text" name="unit" class="form-control border-0 bg-light py-2" placeholder="Tablet / Botol" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Stok Awal</label>
                            <input type="number" name="stock" class="form-control border-0 bg-light py-2" value="0" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Minimal Stok</label>
                            <input type="number" name="min_stock" class="form-control border-0 bg-light py-2" value="10" min="0" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold small">Foto Produk</label>
                            <input type="file" name="image" class="form-control border-0 bg-light py-2" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-white text-muted" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSaveDrug" class="btn btn-success rounded-pill px-4 fw-bold">Simpan Produk</button>
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
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-down-up me-2"></i> Tambah Stok Masuk</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="stockInForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Pilih Produk Obat</label>
                        <select id="selectDrug" class="form-select border-0 bg-light py-2" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Jumlah Penambahan</label>
                        <input type="number" id="inputQty" class="form-control border-0 bg-light py-2" min="1" placeholder="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-white text-muted" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnSubmitStock" onclick="submitStockIn()" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Simpan Stok</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: EDIT DATA OBAT
     ========================================== -->
<div class="modal fade" id="modalEditDrug" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Edit Informasi Obat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditDrug" onsubmit="updateDrug(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Nama Obat</label>
                            <input type="text" id="edit_name" name="name" class="form-control border-0 bg-light py-2" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">SKU</label>
                            <input type="text" id="edit_sku" name="sku" class="form-control border-0 bg-light py-2" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Kategori</label>
                            <select id="edit_category_id" name="category_id" class="form-select border-0 bg-light py-2" required></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Satuan</label>
                            <input type="text" id="edit_unit" name="unit" class="form-control border-0 bg-light py-2" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Minimal Stok</label>
                            <input type="number" id="edit_min_stock" name="min_stock" class="form-control border-0 bg-light py-2" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Ganti Foto (Opsional)</label>
                            <input type="file" name="image" class="form-control border-0 bg-light py-2" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-white text-muted" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnUpdateSave" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    // 1. Fungsi Inisialisasi - Memuat Kategori & Obat
    function initData() {
        const addCatSelect = document.getElementById('add_category_id');
        const editCatSelect = document.getElementById('edit_category_id');

        axios.get('/api/categories')
            .then(res => {
                const categories = res.data;
                let options = '<option value="" selected disabled>-- Pilih Kategori --</option>';

                categories.forEach(cat => {
                    options += `<option value="${cat.id}">${cat.name}</option>`;
                });

                addCatSelect.innerHTML = options;
                editCatSelect.innerHTML = options;

                // Setelah kategori dimuat, baru muat data obat
                fetchDrugs();
            })
            .catch(err => {
                console.error("Gagal memuat kategori:", err);
                Swal.fire('Error', 'Gagal terhubung ke database kategori', 'error');
            });
    }

    // 2. Ambil List Obat & Update Statistik
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
                // --- LOGIKA STATISTIK ---
                if (d.stock <= 0) out++;
                else if (d.stock <= d.min_stock) low++;

                // --- LOGIKA BADGE STATUS (DIPERBAIKI) ---
                let badgeClass = "";
                let statusText = "";

                if (d.stock <= 0) {
                    badgeClass = "bg-danger text-white"; // Merah pekat
                    statusText = "HABIS";
                } else if (d.stock <= d.min_stock) {
                    badgeClass = "bg-warning text-dark"; // Kuning
                    statusText = "RENDAH";
                } else {
                    badgeClass = "bg-success text-white"; // Hijau pekat
                    statusText = "AMAN";
                }

                // --- LOGIKA FOTO (DIPERBAIKI) ---
                let imgUrl = d.image;
                // Jika image tidak ada, gunakan placeholder
                if (!imgUrl) {
                    imgUrl = 'https://placehold.co/200x200?text=No+Image';
                }
                // Jika image adalah path lokal (diawali 'storage/'), tambahkan slash di depan
                else if (!imgUrl.startsWith('http')) {
                    imgUrl = `/${imgUrl}`;
                }

                html += `
                <tr class="border-bottom">
                    <td class="ps-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3" style="width: 45px; height: 45px;">
                                <img src="${imgUrl}"
                                    class="rounded-3 shadow-sm w-100 h-100"
                                    style="object-fit:cover;"
                                    onerror="this.src='https://placehold.co/200x200?text=Error'">
                            </div>
                            <div>
                                <span class="fw-bold text-dark d-block">${d.name}</span>
                                <code class="small text-muted">#${d.sku}</code>
                            </div>
                        </div>
                    </td>
                    <td><span class="text-muted small">${d.category ? d.category.name : '-'}</span></td>
                    <td class="text-center fw-bold text-dark" style="font-size: 1.1rem;">${d.stock}</td>
                    <td><small class="text-muted">${d.unit}</small></td>
                    <td>
                        <span class="badge ${badgeClass} px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.7rem; min-width: 80px;">
                            ${statusText}
                        </span>
                    </td>
                    <td class="text-center pe-4">
                        <button onclick="openEditModal(${d.id})" class="btn btn-light btn-sm rounded-circle me-1 text-primary shadow-sm">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="confirmDelete(${d.id}, '${d.name}')" class="btn btn-light btn-sm rounded-circle text-danger shadow-sm">
                            <i class="bi bi-trash"></i>
                        </button>
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

    // 3. Simpan Obat Baru (Multipart/FormData)
    function submitNewDrug(event) {
        event.preventDefault();
        const btn = document.getElementById('btnSaveDrug');
        const formData = new FormData(document.getElementById('formAddDrug'));

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        axios.post('/api/drugs', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
            .then(res => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddDrug')).hide();
                Swal.fire('Berhasil!', 'Obat baru telah ditambahkan.', 'success');
                document.getElementById('formAddDrug').reset();
                fetchDrugs();
            })
            .catch(err => {
                Swal.fire('Error', err.response.data.message || 'Gagal menyimpan.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Simpan Produk';
            });
    }

    // 4. Update Stok (Stock In)
    function submitStockIn() {
        const drugId = document.getElementById('selectDrug').value;
        const qty = document.getElementById('inputQty').value;
        const btn = document.getElementById('btnSubmitStock');

        if(!drugId || qty <= 0) return Swal.fire('Error', 'Input tidak valid', 'error');

        btn.disabled = true;
        axios.post('/api/drugs/stock-in', { drug_id: drugId, quantity: qty })
            .then(res => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalStockIn')).hide();
                Swal.fire('Berhasil!', 'Stok diperbarui.', 'success');
                document.getElementById('stockInForm').reset();
                fetchDrugs();
            })
            .finally(() => btn.disabled = false);
    }

    // 5. Edit & Update
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

        // Laravel menganggap PUT pada FormData sebagai masalah, gunakan POST dengan _method
        formData.append('_method', 'PUT');

        btn.disabled = true;
        axios.post(`/api/drugs/${id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
            .then(res => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditDrug')).hide();
                Swal.fire('Berhasil!', 'Informasi obat diperbarui.', 'success');
                fetchDrugs();
            })
            .catch(err => Swal.fire('Error', 'Gagal memperbarui data.', 'error'))
            .finally(() => btn.disabled = false);
    }

    // 6. Delete
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Obat?',
            text: `Data "${name}" akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then(result => {
            if (result.isConfirmed) {
                axios.delete(`/api/drugs/${id}`).then(res => {
                    Swal.fire('Terhapus!', 'Obat telah dihapus dari katalog.', 'success');
                    fetchDrugs();
                });
            }
        });
    }

    // Jalankan inisialisasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', initData);
</script>

<style>
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .table thead th { font-size: 11px; letter-spacing: 0.5px; }
    .rounded-4 { border-radius: 1rem !important; }
</style>
@endsection