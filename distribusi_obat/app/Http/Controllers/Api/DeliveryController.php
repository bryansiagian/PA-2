<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DrugRequest;
use App\Models\ShipmentTracking;
use App\Models\AuditLog;
use App\Models\CourierDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller {

    /**
     * Mengambil detail pelacakan untuk timeline (Customer/Operator/Admin)
     */
    public function getTracking($id) {
        try {
            $delivery = Delivery::with(['request.user', 'courier', 'trackings' => function($q) {
                $q->orderBy('created_at', 'desc');
            }])->findOrFail($id);

            return response()->json($delivery, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Data pengiriman tidak ditemukan'], 404);
        }
    }

    /**
     * OPERATOR: Menandai barang sudah dipacking dan siap dijemput kurir.
     */
    public function makeReady($id) {
        try {
            return DB::transaction(function() use ($id) {
                $req = DrugRequest::findOrFail($id);

                // Buat data delivery dengan status 'ready'
                $delivery = Delivery::create([
                    'drug_request_id' => $req->id,
                    'status' => 'ready',
                    'tracking_number' => 'TRK-' . strtoupper(bin2hex(random_bytes(4)))
                ]);

                // Update status di tabel induk menjadi shipping
                $req->update(['status' => 'shipping']);

                // Catat di Timeline
                ShipmentTracking::create([
                    'delivery_id' => $delivery->id,
                    'location' => 'Gudang Farmasi',
                    'description' => 'Pesanan telah dikemas dan menunggu dijemput oleh kurir.'
                ]);

                // Catat ke Audit Log
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => "READY FOR PICKUP: Menyiapkan paket #REQ-{$id} untuk kurir."
                ]);

                return response()->json(['message' => 'Barang siap diambil kurir'], 200);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses: ' . $e->getMessage()], 500);
        }
    }

    /**
     * KURIR: Mengambil tugas pengiriman dari bursa (Claim).
     */
    public function claim($id) {
        try {
            return DB::transaction(function() use ($id) {
                $delivery = Delivery::lockForUpdate()->findOrFail($id);

                if ($delivery->courier_id) {
                    return response()->json(['message' => 'Tugas ini sudah diambil kurir lain'], 422);
                }

                $delivery->update([
                    'courier_id' => auth()->id(),
                    'status' => 'claimed'
                ]);

                // Catat di Timeline
                ShipmentTracking::create([
                    'delivery_id' => $delivery->id,
                    'location' => 'Gudang Farmasi',
                    'description' => 'Paket telah diterima dan akan segera dikirim oleh Kurir: ' . auth()->user()->name
                ]);

                // Catat ke Audit Log
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => "CLAIM TASK: Mengambil tugas pengiriman paket #{$delivery->tracking_number}"
                ]);

                return response()->json(['message' => 'Tugas berhasil diambil']);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil tugas.'], 500);
        }
    }

    /**
     * KURIR: Mengonfirmasi bahwa paket mulai dibawa dalam perjalanan.
     */
    public function startShipping($id) {
        try {
            $delivery = Delivery::where('id', $id)
                ->where('courier_id', auth()->id())
                ->firstOrFail();

            $delivery->update(['status' => 'in_transit', 'picked_up_at' => now()]);

            // Catat di Timeline
            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location' => 'Dalam Perjalanan',
                'description' => 'Kurir telah meninggalkan gudang dan sedang menuju lokasi tujuan.'
            ]);

            return response()->json(['message' => 'Status: Dalam Perjalanan']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Aksi ditolak.'], 403);
        }
    }

    /**
     * KURIR: Menyelesaikan pengiriman dengan mengunggah foto bukti (POD).
     */
    public function complete(Request $request, $id) {
        $request->validate([
            'image' => 'required|image|max:2048'
        ]);

        try {
            return DB::transaction(function() use ($request, $id) {
                $delivery = Delivery::with('request')->where('id', $id)
                    ->where('courier_id', auth()->id())
                    ->firstOrFail();

                // Simpan foto bukti ke storage public
                $path = $request->file('image')->store('proofs', 'public');

                $delivery->update([
                    'status' => 'delivered',
                    'proof_image' => $path,
                    'delivered_at' => now()
                ]);

                // Update status final di tabel induk DrugRequest
                $delivery->request->update(['status' => 'completed']);

                // Catat di Timeline Final
                ShipmentTracking::create([
                    'delivery_id' => $delivery->id,
                    'location' => 'Lokasi Tujuan',
                    'description' => 'Paket telah sampai di lokasi dan diterima dengan sukses.'
                ]);

                // Catat ke Audit Log
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => "DELIVERY COMPLETE: Menyelesaikan pengiriman paket #{$delivery->tracking_number}"
                ]);

                return response()->json(['message' => 'Pengiriman Berhasil Diselesaikan!']);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyelesaikan pengiriman.'], 500);
        }
    }

    /**
     * Statistik untuk Dashboard Kurir
     */
    public function getCourierStats() {
        try {
            $user = auth()->user();

            // 1. Ambil info kendaraan kurir yang sedang login
            $courierInfo = \App\Models\CourierDetail::where('user_id', $user->id)->first();

            // Jika data kendaraan tidak ditemukan (statis 0)
            if (!$courierInfo) {
                return response()->json([
                    'available' => 0,
                    'active'    => 0,
                    'completed' => 0,
                ], 200);
            }

            $myVehicle = $courierInfo->vehicle_type; // 'motorcycle' atau 'car'

            // 2. HITUNG BURSA TERSEDIA (Hanya yang cocok dengan kendaraan kurir ini)
            $available = Delivery::where('status', 'ready')
                ->whereNull('courier_id')
                ->whereHas('request', function($query) use ($myVehicle) {
                    // Filter: Harus cocok dengan kendaraan yang diminta pesanan
                    $query->where('required_vehicle', $myVehicle);
                })
                ->count();

            // 3. Hitung Tugas Aktif Milik Kurir ini
            $active = Delivery::where('courier_id', $user->id)
                ->whereIn('status', ['claimed', 'in_transit'])
                ->count();

            // 4. Hitung Total Berhasil Milik Kurir ini
            $completed = Delivery::where('courier_id', $user->id)
                ->where('status', 'delivered')
                ->count();

            return response()->json([
                'available' => $available,
                'active'    => $active,
                'completed' => $completed,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mendapatkan daftar bursa tugas (tersedia untuk diambil)
     */
    public function getAvailableDeliveries() {
        try {
            $user = auth()->user();

            // 1. Ambil data kendaraan kurir yang sedang login
            // Kita harus ambil dari tabel courier_details
            $courierInfo = \App\Models\CourierDetail::where('user_id', $user->id)->first();

            // Jika data kendaraan tidak ditemukan, kembalikan array kosong
            if (!$courierInfo) {
                return response()->json([], 200);
            }

            $myVehicle = $courierInfo->vehicle_type; // 'motorcycle' atau 'car'

            // 2. Ambil pengiriman yang:
            // - Statusnya 'ready'
            // - Belum ada kurirnya (courier_id is null)
            // - Required_vehicle-nya cocok dengan kendaraan kurir ini
            $available = Delivery::with(['request.user', 'request.items.drug'])
                ->where('status', 'ready')
                ->whereNull('courier_id')
                ->whereHas('request', function($query) use ($myVehicle) {
                    $query->where('required_vehicle', $myVehicle);
                })
                ->latest()
                ->get();

            return response()->json($available, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mendapatkan daftar tugas aktif milik kurir
     */
    public function getActiveDeliveries() {
        return Delivery::with(['request.user', 'request.items.drug'])
            ->where('courier_id', auth()->id())
            ->whereIn('status', ['claimed', 'in_transit'])
            ->latest()
            ->get();
    }

    /**
     * Mendapatkan riwayat pengiriman selesai milik kurir
     */
    public function getCourierHistory() {
        return Delivery::with(['request.user', 'request.items.drug'])
            ->where('courier_id', auth()->id())
            ->where('status', 'delivered')
            ->latest('delivered_at')
            ->get();
    }

    /**
     * Index umum (Opsional)
     */
    public function index() {
        $user = auth()->user();
        $query = Delivery::with(['request.user']);

        if ($user->role->name === 'courier') {
            $query->where('courier_id', $user->id);
        }

        return $query->latest()->get();
    }
}