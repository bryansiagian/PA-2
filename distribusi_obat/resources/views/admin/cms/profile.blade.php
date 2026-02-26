@extends('layouts.backoffice')

@section('page_title', 'Manajemen Profil & Kontak')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-sm-center justify-content-sm-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Pengaturan Konten Website</h4>
            <div class="text-muted small">Kelola narasi yayasan dan informasi kontak yang tampil pada Landing Page.</div>
        </div>
    </div>

    <!-- TABS CARD -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="ph-layout me-2 text-indigo"></i>Editor Konten Statis</h6>
        </div>

        <div class="card-body p-4">
            <!-- Nav Pills (Limitless Toolbar Style) -->
            <ul class="nav nav-pills nav-pills-bordered nav-pills-toolbar mb-4 bg-light p-1 rounded-pill justify-content-center" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill fw-bold" data-bs-toggle="pill" data-bs-target="#tab-about">
                        <i class="ph-info me-2"></i>Tentang Kami
                    </button>
                </li>
                <li class="nav-item ms-1">
                    <button class="nav-link rounded-pill fw-bold" data-bs-toggle="pill" data-bs-target="#tab-history">
                        <i class="ph-clock-counter-clockwise me-2"></i>Sejarah
                    </button>
                </li>
                <li class="nav-item ms-1">
                    <button class="nav-link rounded-pill fw-bold" data-bs-toggle="pill" data-bs-target="#tab-vision">
                        <i class="ph-lightbulb me-2"></i>Visi & Misi
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="pills-tabContent">

                <!-- TAB 1: ABOUT US -->
                <div class="tab-pane fade show active" id="tab-about">
                    <form onsubmit="updateProfileContent(event, 'about')">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Judul Seksi</label>
                                    <input type="text" id="about_title" class="form-control border-0 bg-light py-2" placeholder="Judul yang tampil..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Narasi Tentang Kami</label>
                                    <textarea id="about_content" class="form-control border-0 bg-light py-2" rows="8" placeholder="Jelaskan mengenai yayasan..." required></textarea>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3 text-center">
                                    <label class="fs-xs fw-bold text-uppercase text-muted d-block mb-2 text-start">Gambar Ilustrasi</label>
                                    <div class="p-2 border rounded-3 bg-light mb-2">
                                        <img id="about_preview" src="https://placehold.co/400x300?text=No+Image" class="img-fluid rounded-3 shadow-sm" style="max-height: 200px; object-fit: cover;">
                                    </div>
                                    <input type="file" id="about_image" class="form-control form-control-sm border-0 bg-light" accept="image/*" onchange="previewImage(this, 'about_preview')">
                                </div>
                            </div>
                        </div>
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-indigo rounded-pill px-4 fw-bold">Update Profil</button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: HISTORY -->
                <div class="tab-pane fade" id="tab-history">
                    <form onsubmit="updateProfileContent(event, 'history')">
                        <div class="mb-3">
                            <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Judul Sejarah</label>
                            <input type="text" id="history_title" class="form-control border-0 bg-light py-2" required>
                        </div>
                        <div class="mb-3">
                            <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Konten Sejarah Lengkap</label>
                            <textarea id="history_content" class="form-control border-0 bg-light py-2" rows="8" required></textarea>
                        </div>
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-indigo rounded-pill px-4 fw-bold">Simpan Sejarah</button>
                        </div>
                    </form>
                </div>

                <!-- TAB 3: VISION MISSION -->
                <div class="tab-pane fade" id="tab-vision">
                    <form onsubmit="updateProfileContent(event, 'vision_mission')">
                        <div class="mb-3">
                            <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Judul Visi & Misi</label>
                            <input type="text" id="vision_mission_title" class="form-control border-0 bg-light py-2" required>
                        </div>
                        <div class="mb-3">
                            <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Konten (Gunakan format HTML list)</label>
                            <textarea id="vision_mission_content" class="form-control border-0 bg-light py-2" rows="8" placeholder="Contoh: <ul><li>Misi 1</li></ul>" required></textarea>
                        </div>
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-indigo rounded-pill px-4 fw-bold">Simpan Visi Misi</button>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>
</div>

<script>
    // Header Token Global
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function loadCmsData() {
        axios.get('/api/public/landing-page').then(res => {
            const profiles = res.data.profiles;
            const contacts = res.data.contacts;

            // Load Profil (Table: profiles)
            const keys = ['about', 'history', 'vision_mission'];
            keys.forEach(k => {
                if(profiles[k]) {
                    document.getElementById(k + '_title').value = profiles[k].title;
                    document.getElementById(k + '_content').value = profiles[k].content;
                    if(k === 'about' && profiles[k].image) {
                        document.getElementById('about_preview').src = '/' + profiles[k].image;
                    }
                }
            });

            // Load Kontak (Table: contacts)
            // Sesuaikan key dengan yang diinput di database
            if(contacts.email) document.getElementById('con_email').value = contacts.email.value;
            if(contacts.phone) document.getElementById('con_phone').value = contacts.phone.value;
            if(contacts.whatsapp) document.getElementById('con_wa').value = contacts.whatsapp.value;
            if(contacts.instagram) document.getElementById('con_ig').value = contacts.instagram.value;
            if(contacts.address) document.getElementById('con_address').value = contacts.address.value;
        });
    }

    // Update data di tabel PROFILES (Termasuk Gambar)
    function updateProfileContent(event, key) {
        event.preventDefault();
        const btn = event.submitter;
        const title = document.getElementById(key + '_title').value;
        const content = document.getElementById(key + '_content').value;
        const imageFile = document.getElementById(key + '_image') ? document.getElementById(key + '_image').files[0] : null;

        const formData = new FormData();
        formData.append('key', key);
        formData.append('title', title);
        formData.append('content', content);
        if(imageFile) formData.append('image', imageFile);

        // Laravel PUT method spoofing untuk FormData
        formData.append('_method', 'PUT');

        btn.disabled = true;
        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        axios.post('/api/cms/profile', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        }).then(res => {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Konten diperbarui', timer: 1500, showConfirmButton: false });
            loadCmsData();
        }).catch(err => {
            Swal.fire('Error', err.response.data.message || 'Gagal update database', 'error');
        }).finally(() => {
            btn.disabled = false;
        });
    }

    // Update data di tabel CONTACTS (Bulk Update)
    async function updateContacts(event) {
        event.preventDefault();
        const btn = document.getElementById('btnUpdateContact');
        btn.disabled = true;

        const data = [
            { key: 'email', value: document.getElementById('con_email').value },
            { key: 'phone', value: document.getElementById('con_phone').value },
            { key: 'whatsapp', value: document.getElementById('con_wa').value },
            { key: 'instagram', value: document.getElementById('con_ig').value },
            { key: 'address', value: document.getElementById('con_address').value },
        ];

        try {
            Swal.fire({ title: 'Menyinkronkan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            // Loop update satu per satu ke API
            await Promise.all(data.map(item => axios.put('/api/cms/contact', item)));

            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Informasi kontak disinkronkan ke Landing Page.', timer: 1500, showConfirmButton: false });
            loadCmsData();
        } catch (err) {
            Swal.fire('Gagal', 'Terjadi masalah koneksi server', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    // Helper preview gambar lokal sebelum upload
    function previewImage(input, targetId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById(targetId).src = e.target.result;
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', loadCmsData);
</script>

<style>
    .bg-indigo { background-color: #5c6bc0 !important; }
    .text-indigo { color: #5c6bc0 !important; }
    .btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .nav-pills .nav-link.active { background-color: #5c6bc0; color: #fff; }
    .nav-pills .nav-link { color: #555; }
    .fs-xs { font-size: 0.7rem; }
    .form-control-feedback-start .form-control { padding-left: 2.75rem; }
    .form-control:focus { border-color: #5c6bc0; }
</style>
@endsection