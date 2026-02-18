@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="/operator/requests" class="btn btn-light rounded-circle me-3 shadow-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold text-dark mb-0">Monitoring Pengiriman</h4>
            <p class="text-muted small mb-0">Pantau posisi kurir dan status pesanan secara real-time.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Panel Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Informasi Paket</h6>
                    <div class="mb-3">
                        <small class="text-muted d-block">No. Resi</small>
                        <span class="fw-bold text-primary fs-5" id="trackNum">-------</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Pemohon</small>
                        <span class="fw-bold" id="customerName">-</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Kurir Pengantar</small>
                        <div class="d-flex align-items-center mt-1">
                            <img id="courierImg" src="https://ui-avatars.com/api/?name=C&background=random" class="rounded-circle me-2" width="30">
                            <span class="fw-bold small" id="courierName">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
                <div class="card-body p-4 text-center">
                    <small class="text-white-50 d-block mb-1">STATUS TERKINI</small>
                    <h4 class="fw-bold mb-0 text-uppercase" id="currStatus">---</h4>
                </div>
            </div>
        </div>

        <!-- Foto -->
        <div id="operatorProofSection" class="card border-0 shadow-sm rounded-4 mt-4 d-none">
            <div class="card-body p-3">
                <small class="text-muted fw-bold d-block mb-2">FOTO BUKTI TERIMA</small>
                <img id="opProofImg" src="" class="img-fluid rounded-3 border" style="cursor: pointer;" onclick="window.open(this.src)">
            </div>
        </div>

        <!-- Panel Timeline -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold mb-4"><i class="bi bi-geo-fill me-2 text-danger"></i>Log Perjalanan Barang</h6>

                <div id="timelineContainer" class="ms-2">
                    <!-- Data Timeline dimuat via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Timeline (Konsisten dengan Customer tapi versi Backoffice) */
    #timelineContainer { border-left: 2px solid #e9ecef; padding-left: 30px; position: relative; }
    .timeline-node { position: relative; padding-bottom: 2rem; }
    .timeline-node::before {
        content: ''; position: absolute; left: -41px; top: 0;
        width: 20px; height: 20px; background: #fff;
        border: 4px solid #dee2e6; border-radius: 50%; z-index: 2;
    }
    .timeline-node.active::before { border-color: #0d6efd; background: #0d6efd; }
    .timeline-node:last-child { padding-bottom: 0; }
    .timeline-date { font-size: 11px; font-weight: bold; color: #adb5bd; }
    .timeline-title { font-weight: bold; color: #2d3436; margin-top: 2px; font-size: 0.95rem; }
    .timeline-desc { font-size: 13px; color: #636e72; }
</style>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchTracking() {
        const deliveryId = "{{ $id }}";
        axios.get(`/api/deliveries/${deliveryId}/tracking`)
            .then(res => {
                const d = res.data;

                document.getElementById('trackNum').innerText = d.tracking_number;
                document.getElementById('customerName').innerText = d.request.user.name;
                document.getElementById('courierName').innerText = d.courier ? d.courier.name : 'Belum Diambil';
                document.getElementById('currStatus').innerText = d.status.replace('_', ' ');

                if (d.status === 'delivered' && d.proof_image_url) {
                    document.getElementById('operatorProofSection').classList.remove('d-none');
                    document.getElementById('opProofImg').src = d.proof_image_url;

                    document.getElementById('currStatus').innerText = "SELESAI";
                    document.getElementById('currStatus').parentElement.classList.replace('bg-primary', 'bg-success');
                }

                if(d.courier) {
                    document.getElementById('courierImg').src = `https://ui-avatars.com/api/?name=${d.courier.name}&background=0D6EFD&color=fff`;
                }

                let html = '';
                if(d.trackings && d.trackings.length > 0) {
                    d.trackings.forEach((t, index) => {
                        const date = new Date(t.created_at).toLocaleString('id-ID', {
                            day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
                        });
                        const isActive = index === 0 ? 'active' : '';

                        html += `
                        <div class="timeline-node ${isActive}">
                            <div class="timeline-date text-uppercase">${date}</div>
                            <div class="timeline-title">${t.location}</div>
                            <div class="timeline-desc">${t.description}</div>
                        </div>`;
                    });
                } else {
                    html = '<p class="text-muted text-center py-4">Belum ada riwayat tercatat.</p>';
                }
                document.getElementById('timelineContainer').innerHTML = html;
            })
            .catch(err => {
                Swal.fire('Error', 'Gagal memuat detail pelacakan', 'error');
            });
    }

    document.addEventListener('DOMContentLoaded', fetchTracking);
</script>
@endsection