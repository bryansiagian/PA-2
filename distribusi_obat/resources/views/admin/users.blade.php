@extends('layouts.backoffice')

@section('page_title', 'Audit Log Sistem')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-sm-center justify-content-sm-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Audit Logs Sistem</h4>
            <div class="text-muted small">Rekam jejak aktivitas digital seluruh staf dalam ekosistem E-Pharma.</div>
        </div>

        <div class="mt-3 mt-sm-0">
            <button onclick="fetchLogs()" class="btn btn-indigo rounded-pill px-4 shadow-sm fw-bold">
                <i class="ph-arrows-clockwise me-2"></i> Perbarui Log
            </button>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="ph-activity me-2 text-indigo"></i>Jejak Aktivitas Terakhir</h6>
            <div class="ms-auto text-muted small">
                Status: <span class="text-success fw-bold">Live Tracking Online</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3 py-3" style="width: 200px;">Waktu & Tanggal</th>
                        <th style="width: 250px;">Aktor / Pengguna</th>
                        <th>Aksi & Keterangan Aktivitas</th>
                    </tr>
                </thead>
                <tbody id="logTableBody">
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <div class="ph-spinner spinner text-indigo me-2"></div>
                            <span class="text-muted">Sedang menyingkronkan database log...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Header Token Global
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchLogs() {
        const tableBody = document.getElementById('logTableBody');

        axios.get('/api/admin/logs')
            .then(res => {
                let html = '';
                const logs = res.data;

                if (!logs || logs.length === 0) {
                    html = '<tr><td colspan="3" class="text-center py-5 text-muted italic small">Belum ada catatan aktivitas sistem yang terekam.</td></tr>';
                } else {
                    logs.forEach(log => {
                        // 1. Format Waktu khas Indonesia
                        const date = new Date(log.created_at);
                        const timeStr = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        const dateStr = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

                        // 2. Data User (Spatie Safe)
                        const user = log.user || { name: 'System/Deleted', roles: [] };
                        const roleName = user.roles.length > 0 ? user.roles[0].name : 'no-role';

                        // 3. Tema Aksi Berdasarkan Kata Kunci
                        const theme = getActionTheme(log.action);

                        html += `
                        <tr class="border-bottom">
                            <td class="ps-3">
                                <div class="fw-bold text-indigo small">${timeStr}</div>
                                <div class="text-muted" style="font-size: 11px;">${dateStr}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm fw-bold" style="width: 34px; height: 34px; font-size: 11px;">
                                        ${user.name.charAt(0)}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">${user.name}</div>
                                        <span class="badge bg-indigo bg-opacity-10 text-indigo rounded-pill" style="font-size: 9px; letter-spacing: 0.5px;">
                                            ${roleName.toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-start py-1">
                                    <div class="${theme.color} me-3 mt-1">
                                        <i class="ph-${theme.icon} ph-lg"></i>
                                    </div>
                                    <div class="small">
                                        <div class="${theme.bold ? 'fw-bold text-dark' : 'text-muted'}">${log.action}</div>
                                        <div class="text-muted fs-xs opacity-75">Transaction Source: API Engine</div>
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                tableBody.innerHTML = html;
            })
            .catch(err => {
                console.error("Audit Log Error:", err);
                tableBody.innerHTML = `<tr><td colspan="3" class="text-center py-5 text-danger small">Gagal memuat log. Periksa koneksi API Anda.</td></tr>`;
            });
    }

    // Helper untuk memetakan ikon Phosphor berdasarkan kata kunci aksi
    function getActionTheme(action) {
        const act = action.toUpperCase();

        // Aturan: { icon, color, bold }
        if(act.includes('LOGIN'))   return { icon: 'sign-in', color: 'text-info', bold: false };
        if(act.includes('LOGOUT'))  return { icon: 'sign-out', color: 'text-muted', bold: false };
        if(act.includes('APPROVE')) return { icon: 'check-circle', color: 'text-success', bold: true };
        if(act.includes('REJECT'))  return { icon: 'prohibit', color: 'text-danger', bold: true };
        if(act.includes('DELETE'))  return { icon: 'trash', color: 'text-danger', bold: true };
        if(act.includes('CANCEL'))  return { icon: 'x-circle', color: 'text-danger', bold: true };
        if(act.includes('CREATE') || act.includes('TAMBAH')) return { icon: 'plus-circle', color: 'text-primary', bold: true };
        if(act.includes('STOCK'))   return { icon: 'package', color: 'text-warning', bold: true };
        if(act.includes('CMS') || act.includes('PROFILE'))   return { icon: 'browser', color: 'text-indigo', bold: true };

        return { icon: 'note', color: 'text-dark', bold: false };
    }

    document.addEventListener('DOMContentLoaded', fetchLogs);
</script>

<style>
    /* Styling Tambahan Limitless */
    .bg-indigo { background-color: #5c6bc0 !important; }
    .text-indigo { color: #5c6bc0 !important; }
    .btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .fs-xs { font-size: 0.7rem; }

    /* Menyelaraskan padding sel tabel */
    .table td { padding: 0.85rem 1.25rem; vertical-align: top; }
    .table th { padding: 0.75rem 1.25rem; border-top: none; }

    /* Animasi Hover Baris */
    .table tbody tr { transition: background-color 0.2s; }
    .table tbody tr:hover { background-color: rgba(92, 107, 192, 0.03); }
</style>
@endsection