<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderNotification;
use App\Models\DrugRequest;
use App\Models\DrugRequestItem;
use App\Models\Drug;
use App\Models\User;
use App\Models\Cart;
use App\Models\AuditLog;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestController extends Controller {

    /**
     * Menampilkan daftar permintaan.
     * Customer hanya melihat miliknya, Admin/Operator melihat semua.
     */
    public function index() {
        try {
            $user = auth()->user();

            // Query dasar dengan relasi yang dibutuhkan
            $query = DrugRequest::with(['items.drug', 'user', 'delivery'])
                ->latest();

            // Penerapan Spatie RBAC
            if ($user->hasRole('customer')) {
                $query->where('user_id', $user->id);
            }

            return response()->json($query->get(), 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan permintaan baru dari Keranjang (Checkout).
     * Dilengkapi Logika Deteksi Kendaraan Otomatis.
     */
    public function store(Request $request) {
        return DB::transaction(function() use ($request) {
            $userId = auth()->id();

            // 1. Ambil isi keranjang beserta info obat (untuk cek is_bulky)
            $cartItems = Cart::with('drug')->where('user_id', $userId)->get();

            if($cartItems->isEmpty()) {
                return response()->json(['message' => 'Keranjang kosong'], 422);
            }

            // --- MULAI LOGIKA CERDAS PENENTUAN KENDARAAN (Dispatching Logic) ---
            $totalQuantity = 0;
            $anyBulkyItem = false;
            $limitMotor = 50; // Batas kapasitas angkut motor

            foreach($cartItems as $item) {
                $totalQuantity += $item->quantity;

                // Indikator 1: Cek apakah jenis obatnya bersifat Bulky (Besar/Berat)
                if($item->drug->is_bulky == true) {
                    $anyBulkyItem = true;
                }
            }

            // Indikator 2: Cek apakah total kuantitas kumulatif > limit motor
            $isTooHeavy = ($totalQuantity > $limitMotor);

            // Keputusan Final: Jika salah satu syarat terpenuhi, wajib pakai Mobil
            $finalVehicle = ($anyBulkyItem || $isTooHeavy) ? 'car' : 'motorcycle';
            // --- SELESAI LOGIKA ---

            // 2. Buat Header Permintaan
            $drugReq = DrugRequest::create([
                'user_id' => $userId,
                'status' => 'pending',
                'request_type' => $request->request_type ?? 'delivery',
                'required_vehicle' => $finalVehicle,
                'notes' => $request->notes ?? 'Permintaan stok rutin.'
            ]);

            // 3. Pindahkan data dari tabel Cart ke DrugRequestItem
            foreach ($cartItems as $item) {
                DrugRequestItem::create([
                    'drug_request_id' => $drugReq->id,
                    'drug_id'         => $item->drug_id,
                    'quantity'         => $item->quantity,
                ]);
            }

            // 4. Kosongkan Keranjang Database
            Cart::where('user_id', $userId)->delete();

            // 5. Catat ke Audit Log
            AuditLog::create([
                'user_id' => $userId,
                'action'  => "CREATE REQUEST: Membuat permintaan stok baru #REQ-{$drugReq->id} (Kendaraan: {$finalVehicle})"
            ]);

            return response()->json([
                'message' => 'Pesanan berhasil dibuat!',
                'required_vehicle' => $finalVehicle
            ], 201);
        });
    }

    /**
     * Menyetujui permintaan (Oleh Operator/Admin).
     * Melakukan pemotongan stok dan pengiriman email.
     */
    public function approve($id) {
        return DB::transaction(function() use ($id) {
            // Lock data untuk integritas stok (Pessimistic Locking)
            $req = DrugRequest::with(['items.drug', 'user'])->lockForUpdate()->findOrFail($id);

            if ($req->status !== 'pending') {
                return response()->json(['message' => 'Hanya permintaan pending yang dapat disetujui.'], 422);
            }

            foreach($req->items as $item) {
                if ($item->drug_id) {
                    $drug = Drug::where('id', $item->drug_id)->lockForUpdate()->first();

                    if($drug->stock < $item->quantity) {
                        throw new \Exception("Stok obat {$drug->name} tidak mencukupi (Sisa: {$drug->stock}).");
                    }

                    // 1. Potong Stok Fisik
                    $drug->decrement('stock', $item->quantity);

                    // 2. Catat riwayat di Kartu Stok (OUT)
                    StockLog::create([
                        'drug_id'  => $drug->id,
                        'user_id'  => auth()->id(),
                        'type'     => 'out',
                        'quantity' => $item->quantity,
                        'reference'=> "REQ-{$id}"
                    ]);
                }
            }

            // 3. Update Status
            $req->update(['status' => 'approved']);

            // 4. Kirim Notifikasi Email (Struk Digital)
            try {
                Mail::to($req->user->email)->send(new OrderNotification($req, 'Disetujui & Menunggu Kurir'));
            } catch (\Exception $e) {
                Log::error("Gagal kirim email ke {$req->user->email}: " . $e->getMessage());
            }

            // 5. Catat ke Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "APPROVE REQUEST: Menyetujui permintaan #REQ-{$id} dari {$req->user->name}"
            ]);

            return response()->json(['message' => 'Permintaan disetujui & stok berhasil dipotong.']);
        });
    }

    /**
     * Menolak permintaan (Oleh Operator/Admin).
     */
    public function reject($id) {
        try {
            $req = DrugRequest::with('user')->findOrFail($id);
            $req->update(['status' => 'rejected']);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "REJECT REQUEST: Menolak permintaan #REQ-{$id} dari {$req->user->name}"
            ]);

            return response()->json(['message' => 'Permintaan telah ditolak.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses penolakan.'], 500);
        }
    }

    /**
     * Membatalkan permintaan (Oleh Customer).
     * Melakukan Rollback Stok jika sebelumnya sudah disetujui.
     */
    public function cancel($id) {
        return DB::transaction(function() use ($id) {
            $req = DrugRequest::with(['items.drug', 'user'])->lockForUpdate()->findOrFail($id);

            // Validasi Keamanan: Batalkan hanya jika belum dikirim kurir
            if (in_array($req->status, ['shipping', 'completed', 'cancelled', 'rejected'])) {
                return response()->json(['message' => 'Permintaan tidak dapat dibatalkan pada tahap ini.'], 422);
            }

            // LOGIKA ROLLBACK: Jika status sebelumnya 'approved', stok harus dikembalikan
            if ($req->status === 'approved') {
                foreach ($req->items as $item) {
                    if ($item->drug_id) {
                        $drug = Drug::where('id', $item->drug_id)->lockForUpdate()->first();
                        if ($drug) {
                            $drug->increment('stock', $item->quantity);

                            // Catat Kartu Stok (IN - Pengembalian)
                            StockLog::create([
                                'drug_id'  => $drug->id,
                                'user_id'  => auth()->id(),
                                'type'     => 'in',
                                'quantity' => $item->quantity,
                                'reference'=> "CANCEL-ROLLBACK-REQ-{$id}"
                            ]);
                        }
                    }
                }
            }

            $req->update(['status' => 'cancelled']);

            // Catat ke Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "CANCEL REQUEST: Customer membatalkan permintaan #REQ-{$id} (Stok di-rollback)"
            ]);

            return response()->json(['message' => 'Permintaan berhasil dibatalkan dan stok dikembalikan.']);
        });
    }
}