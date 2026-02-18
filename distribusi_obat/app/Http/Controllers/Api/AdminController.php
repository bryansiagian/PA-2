<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Models\DrugRequest;
use App\Models\DrugRequestItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Ambil daftar user Aktif (Status 1) selain Admin.
     */
    public function getUsers() {
        try {
            $users = User::with('roles')
                ->where('status', 1)
                ->whereHas('roles', function($query) {
                    $query->where('name', '!=', 'admin');
                })
                ->latest()
                ->get();

            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data user'], 500);
        }
    }

    /**
     * Ambil daftar user yang baru mendaftar (Status 0).
     */
    public function getPendingUsers() {
        try {
            return User::with('roles', 'courierDetail')
                ->where('status', 0)
                ->latest()
                ->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data antrian'], 500);
        }
    }

    /**
     * Tampilkan detail satu user.
     */
    public function showUser($id) {
        return User::with('role')->findOrFail($id);
    }

    /**
     * Tambah User Baru secara manual oleh Admin.
     */
    public function storeUser(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
        ]);

        try {
            return DB::transaction(function() use ($request) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id'  => $request->role_id,
                    'status'   => 1 // Otomatis aktif jika dibuat oleh Admin
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "CREATE USER: Admin membuat akun baru untuk {$user->name} ({$user->email})"
                ]);

                return response()->json(['message' => 'User berhasil dibuat', 'data' => $user], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan user'], 500);
        }
    }

    /**
     * Menyetujui pendaftaran akun baru.
     */
    public function approveUser($id) {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 1]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "APPROVE USER: Menyetujui pendaftaran akun {$user->name}"
            ]);

            return response()->json(['message' => 'Pendaftaran akun telah disetujui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses persetujuan'], 500);
        }
    }

    /**
     * Menolak pendaftaran akun baru.
     */
    public function rejectUser($id) {
        try {
            $user = User::findOrFail($id);
            // Mengubah status menjadi 2 (Rejected) agar user tahu mereka ditolak
            $user->update(['status' => 2]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "REJECT USER: Menolak pendaftaran akun {$user->name}"
            ]);

            return response()->json(['message' => 'Pendaftaran akun telah ditolak']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses penolakan'], 500);
        }
    }

    /**
     * Update Data/Role User.
     */
    public function updateUser(Request $request, $id) {
        $user = User::findOrFail($id);

        // Cari nama role berdasarkan ID yang dikirim dari modal
        $role = Role::findById($request->role_id);

        // CARA SPATIE: hapus role lama, ganti yang baru
        $user->syncRoles([$role->name]);

        return response()->json(['message' => 'Success']);
    }

    /**
     * Hapus User secara permanen.
     */
    public function destroyUser($id) {
        try {
            $user = User::findOrFail($id);
            $userName = $user->name;

            // Proteksi: Jangan biarkan admin menghapus dirinya sendiri
            if ($user->id === auth()->id()) {
                return response()->json(['message' => 'Anda tidak bisa menghapus akun Anda sendiri'], 403);
            }

            $user->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "DELETE USER: Menghapus akun {$userName} secara permanen"
            ]);

            return response()->json(['message' => 'User berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus user'], 500);
        }
    }

    /**
     * Ambil daftar audit logs terbaru.
     */
    public function getLogs() {
        try {
            // Kita muat user dan roles (jamak - standar Spatie)
            $logs = \App\Models\AuditLog::with(['user.roles'])->latest()->get();
            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil log: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ambil daftar Role selain Admin.
     */
    public function getRoles() {
        return Role::where('name', '!=', 'admin')->get();
    }

    public function getAnalytics(Request $request) {
        try {
            $period = $request->query('period', 'daily');

            // 1. Hitung Total Pesanan Selesai
            $totalCompleted = DrugRequest::where('status', 'completed')->count();

            // 2. Hitung Total Obat Terdistribusi (Menggunakan JOIN agar lebih akurat)
            // Kita menjumlahkan quantity dari tabel items yang terhubung ke request berstatus completed
            $totalItems = DB::table('drug_request_items')
                ->join('drug_requests', 'drug_request_items.drug_request_id', '=', 'drug_requests.id')
                ->where('drug_requests.status', 'completed')
                ->sum('drug_request_items.quantity');

            // 3. Statistik Grafik (Sesuai Periode)
            $query = DrugRequest::where('status', 'completed');
            if ($period == 'daily') {
                $stats = $query->select(
                    DB::raw('DATE_FORMAT(updated_at, "%d %b") as label'),
                    DB::raw('COUNT(*) as total_requests')
                )
                ->where('updated_at', '>=', now()->subDays(7))
                ->groupBy('label')
                ->orderBy('updated_at', 'ASC')
                ->get();
            } else {
                $stats = $query->select(
                    DB::raw('DATE_FORMAT(updated_at, "%b %Y") as label'),
                    DB::raw('COUNT(*) as total_requests')
                )
                ->where('updated_at', '>=', now()->subMonths(6))
                ->groupBy('label')
                ->orderBy('updated_at', 'ASC')
                ->get();
            }

            // 4. Top 5 Obat
            $topDrugs = DB::table('drug_request_items')
                ->join('drugs', 'drugs.id', '=', 'drug_request_items.drug_id')
                ->join('drug_requests', 'drug_requests.id', '=', 'drug_request_items.drug_request_id')
                ->where('drug_requests.status', 'completed')
                ->select('drugs.name', DB::raw('SUM(drug_request_items.quantity) as total_qty'))
                ->groupBy('drugs.name')
                ->orderBy('total_qty', 'DESC')
                ->limit(5)
                ->get();

            return response()->json([
                'stats' => $stats,
                'top_drugs' => $topDrugs,
                'summary' => [
                    'total_completed' => (int)$totalCompleted,
                    'total_items_distributed' => (int)$totalItems
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}