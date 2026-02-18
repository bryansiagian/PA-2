@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Struktur Organisasi</h4>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalOrg">
            <i class="bi bi-plus-lg me-2"></i> Tambah Pengurus
        </button>
    </div>

    <div id="orgList" class="row g-4">
        <!-- Render via JS -->
    </div>
</div>

<!-- MODAL TAMBAH ORG -->
<div class="modal fade" id="modalOrg" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0"><h5>Tambah Struktur</h5></div>
            <form id="orgForm" onsubmit="saveOrg(event)">
                <div class="modal-body p-4">
                    <div class="mb-3"><label class="small fw-bold">Nama Lengkap</label><input type="text" name="name" class="form-control bg-light border-0" required></div>
                    <div class="mb-3"><label class="small fw-bold">Jabatan</label><input type="text" name="position" class="form-control bg-light border-0" required></div>
                    <div class="mb-3"><label class="small fw-bold">Foto</label><input type="file" name="photo" class="form-control bg-light border-0" accept="image/*" required></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function loadOrg() {
        axios.get('/api/cms/org').then(res => {
            let html = '';
            res.data.forEach(o => {
                const img = o.photo ? `/storage/${o.photo}` : 'https://ui-avatars.com/api/?name='+o.name;
                html += `
                <div class="col-md-3 text-center">
                    <div class="card border-0 shadow-sm p-4 rounded-4">
                        <img src="${img}" class="rounded-circle mb-3 mx-auto" width="100" height="100" style="object-fit:cover">
                        <h6 class="fw-bold mb-1">${o.name}</h6>
                        <small class="text-primary">${o.position}</small>
                    </div>
                </div>`;
            });
            document.getElementById('orgList').innerHTML = html;
        });
    }

    function saveOrg(e) {
        e.preventDefault();
        axios.post('/api/cms/org', new FormData(e.target)).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalOrg')).hide();
            loadOrg();
        });
    }

    document.addEventListener('DOMContentLoaded', loadOrg);
</script>
@endsection