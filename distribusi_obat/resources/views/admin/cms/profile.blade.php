@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-dark">Kelola Profil & Informasi</h4>
        <p class="text-muted small">Update informasi dasar yayasan yang tampil di Landing Page utama.</p>
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
                    <button class="nav-link w-100 fw-bold" data-bs-toggle="pill" data-bs-target="#tab-contact">Kontak Yayasan</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="pills-tabContent">
                <!-- FORM ABOUT -->
                <div class="tab-pane fade show active" id="tab-about">
                    <form onsubmit="updateContent(event, 'about')">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Judul Tentang Kami</label>
                            <input type="text" id="about_title" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Isi Konten</label>
                            <textarea id="about_content" class="form-control border-0 bg-light" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                    </form>
                </div>

                <!-- FORM HISTORY -->
                <div class="tab-pane fade" id="tab-history">
                    <form onsubmit="updateContent(event, 'history')">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Judul Sejarah</label>
                            <input type="text" id="history_title" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Isi Konten Sejarah</label>
                            <textarea id="history_content" class="form-control border-0 bg-light" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Sejarah</button>
                    </form>
                </div>

                <!-- FORM VISION MISSION -->
                <div class="tab-pane fade" id="tab-vision">
                    <form onsubmit="updateContent(event, 'vision_mission')">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Judul Visi & Misi</label>
                            <input type="text" id="vision_mission_title" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Isi Konten (Gunakan HTML untuk list jika perlu)</label>
                            <textarea id="vision_mission_content" class="form-control border-0 bg-light" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Visi Misi</button>
                    </form>
                </div>

                <!-- FORM CONTACT -->
                <div class="tab-pane fade" id="tab-contact">
                    <form onsubmit="updateContact(event)">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold">Email Resmi</label>
                                <input type="email" id="con_email" class="form-control border-0 bg-light" placeholder="email@yayasan.org" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold">Nomor Telepon</label>
                                <input type="text" id="con_phone" class="form-control border-0 bg-light" placeholder="(021) xxxxx" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold">WhatsApp Bisnis</label>
                                <input type="text" id="con_wa" class="form-control border-0 bg-light" placeholder="0812xxxx" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="small fw-bold">Alamat Kantor</label>
                                <textarea id="con_address" class="form-control border-0 bg-light" rows="3" required></textarea>
                            </div>
                        </div>
                        <button type="submit" id="btnUpdateContact" class="btn btn-primary rounded-pill px-4 shadow-sm">Update Kontak</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Header token diambil dari session via layout backoffice
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function loadProfiles() {
        axios.get('/api/public/landing-page').then(res => {
            const p = res.data.profiles;

            // Map About, History, Vision
            if(p.about) {
                document.getElementById('about_title').value = p.about.title;
                document.getElementById('about_content').value = p.about.content;
            }
            if(p.history) {
                document.getElementById('history_title').value = p.history.title;
                document.getElementById('history_content').value = p.history.content;
            }
            if(p.vision_mission) {
                document.getElementById('vision_mission_title').value = p.vision_mission.title;
                document.getElementById('vision_mission_content').value = p.vision_mission.content;
            }

            // Map Contacts
            if(p.contact_email) document.getElementById('con_email').value = p.contact_email.content;
            if(p.contact_phone) document.getElementById('con_phone').value = p.contact_phone.content;
            if(p.contact_wa) document.getElementById('con_wa').value = p.contact_wa.content;
            if(p.contact_address) document.getElementById('con_address').value = p.contact_address.content;
        });
    }

    function updateContent(event, key) {
        event.preventDefault();
        const title = document.getElementById(key + '_title').value;
        const content = document.getElementById(key + '_content').value;

        axios.put('/api/cms/profile', { key, title, content })
            .then(res => {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Konten berhasil diperbarui', timer: 1500, showConfirmButton: false });
            })
            .catch(err => Swal.fire('Gagal', 'Terjadi kesalahan server', 'error'));
    }

    async function updateContact(event) {
        event.preventDefault();
        const btn = document.getElementById('btnUpdateContact');
        btn.disabled = true;

        const contactData = [
            { key: 'contact_email', title: 'Email', content: document.getElementById('con_email').value },
            { key: 'contact_phone', title: 'Phone', content: document.getElementById('con_phone').value },
            { key: 'contact_wa', title: 'WhatsApp', content: document.getElementById('con_wa').value },
            { key: 'contact_address', title: 'Address', content: document.getElementById('con_address').value },
        ];

        try {
            // Melakukan update massal untuk data kontak
            await Promise.all(contactData.map(item =>
                axios.put('/api/cms/profile', item)
            ));
            Swal.fire({ icon: 'success', title: 'Kontak Diperbarui', text: 'Data kontak Landing Page telah sinkron.', timer: 1500, showConfirmButton: false });
        } catch (error) {
            Swal.fire('Gagal', 'Beberapa data gagal diperbarui', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', loadProfiles);
</script>
@endsection