@extends('layouts.portal')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <!-- HEADER & TOMBOL KEMBALI -->
            <div class="d-flex align-items-center mb-4">
                <a href="/customer/history" class="btn btn-outline-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold m-0" style="color: #2c4964;">Detail Pelacakan Paket</h3>
            </div>

            <!-- CARD 1: INFO UTAMA RESI -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="border-top: 5px solid #3fbbc0 !important;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <small class="text-uppercase fw-bold text-muted" style="font-size: 11px; letter-spacing: 1px;">Nomor Resi Pengiriman</small>
                            <h4 class="fw-bold m-0" style="color: #3fbbc0;" id="trackNum">-------</h4>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small class="text-muted d-block mb-1 small text-uppercase">Status Terkini</small>
                            <span id="badgeStatus" class="badge rounded-pill px-4 py-2 shadow-sm fs-6">Memuat...</span>
                        </div>
                    </div>
                </div>
                <div class="bg-light p-3 px-4 d-flex justify-content-between align-items-center border-top">
                    <div class="small fw-bold text-dark">
                        <i class="bi bi-person-badge me-2 text-accent"></i> <span id="courierName">Kurir: -</span>
                    </div>
                    <div class="small text-muted" id="lastUpdate">Update: -</div>
                </div>
            </div>

            <!-- CARD 2: BUKTI FOTO (Hanya muncul jika Delivered) -->
            <div id="proofSection" class="card border-0 shadow-sm rounded-4 mb-4 d-none">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold mb-3 text-start"><i class="bi bi-camera-fill me-2 text-success"></i>Konfirmasi Foto Penerimaan</h6>
                    <div class="bg-light p-2 rounded-4 d-inline-block">
                        <img id="proofImg" src="" class="img-fluid rounded-4 shadow-sm" style="max-height: 350px; cursor: zoom-in;" onclick="window.open(this.src)">
                    </div>
                    <p class="text-muted small mt-3 mb-0 italic">Paket telah diterima dengan sukses di lokasi tujuan.</p>
                </div>
            </div>

            <!-- CARD 3: TIMELINE PERJALANAN -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <h5 class="fw-bold mb-4" style="color: #2c4964;">
                    <i class="bi bi-clock-history me-2 text-accent"></i>Riwayat Perjalanan
                </h5>

                <!-- CONTAINER TIMELINE -->
                <div id="timelineContainer" class="ms-2">
                    <div class="text-center py-5">
                        <div class="spinner-border text-accent" role="status"></div>
                        <p class="mt-2 text-muted">Menghubungkan ke satelit logistik...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Timeline Medinest */
    #timelineContainer {
        border-left: 2px solid #e0ebec;
        padding-left: 35px;
        position: relative;
    }
    .timeline-node { position: relative; padding-bottom: 2.5rem; }
    .timeline-node::before {
        content: ''; position: absolute; left: -46px; top: 0;
        width: 20px; height: 20px; background: #fff;
        border: 4px solid #dee2e6; border-radius: 50%; z-index: 2;
    }
    .timeline-node.active::before {
        border-color: #3fbbc0; background: #3fbbc0;
        box-shadow: 0 0 0 6px rgba(63, 187, 192, 0.1);
    }
    .timeline-node:last-child { padding-bottom: 0; }
    .timeline-date { font-size: 11px; font-weight: 700; color: #9eb5b6; text-transform: uppercase; }
    .timeline-title { font-weight: 700; color: #2c4964; margin-top: 2px; }
    .timeline-desc { font-size: 13px; color: #6c757d; line-height: 1.5; }
    .text-accent { color: #3fbbc0 !important; }
    .italic { font-style: italic; }
</style>

<script>
    // PENTING: Gunakan API Token dari session
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchTracking() {
        const id = "{{ $id }}"; // Diambil dari rute web
        console.log("Fetching tracking for ID:", id);

        axios.get(`/api/deliveries/${id}/tracking`)
            .then(res => {
                const d = res.data;
                console.log("Data diterima:", d);

                // 1. Update Info Card
                document.getElementById('trackNum').innerText = d.tracking_number || 'TRK-UNKNOWN';
                document.getElementById('courierName').innerText = `Kurir: ${d.courier ? d.courier.name : 'Mencari Kurir...'}`;
                document.getElementById('lastUpdate').innerText = `Update: ${new Date(d.updated_at).toLocaleTimeString('id-ID')} WIB`;

                // 2. Handle Foto Bukti
                const proofSection = document.getElementById('proofSection');
                if (d.status === 'delivered' && d.proof_image_url) {
                    proofSection.classList.remove('d-none');
                    document.getElementById('proofImg').src = d.proof_image_url;
                }

                // 3. Update Badge Status
                const badge = document.getElementById('badgeStatus');
                const statusStr = d.status.toUpperCase().replace('_', ' ');
                badge.innerText = statusStr;

                if(d.status === 'delivered') {
                    badge.className = "badge bg-success rounded-pill px-4 py-2";
                } else if(d.status === 'in_transit') {
                    badge.className = "badge bg-info text-white rounded-pill px-4 py-2";
                } else {
                    badge.className = "badge bg-warning text-dark rounded-pill px-4 py-2";
                }

                // 4. Render Timeline
                let html = '';
                if (d.trackings && d.trackings.length > 0) {
                    d.trackings.forEach((t, index) => {
                        const date = new Date(t.created_at).toLocaleString('id-ID', {
                            day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
                        });
                        const isActive = index === 0 ? 'active' : '';

                        html += `
                        <div class="timeline-node ${isActive}">
                            <div class="timeline-date">${date}</div>
                            <div class="timeline-title">${t.location}</div>
                            <div class="timeline-desc">${t.description}</div>
                        </div>`;
                    });
                } else {
                    html = '<div class="text-center py-4 text-muted small italic">Belum ada riwayat pergerakan paket.</div>';
                }
                document.getElementById('timelineContainer').innerHTML = html;

            })
            .catch(err => {
                console.error("Gagal memuat tracking:", err);
                document.getElementById('timelineContainer').innerHTML = `
                    <div class="alert alert-danger border-0 shadow-sm">
                        Gagal mengambil data dari server. Pastikan rute API sudah benar.
                    </div>`;
            });
    }

    // Jalankan saat halaman dimuat
    document.addEventListener('DOMContentLoaded', fetchTracking);
</script>
@endsection