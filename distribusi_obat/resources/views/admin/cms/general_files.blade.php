@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold">Manajemen Dokumen & File</h4>
            <p class="text-muted small">Kelola dokumen yang dapat diunduh oleh publik untuk keperluan kerjasama.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFile">
            <i class="bi bi-cloud-upload me-2"></i> Unggah Dokumen
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small fw-bold text-muted">
                        <th class="ps-4 py-3">NAMA DOKUMEN</th>
                        <th>UKURAN / TIPE</th>
                        <th>PENGUNGGAH</th>
                        <th class="text-center pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody id="fileTableBody">
                    <tr><td colspan="4" class="text-center py-5">Memuat data file...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL UNGGAH -->
<div class="modal fade" id="modalFile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold">Unggah Dokumen Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="fileForm" onsubmit="saveFile(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold">Judul Dokumen</label>
                        <input type="text" name="name" class="form-control border-0 bg-light" placeholder="Contoh: Formulir Kerjasama Apotek" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Pilih File (PDF/DOCX)</label>
                        <input type="file" name="file" class="form-control border-0 bg-light" required>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="submit" id="btnSave" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Mulai Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchFiles() {
        axios.get('/api/cms/general-files').then(res => {
            let html = '';
            res.data.forEach(f => {
                html += `
                <tr class="border-bottom">
                    <td class="ps-4 py-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf fs-4 text-danger me-3"></i>
                            <span class="fw-bold text-dark">${f.name}</span>
                        </div>
                    </td>
                    <td><small class="text-muted text-uppercase">Document</small></td>
                    <td><small>${f.creator ? f.creator.name : 'Admin'}</small></td>
                    <td class="text-center pe-4">
                        <a href="/storage/${f.file_path}" target="_blank" class="btn btn-sm btn-light rounded-circle text-primary me-1"><i class="bi bi-download"></i></a>
                        <button onclick="deleteFile(${f.id})" class="btn btn-sm btn-light rounded-circle text-danger"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
            document.getElementById('fileTableBody').innerHTML = html || '<tr><td colspan="4" class="text-center py-4">Belum ada file.</td></tr>';
        });
    }

    function saveFile(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const btn = document.getElementById('btnSave');
        btn.disabled = true;

        axios.post('/api/cms/general-files', formData).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalFile')).hide();
            Swal.fire('Berhasil!', 'File telah tersedia untuk publik.', 'success');
            fetchFiles();
            e.target.reset();
        }).finally(() => btn.disabled = false);
    }

    function deleteFile(id) {
        Swal.fire({ title: 'Hapus File?', icon: 'warning', showCancelButton: true }).then(res => {
            if(res.isConfirmed) axios.delete(`/api/cms/general-files/${id}`).then(() => fetchFiles());
        });
    }

    document.addEventListener('DOMContentLoaded', fetchFiles);
</script>
@endsection