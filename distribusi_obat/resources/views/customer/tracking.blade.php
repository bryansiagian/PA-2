@extends('layouts.portal')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="/customer/history" class="btn btn-light rounded-circle me-3"><i class="bi bi-arrow-left"></i></a>
                <h3 class="fw-bold m-0">Lacak Pengiriman</h3>
            </div>

            <!-- Card Info Pengiriman -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">No. Resi</small>
                            <h5 class="fw-bold text-primary mb-0" id="trackNum">-------</h5>
                        </div>
                        <div class="col-sm-6 border-start-sm">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Status Terkini</small>
                            <span id="badgeStatus" class="badge rounded-pill px-3 py-2">Memuat...</span>
                        </div>
                    </div>
                </div>
                <div class="bg-light p-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-badge me-2 text-primary"></i>
                        <small id="courierName" class="fw-bold text-dark">Kurir: -</small>
                    </div>
                    <small id="lastUpdate" class="text-muted small"></small>
                </div>
            </div>

            <!-- ID untuk kontainer foto -->
            <div id="proofSection" class="card border-0 shadow-sm rounded-4 mb-4 d-none">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-camera-fill me-2 text-success"></i>Bukti Penerimaan</h6>
                    <div class="text-center bg-light rounded-4 p-2">
                        <img id="proofImg" src="" alt="Bukti Foto" class="img-fluid rounded-4 shadow-sm" style="max-height: 300px; cursor: zoom-in;" onclick="window.open(this.src)">
                        <p class="text-muted small mt-2 mb-0">Klik foto untuk memperbesar</p>
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-clock-history me-2"></i>Riwayat Perjalanan Paket</h6>

                <div id="timelineContainer" class="ms-2">
                    <!-- Data Timeline Muncul Di Sini -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Timeline Modern */
    #timelineContainer {
        border-left: 2px dashed #dee2e6;
        padding-left: 30px;
        position: relative;
    }
    .timeline-node {
        position: relative;
        padding-bottom: 2.5rem;
    }
    .timeline-node::before {
        content: '';
        position: absolute;
        left: -41px;
        top: 0;
        width: 20px;
        height: 20px;
        background: #fff;
        border: 4px solid #dee2e6;
        border-radius: 50%;
        z-index: 2;
    }
    .timeline-node.active::before {
        border-color: #0d6efd;
        background: #0d6efd;
        box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.15);
    }
    .timeline-node:last-child { padding-bottom: 0; }
    .timeline-date { font-size: 11px; font-weight: 700; color: #adb5bd; text-uppercase: true; }
    .timeline-title { font-weight: 700; color: #2d3436; margin-top: 2px; }
    .timeline-desc { font-size: 13px; color: #636e72; }
</style>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchTracking() {
        const id = "{{ $id }}";
        axios.get(`/api/deliveries/${id}/tracking`)
            .then(res => {
                const d = res.data;

                // Update Info Card
                document.getElementById('trackNum').innerText = d.tracking_number;
                document.getElementById('courierName').innerText = `Kurir: ${d.courier ? d.courier.name : 'Mencari Kurir...'}`;

                // TAMPILKAN FOTO JIKA SUDAH DELIVERED
                if (d.status === 'delivered' && d.proof_image_url) {
                    document.getElementById('proofSection').classList.remove('d-none');
                    document.getElementById('proofImg').src = d.proof_image_url;

                    // Ubah badge jadi Sukses
                    const badge = document.getElementById('badgeStatus');
                    badge.className = "badge bg-success rounded-pill px-3 py-2";
                    badge.innerText = "DITERIMA";
                }

                // Update Badge Status
                const badge = document.getElementById('badgeStatus');
                badge.innerText = d.status.toUpperCase().replace('_', ' ');
                badge.className = `badge rounded-pill px-3 py-2 ${d.status === 'delivered' ? 'bg-success' : 'bg-primary'}`;

                // Render Timeline
                let html = '';
                if(d.trackings && d.trackings.length > 0) {
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
                }
                document.getElementById('timelineContainer').innerHTML = html;
            });
    }

    document.addEventListener('DOMContentLoaded', fetchTracking);
</script>
@endsection