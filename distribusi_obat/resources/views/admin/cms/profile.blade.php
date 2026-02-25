@extends('layouts.backoffice')

@section('page_title', 'Konten Statis & Profil')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Manajemen Konten Statis</h4>
            <div class="text-muted small">Kelola narasi profil yayasan dan data kontak resmi untuk Landing Page publik.</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-browser me-2 text-indigo"></i>Editor Konten Website</h6>
                </div>

                <div class="card-body p-4">
                    <!-- Nav Pills (Limitless Style) -->
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
                        <li class="nav-item ms-1">
                            <button class="nav-link rounded-pill fw-bold" data-bs-toggle="pill" data-bs-target="#tab-contact">
                                <i class="ph-phone-call me-2"></i>Kontak & Sosmed
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="pills-tabContent">

                        <!-- TAB: ABOUT -->
                        <div class="tab-pane fade show active" id="tab-about">
                            <form onsubmit="updateProfileContent(event, 'about')">
                                <div class="mb-3">
                                    <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Judul Seksi</label>
                                    <input type="text" id="about_title" class="form-control border-0 bg-light py-2" placeholder="Judul yang tampil di Landing Page..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Konten Deskripsi</label>
                                    <textarea id="about_content" class="form-control border-0 bg-light py-2" rows="8" placeholder="Jelaskan mengenai yayasan dan sistem ini secara mendetail..." required></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-indigo rounded-pill px-4 shadow-sm fw-bold">
                                        <i class="ph-check-circle me-2"></i>Update Tentang Kami
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TAB: HISTORY -->
                        <div class="tab-pane fade" id="tab-history">
                            <form onsubmit="updateProfileContent(event, 'history')">
                                <div class="mb-3">
                                    <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Judul Seksi Sejarah</label>
                                    <input type="text" id="history_title" class="form-control border-0 bg-light py-2" required>
                                </div>
                                <div class="mb-3">
                                    <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Isi Sejarah Lengkap</label>
                                    <textarea id="history_content" class="form-control border-0 bg-light py-2" rows="8" required></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-indigo rounded-pill px-4 shadow-sm fw-bold">
                                        <i class="ph-check-circle me-2"></i>Update Sejarah
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TAB: VISION MISSION -->
                        <div class="tab-pane fade" id="tab-vision">
                            <form onsubmit="updateProfileContent(event, 'vision_mission')">
                                <div class="mb-3">
                                    <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Judul Visi & Misi</label>
                                    <input type="text" id="vision_mission_title" class="form-control border-0 bg-light py-2" required>
                                </div>
                                <div class="mb-3">
                                    <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Konten (Mendukung format HTML list)</label>
                                    <textarea id="vision_mission_content" class="form-control border-0 bg-light py-2" rows="8" placeholder="Contoh: <ul><li>Misi 1</li></ul>" required></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-indigo rounded-pill px-4 shadow-sm fw-bold">
                                        <i class="ph-check-circle me-2"></i>Update Visi Misi
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TAB: CONTACT (Sync with 'contacts' table) -->
                        <div class="tab-pane fade" id="tab-contact">
                            <form onsubmit="updateContacts(event)">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Email Resmi</label>
                                        <div class="form-control-feedback form-control-feedback-start">
                                            <input type="email" id="con_email" class="form-control border-0 bg-light py-2" placeholder="info@e-pharma.org" required>
                                            <div class="form-control-feedback-icon"><i class="ph-envelope"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fs-xs fw-bold text-uppercase text-muted mb-1">No. Telepon / Hotline</label>
                                        <div class="form-control-feedback form-control-feedback-start">
                                            <input type="text" id="con_phone" class="form-control border-0 bg-light py-2" placeholder="021-xxxx" required>
                                            <div class="form-control-feedback-icon"><i class="ph-phone"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fs-xs fw-bold text-uppercase text-muted mb-1">WhatsApp Bisnis</label>
                                        <div class="form-control-feedback form-control-feedback-start">
                                            <input type="text" id="con_wa" class="form-control border-0 bg-light py-2" placeholder="0812xxxx" required>
                                            <div class="form-control-feedback-icon"><i class="ph-whatsapp-logo text-success"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Username Instagram</label>
                                        <div class="form-control-feedback form-control-feedback-start">
                                            <input type="text" id="con_ig" class="form-control border-0 bg-light py-2" placeholder="@yayasan_logistik">
                                            <div class="form-control-feedback-icon"><i class="ph-instagram-logo text-pink"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Alamat Fisik Kantor Pusat</label>
                                        <textarea id="con_address" class="form-control border-0 bg-light py-2" rows="3" placeholder="Jl. Alamat Lengkap No. X..." required></textarea>
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" id="btnUpdateContact" class="btn btn-indigo rounded-pill px-4 shadow-sm fw-bold">
                                        <i class="ph-cloud-arrow-up me-2"></i>Update Informasi Kontak
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Axios global config (Token sudah di-set di master layout)

    function loadCmsData() {
        axios.get('/api/public/landing-page').then(res => {
            const profiles = res.data.profiles;
            const contacts = res.data.contacts;

            // Load Profil Text
            if(profiles.about) {
                document.getElementById('about_title').value = profiles.about.title;
                document.getElementById('about_content').value = profiles.about.content;
            }
            if(profiles.history) {
                document.getElementById('history_title').value = profiles.history.title;
                document.getElementById('history_content').value = profiles.history.content;
            }
            if(profiles.vision_mission) {
                document.getElementById('vision_mission_title').value = profiles.vision_mission.title;
                document.getElementById('vision_mission_content').value = profiles.vision_mission.content;
            }

            // Load Contacts via Key
            if(contacts.email) document.getElementById('con_email').value = contacts.email.value;
            if(contacts.phone) document.getElementById('con_phone').value = contacts.phone.value;
            if(contacts.whatsapp) document.getElementById('con_wa').value = contacts.whatsapp.value;
            if(contacts.instagram) document.getElementById('con_ig').value = contacts.instagram.value;
            if(contacts.address) document.getElementById('con_address').value = contacts.address.value;
        });
    }

    // Single Update for Profiles Table
    function updateProfileContent(event, key) {
        event.preventDefault();
        const btn = event.submitter;
        const title = document.getElementById(key + '_title').value;
        const content = document.getElementById(key + '_content').value;

        btn.disabled = true;
        axios.put('/api/cms/profile', { key, title, content })
            .then(res => {
                Swal.fire({ icon: 'success', title: 'Sinkron Berhasil', text: 'Konten ' + key + ' telah diperbarui.', timer: 2000, showConfirmButton: false });
                loadCmsData();
            })
            .catch(() => Swal.fire('Error', 'Gagal memperbarui database.', 'error'))
            .finally(() => btn.disabled = false);
    }

    // Bulk Update for Contacts Table
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

            // PENTING: Menjalankan semua update secara paralel
            await Promise.all(data.map(item => axios.put('/api/cms/contact', item)));

            Swal.fire({ icon: 'success', title: 'Tersimpan!', text: 'Informasi kontak berhasil diperbarui.', timer: 1500, showConfirmButton: false });
            loadCmsData();
        } catch (err) {
            Swal.fire('Gagal', 'Beberapa data gagal diperbarui ke server.', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', loadCmsData);
</script>

<style>
    /* Custom Styling for Limitless Integration */
    .bg-indigo { background-color: #5c6bc0 !important; }
    .text-indigo { color: #5c6bc0 !important; }
    .btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .nav-pills .nav-link.active { background-color: #5c6bc0; color: #fff; }
    .nav-pills .nav-link { color: #555; }
    .fs-xs { font-size: 0.7rem; }
    .form-control-feedback-start .form-control { padding-left: 2.75rem; }
</style>
@endsection