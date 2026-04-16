<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\ProductOrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\OrdersExport;

class AdminController extends Controller
{
    /**
     * Menampilkan daftar pengguna aktif (selain admin).
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
     * Mengambil daftar pengguna yang baru mendaftar dan sudah verifikasi OTP.
     */
    public function getPendingUsers() {
        try {
            return User::with('roles', 'courierDetail')
                ->where('status', 0)
                ->whereNotNull('email_verified_at')
                ->latest()
                ->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data antrian'], 500);
        }
    }

    public function showUser($id) {
        return User::with('roles')->findOrFail($id);
    }

    /**
     * Menambahkan User/Operator/Kurir secara manual.
     */
    public function storeUser(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
            'address'  => 'nullable|string',
            'vehicle_type' => 'nullable|required_if:role_name,courier|in:motorcycle,car',
            'vehicle_plate' => 'nullable|required_if:role_name,courier|string',
        ]);

        try {
            return DB::transaction(function() use ($request) {
                $role = Role::where('id', $request->role_id)->where('guard_name', 'web')->firstOrFail();

                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'address'  => $request->address,
                    'status'   => 1,
                    'email_verified_at' => now()
                ]);

                $user->assignRole($role->name);

                if ($role->name === 'courier') {
                    \App\Models\CourierDetail::create([
                        'user_id' => $user->id,
                        'vehicle_type' => $request->vehicle_type,
                        'vehicle_plate' => strtoupper($request->vehicle_plate),
                    ]);
                }

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "CREATE USER: Admin membuat akun {$role->name} - {$user->name}"
                ]);

                return response()->json(['message' => 'Akun ' . ucfirst($role->name) . ' berhasil dibuat'], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan user: ' . $e->getMessage()], 500);
        }
    }

    public function approveUser($id) {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 1]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "APPROVE USER: Menyetujui akun {$user->name}"]);
            return response()->json(['message' => 'Pendaftaran akun telah disetujui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses persetujuan'], 500);
        }
    }

    public function rejectUser($id) {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 2]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "REJECT USER: Menolak akun {$user->name}"]);
            return response()->json(['message' => 'Pendaftaran akun telah ditolak']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses penolakan'], 500);
        }
    }

    public function updateUser(Request $request, $id) {
        try {
            $user = User::findOrFail($id);
            $role = Role::where('id', $request->role_id)->where('guard_name', 'web')->firstOrFail();
            $user->update(['name' => $request->name]);
            $user->syncRoles([$role->name]);
            return response()->json(['message' => 'Success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal update role'], 500);
        }
    }

    public function destroyUser($id) {
        try {
            $user = User::findOrFail($id);
            if ($user->id === auth()->id()) return response()->json(['message' => 'Aksi dilarang'], 403);
            $user->delete();
            return response()->json(['message' => 'User berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus user'], 500);
        }
    }

    public function getLogs() {
        return response()->json(AuditLog::with(['user.roles'])->latest()->limit(100)->get());
    }

    public function getRoles() {
        return Role::where('name', '!=', 'admin')->get();
    }

    /**
     * Dashboard Analytics & Rekapitulasi Umum.
     */
    public function getAnalytics(Request $request) {
        try {
            $period = $request->query('period', 'daily');

            // Ambil ID Status dari tabel lookup
            $completedStatus = ProductOrderStatus::where('name', 'Completed')->first();
            $completedId = $completedStatus ? $completedStatus->id : 0;

            $shippingStatus = ProductOrderStatus::where('name', 'Shipping')->first();
            $shippingId = $shippingStatus ? $shippingStatus->id : 0;

            // 1. Hitung Statistik Utama
            $totalUsers = User::role('customer')->count(); // Total Mitra
            $totalProducts = Product::where('active', 1)->count(); // Total Katalog
            $totalOrders = ProductOrder::count(); // Total Transaksi Pembelian

            // Pesanan yang belum sampai (Status selain Completed dan Cancelled)
            $cancelledStatus = ProductOrderStatus::where('name', 'Cancelled')->first();
            $cancelledId = $cancelledStatus ? $cancelledStatus->id : 0;
            $notShippedCount = ProductOrder::whereNotIn('product_order_status_id', [$completedId, $cancelledId])->count();

            // 2. Statistik Grafik Tren
            if ($period == 'daily') {
                $stats = ProductOrder::select(
                        DB::raw('DATE_FORMAT(created_at, "%d %b") as label'),
                        DB::raw('COUNT(*) as total_requests')
                    )
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('label')->orderBy('created_at', 'ASC')->get();
            } else {
                $stats = ProductOrder::select(
                        DB::raw('DATE_FORMAT(created_at, "%b %Y") as label'),
                        DB::raw('COUNT(*) as total_requests')
                    )
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->groupBy('label')->orderBy('created_at', 'ASC')->get();
            }

            // 3. Produk Terlaris
            $topProducts = DB::table('product_order_details')
                ->join('products', 'products.id', '=', 'product_order_details.product_id')
                ->join('product_orders', 'product_orders.id', '=', 'product_order_details.product_order_id')
                ->where('product_orders.product_order_status_id', $completedId)
                ->select('products.name', DB::raw('SUM(product_order_details.quantity) as total_qty'))
                ->groupBy('products.id', 'products.name')
                ->orderBy('total_qty', 'DESC')->limit(5)->get();

            // 4. Data Rasio Pengiriman
            $shippedCount = ProductOrder::where('product_order_status_id', $completedId)->count();

            return response()->json([
                'stats' => $stats,
                'top_drugs' => $topProducts,
                'delivery_ratio' => [
                    'shipped' => $shippedCount,
                    'not_shipped' => $notShippedCount
                ],
                'summary' => [
                    'total_users' => $totalUsers,
                    'total_products' => $totalProducts,
                    'total_orders' => $totalOrders,
                    'not_shipped' => $notShippedCount,
                    'total_items_distributed' => (int)DB::table('product_order_details')
                        ->join('product_orders', 'product_order_details.product_order_id', '=', 'product_orders.id')
                        ->where('product_orders.product_order_status_id', $completedId)->sum('quantity'),
                    'low_stock_products' => Product::where('active', 1)->whereRaw('stock <= min_stock')->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengambil data untuk halaman Reports (Laporan).
     */
    public function getReportData(Request $request) {
        $query = ProductOrder::with(['user', 'status', 'items.product']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        return response()->json($query->latest()->get());
    }

    /**
     * Export Laporan.
     */
    public function exportExcel() {
        return Excel::download(new OrdersExport, 'Laporan_Distribusi_EPharma_' . date('Ymd') . '.xlsx');
    }

    public function exportPdf() {
        $orders = ProductOrder::with(['user', 'status', 'type', 'items'])->latest()->get();
        $pdf = Pdf::loadView('pdf.orders_report', compact('orders'));
        return $pdf->download('Laporan_Distribusi_EPharma_' . date('Ymd') . '.pdf');
    }
}
