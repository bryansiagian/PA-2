@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold">Manajemen Kontak & Sosial Media</h4>
            <p class="text-muted small mb-0">Kelola informasi yang muncul di bagian footer dan halaman kontak.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-2"></i> Tambah Kontak
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small fw-bold text-muted">
                        <th class="ps-4 py-3">NAMA KONTAK</th>
                        <th>KEY IDENTIFIER</th>
                        <th>VALUE / ISI</th>
                        <th class="text-center pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody id="contactTableBody">
                    <tr><td colspan="4" class="text-center py-5">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT KONTAK -->
<div class="modal fade" id="modalContact" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">Kontak Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="contactForm" onsubmit="saveContact(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="contact_id">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Nama Tampilan (Title)</label>
                        <input type="text" name="title" id="title" class="form-control border-0 bg-light" placeholder="Contoh: WhatsApp CS" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Key Identitas (Unik)</label>
                        <input type="text" name="key" id="key" class="form-control border-0 bg-light" placeholder="Contoh: whatsapp_cs" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Isi Kontak (Value)</label>
                        <input type="text" name="value" id="value" class="form-control border-0 bg-light" placeholder="Contool: 0812345678" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Ikon/Gambar (Opsional)</label>
                        <input type="file" name="image" class="form-control border-0 bg-light" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="submit" id="btnSave" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">Simpan Kontak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchContacts() {
        axios.get('/api/cms/contacts').then(res => {
            let html = '';
            res.data.forEach(c => {
                html += `
                <tr class="border-bottom">
                    <td class="ps-4 py-3">
                        <div class="fw-bold text-dark">${c.title}</div>
                    </td>
                    <td><code class="small">${c.key}</code></td>
                    <td><span class="text-muted small">${c.value}</span></td>
                    <td class="text-center pe-4">
                        <button onclick="editContact(${c.id})" class="btn btn-sm btn-light rounded-circle text-primary me-1 shadow-sm"><i class="bi bi-pencil"></i></button>
                        <button onclick="deleteContact(${c.id}, '${c.title}')" class="btn btn-sm btn-light rounded-circle text-danger shadow-sm"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
            document.getElementById('contactTableBody').innerHTML = html || '<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data kontak.</td></tr>';
        });
    }

    function openAddModal() {
        document.getElementById('contactForm').reset();
        document.getElementById('contact_id').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Kontak Baru';
        new bootstrap.Modal(document.getElementById('modalContact')).show();
    }

    function editContact(id) {
        axios.get(`/api/cms/contacts`).then(res => {
            const c = res.data.find(item => item.id === id);
            document.getElementById('contact_id').value = c.id;
            document.getElementById('title').value = c.title;
            document.getElementById('key').value = c.key;
            document.getElementById('value').value = c.value;
            document.getElementById('modalTitle').innerText = 'Edit Kontak';
            new bootstrap.Modal(document.getElementById('modalContact')).show();
        });
    }

    function saveContact(e) {
        e.preventDefault();
        const id = document.getElementById('contact_id').value;
        const btn = document.getElementById('btnSave');
        const formData = new FormData(e.target);

        if(id) formData.append('_method', 'PUT');
        const url = id ? `/api/cms/contacts/${id}` : '/api/cms/contacts';

        btn.disabled = true;
        axios.post(url, formData).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalContact')).hide();
            Swal.fire('Berhasil!', 'Data kontak diperbarui.', 'success');
            fetchContacts();
        }).catch(err => {
            Swal.fire('Gagal', err.response.data.message, 'error');
        }).finally(() => btn.disabled = false);
    }

    function deleteContact(id, title) {
        Swal.fire({ title: 'Hapus Kontak?', text: `Data "${title}" akan dihapus.`, icon: 'warning', showCancelButton: true }).then(res => {
            if(res.isConfirmed) {
                axios.delete(`/api/cms/contacts/${id}`).then(() => fetchContacts());
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchContacts);
</script>
@endsection