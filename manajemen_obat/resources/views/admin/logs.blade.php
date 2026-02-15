@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark">Audit Logs</h4>
            <p class="text-muted small">Rekam jejak aktivitas seluruh staf di dalam sistem.</p>
        </div>
        <button onclick="fetchLogs()" class="btn btn-white border shadow-sm rounded-pill px-3">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Log
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold">WAKTU</th>
                            <th class="text-muted small fw-bold">AKTOR</th>
                            <th class="text-muted small fw-bold">ROLE</th>
                            <th class="text-muted small fw-bold">AKTIVITAS / AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="spinner-border spinner-border-sm text-primary"></div> Memuat catatan aktivitas...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function fetchLogs() {
        axios.get('/api/admin/logs')
            .then(res => {
                let html = '';
                const logs = res.data;

                if (logs.length === 0) {
                    html = '<tr><td colspan="4" class="text-center py-5 text-muted">Belum ada aktivitas tercatat.</td></tr>';
                } else {
                    logs.forEach(log => {
                        // Format Tanggal dan Waktu
                        const date = new Date(log.created_at);
                        const timeStr = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        const dateStr = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });

                        // Pilih warna badge berdasarkan kata kunci aksi
                        let actionClass = 'text-dark';
                        if(log.action.includes('Approve') || log.action.includes('Tambah')) actionClass = 'text-success fw-bold';
                        if(log.action.includes('Hapus') || log.action.includes('Tolak')) actionClass = 'text-danger fw-bold';

                        html += `
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">${timeStr}</div>
                                <small class="text-muted">${dateStr}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=${log.user.name}&background=random" class="rounded-circle me-2" width="24">
                                    <span class="small fw-bold">${log.user.name}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border rounded-pill" style="font-size: 10px;">
                                    ${log.user.role.name.toUpperCase()}
                                </span>
                            </td>
                            <td class="${actionClass} small">
                                ${log.action}
                            </td>
                        </tr>`;
                    });
                }
                document.getElementById('logTableBody').innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('logTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-5 text-danger">Gagal memuat data log.</td></tr>';
            });
    }

    document.addEventListener('DOMContentLoaded', fetchLogs);
</script>

<style>
    .table thead th {
        letter-spacing: 0.5px;
        font-size: 11px;
    }
    .table tbody tr {
        transition: 0.2s;
    }
    .table tbody tr:hover {
        background-color: #fcfdfe;
    }
</style>
@endsection