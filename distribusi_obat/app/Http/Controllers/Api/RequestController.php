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

    public function index() {
        try {
            $user = auth()->user();
            // Eager load rack dan storage untuk Picking List Operator
            $query = DrugRequest::with(['items.drug.rack.storage', 'user', 'delivery'])->latest();

            if ($user->hasRole('customer')) {
                $query->where('user_id', $user->id);
            }

            return response()->json($query->get(), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request) {
        return DB::transaction(function() use ($request) {
            $userId = auth()->id();
            $cartItems = Cart::with('drug')->where('user_id', $userId)->get();

            if($cartItems->isEmpty()) return response()->json(['message' => 'Keranjang kosong'], 422);

            // --- LOGIKA SMART LOGISTICS (Revisi Poin #4) ---
            $totalQuantity = 0;
            $anyBulkyItem = false;
            $limitMotor = 50;

            foreach($cartItems as $item) {
                $totalQuantity += (int)$item->quantity;
                if($item->drug->is_bulky) $anyBulkyItem = true;
            }

            $finalVehicle = ($anyBulkyItem || $totalQuantity > $limitMotor) ? 'car' : 'motorcycle';

            // --- SIMPAN REQUEST ---
            $drugReq = DrugRequest::create([
                'user_id' => $userId,
                'status' => 'pending',
                'request_type' => $request->request_type ?? 'delivery', // Revisi Poin #2
                'required_vehicle' => $finalVehicle,
                'notes' => $request->notes
            ]);

            foreach ($cartItems as $item) {
                DrugRequestItem::create([
                    'drug_request_id' => $drugReq->id,
                    'drug_id'         => $item->drug_id,
                    'quantity'         => $item->quantity,
                ]);
            }

            Cart::where('user_id', $userId)->delete();

            AuditLog::create([
                'user_id' => $userId,
                'action'  => "CREATE REQUEST: Membuat pesanan #REQ-{$drugReq->id} (Tipe: {$drugReq->request_type})"
            ]);

            return response()->json(['message' => 'Pesanan berhasil dibuat!', 'vehicle' => $finalVehicle], 201);
        });
    }

    public function approve($id) {
        return DB::transaction(function() use ($id) {
            $req = DrugRequest::with(['items.drug', 'user'])->lockForUpdate()->findOrFail($id);

            if ($req->status !== 'pending') return response()->json(['message' => 'Sudah diproses'], 422);

            foreach($req->items as $item) {
                if ($item->drug_id) {
                    $drug = Drug::where('id', $item->drug_id)->lockForUpdate()->first();
                    if($drug->stock < $item->quantity) throw new \Exception("Stok {$drug->name} tidak cukup.");

                    $drug->decrement('stock', $item->quantity);

                    StockLog::create([
                        'drug_id' => $drug->id, 'user_id' => auth()->id(),
                        'type' => 'out', 'quantity' => $item->quantity, 'reference' => "REQ-{$id}"
                    ]);
                }
            }

            $req->update(['status' => 'approved']);

            try {
                Mail::to($req->user->email)->send(new OrderNotification($req, 'Disetujui'));
            } catch (\Exception $e) { Log::error("Mail Error: " . $e->getMessage()); }

            AuditLog::create(['user_id' => auth()->id(), 'action' => "APPROVE: Menyetujui #REQ-{$id}"]);

            return response()->json(['message' => 'Berhasil disetujui']);
        });
    }

    public function reject($id) {
        $req = DrugRequest::findOrFail($id);
        $req->update(['status' => 'rejected']);
        AuditLog::create(['user_id' => auth()->id(), 'action' => "REJECT: Menolak #REQ-{$id}"]);
        return response()->json(['message' => 'Ditolak']);
    }

    public function cancel($id) {
        return DB::transaction(function() use ($id) {
            $req = DrugRequest::with('items')->lockForUpdate()->findOrFail($id);
            if (in_array($req->status, ['shipping', 'completed'])) return response()->json(['message' => 'Gagal batal'], 422);

            if ($req->status === 'approved') {
                foreach ($req->items as $item) {
                    if ($item->drug_id) Drug::find($item->drug_id)->increment('stock', $item->quantity);
                }
            }

            $req->update(['status' => 'cancelled']);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "CANCEL: Membatalkan #REQ-{$id}"]);
            return response()->json(['message' => 'Dibatalkan']);
        });
    }
}