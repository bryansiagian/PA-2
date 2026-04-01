@extends('layouts.backoffice')

@section('page_title', 'Manajemen Profil & Kontak')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="mb-4">
        <h4 class="fw-bold text-primary mb-1">Pengaturan Konten Website</h4>
        <div class="text-muted small">Kelola narasi yayasan dan informasi kontak yang tampil pada Landing Page.</div>
    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4">

        <!-- Header Card -->
        <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="bi bi-pencil-square me-2"></i>
                Editor Konten Statis
            </h6>
        </div>

        <div class="card-body p-4">

            <!-- TAB -->
            <ul class="nav nav-pills mb-4 bg-light p-2 rounded-pill justify-content-center custom-tab">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill fw-bold px-4" data-bs-toggle="pill" data-bs-target="#tab-about">
                        <i class="bi bi-info-circle me-1"></i> Tentang Kami
                    </button>
                </li>
                <li class="nav-item ms-2">
                    <button class="nav-link rounded-pill fw-bold px-4" data-bs-toggle="pill" data-bs-target="#tab-history">
                        <i class="bi bi-clock-history me-1"></i> Sejarah
                    </button>
                </li>
                <li class="nav-item ms-2">
                    <button class="nav-link rounded-pill fw-bold px-4" data-bs-toggle="pill" data-bs-target="#tab-vision">
                        <i class="bi bi-lightbulb me-1"></i> Visi & Misi
                    </button>
                </li>
            </ul>

            <!-- CONTENT -->
            <div class="tab-content">

                <!-- ABOUT -->
                <div class="tab-pane fade show active" id="tab-about">
                    <form onsubmit="updateProfileContent(event, 'about')">

                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Judul Seksi</label>
                            <input type="text" id="about_title"
                                   class="form-control shadow-sm text-dark fw-semibold"
                                   style="background:#f8fafc; border-radius:10px;"
                                   placeholder="Judul..." required>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Narasi Tentang Kami</label>
                            <textarea id="about_content"
                                class="form-control shadow-sm text-dark"
                                rows="8"
                                style="background:#f8fafc; border-radius:12px; line-height:1.6;"
                                placeholder="Jelaskan mengenai yayasan..." required></textarea>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                Update Profil
                            </button>
                        </div>
                    </form>
                </div>

                <!-- HISTORY -->
                <div class="tab-pane fade" id="tab-history">
                    <form onsubmit="updateProfileContent(event, 'history')">

                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Judul Sejarah</label>
                            <input type="text" id="history_title"
                                   class="form-control shadow-sm text-dark"
                                   style="background:#f8fafc; border-radius:10px;" required>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Konten Sejarah</label>
                            <textarea id="history_content"
                                class="form-control shadow-sm text-dark"
                                rows="8"
                                style="background:#f8fafc; border-radius:12px;"></textarea>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                Simpan Sejarah
                            </button>
                        </div>
                    </form>
                </div>

                <!-- VISION -->
                <div class="tab-pane fade" id="tab-vision">
                    <form onsubmit="updateProfileContent(event, 'vision_mission')">

                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Judul Visi & Misi</label>
                            <input type="text" id="vision_mission_title"
                                   class="form-control shadow-sm text-dark"
                                   style="background:#f8fafc; border-radius:10px;" required>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Konten</label>
                            <textarea id="vision_mission_content"
                                class="form-control shadow-sm text-dark"
                                rows="8"
                                style="background:#f8fafc; border-radius:12px;"
                                placeholder="<ul><li>Misi...</li></ul>"></textarea>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                Simpan Visi Misi
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

function loadCmsData() {
    axios.get('/api/public/landing-page').then(res => {
        const profiles = res.data.profiles;

        ['about','history','vision_mission'].forEach(k => {
            if(profiles[k]) {
                document.getElementById(k + '_title').value = profiles[k].title;
                document.getElementById(k + '_content').value = profiles[k].content;
            }
        });
    });
}

function updateProfileContent(event, key) {
    event.preventDefault();

    const btn = event.submitter;
    const formData = new FormData();

    formData.append('key', key);
    formData.append('title', document.getElementById(key + '_title').value);
    formData.append('content', document.getElementById(key + '_content').value);
    formData.append('_method', 'PUT');

    btn.disabled = true;

    Swal.fire({title:'Menyimpan...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});

    axios.post('/api/cms/profile', formData)
        .then(() => {
            Swal.fire({icon:'success', title:'Berhasil', timer:1500, showConfirmButton:false});
            loadCmsData();
        })
        .catch(() => {
            Swal.fire('Error','Gagal update','error');
        })
        .finally(()=> btn.disabled=false);
}

document.addEventListener('DOMContentLoaded', loadCmsData);
</script>

<style>

/* ===== GLOBAL FIX ===== */
body {
    background-color: #f1f5f9 !important;
}

/* CARD */
.card {
    background: #ffffff !important;
    border-radius: 12px !important;
}

/* HEADER TEXT */
h4 {
    color: #1e293b;
}

/* ===== FORM ===== */
.form-control {
    background-color: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    color: #1e293b !important;
}

.form-control:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59,130,246,0.1);
}

/* ===== TAB STYLE (SAMAKAN ORG STYLE) ===== */
.custom-tab {
    background: transparent !important;
}

/* TAB NORMAL */
.custom-tab .nav-link {
    background: #e2e8f0;
    color: #475569 !important;
    border-radius: 999px;
    padding: 8px 18px;
}

/* TAB AKTIF */
.custom-tab .nav-link.active {
    background: #3b82f6 !important;
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(59,130,246,0.3);
}

/* BUTTON */
.btn-primary {
    background: #3b82f6;
    border: none;
}

.btn-primary:hover {
    background: #2563eb;
}

/* REMOVE ABU AREA */
.container-fluid,
.card-body {
    background-color: transparent !important;
}

</style>

@endsection

