@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-dark">Manajemen Konten Statis</h4>
        <p class="text-muted small">Update informasi profil yayasan dan data kontak resmi untuk Landing Page.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Nav Tabs -->
            <ul class="nav nav-pills mb-4 bg-light p-2 rounded-3" id="pills-tab" role="tablist">
                <li class="nav-item flex-fill">
                    <button class="nav-link active w-100 fw-bold" data-bs-toggle="pill" data-bs-target="#tab-about">Tentang Kami</button>
                </li>
                <li class="nav-item flex-fill">
                    <button class="nav-link w-100 fw-bold" data-bs-toggle="pill" data-bs-target="#tab-history">Sejarah</button>
                </li>
                <li class="nav-item flex-fill">
                    <button class="nav-link w-100 fw-bold" data-bs-toggle="pill" data-bs-target="#tab-vision">Visi & Misi</button>
                </li>
                <li class="nav-item flex-fill">
                    <button class="nav-link w-100 fw-bold" data-bs-toggle="pill" data-bs-target="#tab-contact">Kontak & Sosmed</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="pills-tabContent">
                <!-- FORM ABOUT -->
                <div class="tab-pane fade show active" id="tab-about">
                    <form onsubmit="updateProfileContent(event, 'about')">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Judul Seksi</label>
                            <input type="text" id="about_title" class="form-control border-0 bg-light py-2" placeholder="Judul yang tampil..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Konten Deskripsi</label>
                            <textarea id="about_content" class="form-control border-0 bg-light py-2" rows="6" placeholder="Jelaskan mengenai yayasan..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Update Tentang Kami</button>
                        </div>
                    </form>
                </div>

                <!-- FORM HISTORY -->
                <div class="tab-pane fade" id="tab-history">
                    <form onsubmit="updateProfileContent(event, 'history')">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Judul Seksi Sejarah</label>
                            <input type="text" id="history_title" class="form-control border-0 bg-light py-2" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Isi Sejarah Lengkap</label>
                            <textarea id="history_content" class="form-control border-0 bg-light py-2" rows="6" required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Update Sejarah</button>
                        </div>
                    </form>
                </div>

                <!-- FORM VISION MISSION -->
                <div class="tab-pane fade" id="tab-vision">
                    <form onsubmit="updateProfileContent(event, 'vision_mission')">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Judul Visi & Misi</label>
                            <input type="text" id="vision_mission_title" class="form-control border-0 bg-light py-2" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Konten (Dukung format HTML)</label>
                            <textarea id="vision_mission_content" class="form-control border-0 bg-light py-2" rows="6" placeholder="<ul><li>Misi 1</li></ul>" required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Update Visi Misi</button>
                        </div>
                    </form>
                </div>

                <!-- FORM CONTACT (Menyesuaikan tabel contacts di DBML) -->
                <div class="tab-pane fade" id="tab-contact">
                    <form onsubmit="updateContacts(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Email Kantor</label>
                                <input type="email" id="con_email" class="form-control border-0 bg-light py-2" placeholder="info@yayasan.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">No. Telepon / Hotline</label>
                                <input type="text" id="con_phone" class="form-control border-0 bg-light py-2" placeholder="021-xxxx" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">WhatsApp Bisnis</label>
                                <input type="text" id="con_wa" class="form-control border-0 bg-light py-2" placeholder="0812xxxx" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Username Instagram</label>
                                <input type="text" id="con_ig" class="form-control border-0 bg-light py-2" placeholder="@yayasan_ig">
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold text-muted">Alamat Fisik</label>
                                <textarea id="con_address" class="form-control border-0 bg-light py-2" rows="3" placeholder="Alamat lengkap kantor pusat..." required></textarea>
                            </div>
                        </div>
                        <div class="text-end mt-4">
                            <button type="submit" id="btnUpdateContact" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Update Informasi Kontak</button>
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

            // Load Kontak (Table: contacts)
            if(contacts.email) document.getElementById('con_email').value = contacts.email.value;
            if(contacts.phone) document.getElementById('con_phone').value = contacts.phone.value;
            if(contacts.whatsapp) document.getElementById('con_wa').value = contacts.whatsapp.value;
            if(contacts.instagram) document.getElementById('con_ig').value = contacts.instagram.value;
            if(contacts.address) document.getElementById('con_address').value = contacts.address.value;
        });
    }

    // Update data di tabel PROFILES
    function updateProfileContent(event, key) {
        event.preventDefault();
        const title = document.getElementById(key + '_title').value;
        const content = document.getElementById(key + '_content').value;

        axios.put('/api/cms/profile', { key, title, content })
            .then(res => {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Profil diperbarui', timer: 1500, showConfirmButton: false });
            })
            .catch(() => Swal.fire('Error', 'Gagal update database', 'error'));
    }

    // Update data di tabel CONTACTS (Multi-update)
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
            // Kita gunakan endpoint updateContact satu per satu atau buat bulk di backend
            await Promise.all(data.map(item => axios.put('/api/cms/contact', item)));
            Swal.fire({ icon: 'success', title: 'Sinkron Berhasil', text: 'Data kontak Landing Page telah diperbarui.', timer: 1500, showConfirmButton: false });
        } catch (err) {
            Swal.fire('Gagal', 'Beberapa data gagal diperbarui', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', loadCmsData);
</script>
@endsection