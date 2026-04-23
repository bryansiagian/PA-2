@extends('layouts.backoffice')

@section('page_title', 'Manajemen Profil & Kontak')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="mb-4">
        <h4 class="fw-bold text-primary mb-1">Pengaturan Konten Website</h4>
        <div class="text-muted small">Kelola narasi yayasan dan informasi informasi penting pada Landing Page.</div>
    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="bi bi-pencil-square me-2"></i>
                Editor Konten Statis
            </h6>
        </div>

        <div class="card-body p-4">
            <!-- TAB NAV -->
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

            <!-- TAB CONTENT -->
            <div class="tab-content">
                <!-- ABOUT -->
                <div class="tab-pane fade show active" id="tab-about">
                    <form onsubmit="updateProfileContent(event, 'about')">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Judul Seksi</label>
                            <input type="text" id="about_title" class="form-control" placeholder="Judul..." required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Isi Konten</label>
                            <textarea id="about_content" class="form-control" rows="8" required></textarea>
                        </div>
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Profil</button>
                        </div>
                    </form>
                </div>

                <!-- HISTORY -->
                <div class="tab-pane fade" id="tab-history">
                    <form onsubmit="updateProfileContent(event, 'history')">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Judul Sejarah</label>
                            <input type="text" id="history_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Isi Konten</label>
                            <textarea id="history_content" class="form-control" rows="8" required></textarea>
                        </div>
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Sejarah</button>
                        </div>
                    </form>
                </div>

                <!-- VISION & MISSION (REVISED TO REPEATER) -->
                <div class="tab-pane fade" id="tab-vision">
                    <form onsubmit="updateVisionMission(event)">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Judul Utama</label>
                            <input type="text" id="vision_mission_title" class="form-control" placeholder="Contoh: Visi & Misi Yayasan" required>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase text-primary mb-2">Daftar Poin Visi & Misi</label>
                            <div id="misi-container">
                                <!-- Input Misi Akan Muncul Di Sini -->
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill mt-2" onclick="addMisiRow()">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Poin
                            </button>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" id="btnSaveVision" class="btn btn-primary rounded-pill px-4 fw-bold">
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

        // Load About & History (Normal)
        ['about','history'].forEach(k => {
            if(profiles[k]) {
                document.getElementById(k + '_title').value = profiles[k].title;
                document.getElementById(k + '_content').value = profiles[k].content;
            }
        });

        // Load Vision Mission (Parsing HTML to List)
        if(profiles['vision_mission']) {
            document.getElementById('vision_mission_title').value = profiles['vision_mission'].title;
            const content = profiles['vision_mission'].content;

            // Regex untuk mengambil isi di dalam tag <li>
            const misiItems = content.match(/<li>(.*?)<\/li>/g);
            const container = document.getElementById('misi-container');
            container.innerHTML = ''; // Clear

            if(misiItems) {
                misiItems.forEach(item => {
                    const cleanText = item.replace(/<\/?li>/g, '');
                    addMisiRow(cleanText);
                });
            } else {
                addMisiRow(); // Jika kosong kasih 1 baris
            }
        } else {
            addMisiRow();
        }
    });
}

// Fungsi Menambah Baris Input Misi
function addMisiRow(val = '') {
    const container = document.getElementById('misi-container');
    const index = container.children.length + 1;
    const div = document.createElement('div');
    div.className = 'input-group mb-2 misi-row';
    div.innerHTML = `
        <span class="input-group-text bg-white border-end-0 fw-bold text-primary">${index}</span>
        <input type="text" class="form-control border-start-0 misi-input" value="${val}" placeholder="Tuliskan poin visi/misi..." required>
        <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove(); reorderMisi();">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

// Menata ulang nomor urut jika ada yang dihapus
function reorderMisi() {
    const rows = document.querySelectorAll('.misi-row .input-group-text');
    rows.forEach((span, i) => span.innerText = i + 1);
}

// Fungsi Update Khusus Visi Misi (Convert List to HTML)
function updateVisionMission(event) {
    event.preventDefault();
    const title = document.getElementById('vision_mission_title').value;
    const inputs = document.querySelectorAll('.misi-input');

    // Bungkus ke dalam format HTML <ul>
    let htmlContent = '<ul class="list-unstyled">';
    inputs.forEach(input => {
        if(input.value.trim() !== '') {
            htmlContent += `<li>${input.value}</li>`;
        }
    });
    htmlContent += '</ul>';

    sendUpdate('vision_mission', title, htmlContent, event.submitter);
}

// Fungsi Update About & History
function updateProfileContent(event, key) {
    event.preventDefault();
    const title = document.getElementById(key + '_title').value;
    const content = document.getElementById(key + '_content').value;
    sendUpdate(key, title, content, event.submitter);
}

// Fungsi Pengiriman API Global
function sendUpdate(key, title, content, btn) {
    const formData = new FormData();
    formData.append('key', key);
    formData.append('title', title);
    formData.append('content', content);
    formData.append('_method', 'PUT');

    btn.disabled = true;
    Swal.fire({title:'Menyimpan...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});

    axios.post('/api/cms/profile', formData)
        .then(() => {
            Swal.fire({icon:'success', title:'Konten Diperbarui', timer:1500, showConfirmButton:false});
            loadCmsData();
        })
        .catch(() => {
            Swal.fire('Error','Gagal memperbarui data','error');
        })
        .finally(()=> btn.disabled=false);
}

document.addEventListener('DOMContentLoaded', loadCmsData);
</script>

<style>
body { background-color: #f1f5f9 !important; }
.card { background: #ffffff !important; border-radius: 15px !important; }
.form-control { border: 1px solid #e2e8f0 !important; }
.custom-tab .nav-link { background: #e2e8f0; color: #475569 !important; margin: 0 5px; }
.custom-tab .nav-link.active { background: #00838f !important; color: #fff !important; }
.btn-primary { background: #00838f; border: none; }
.btn-primary:hover { background: #006064; }
.misi-row .input-group-text { min-width: 45px; justify-content: center; }
</style>
@endsection
