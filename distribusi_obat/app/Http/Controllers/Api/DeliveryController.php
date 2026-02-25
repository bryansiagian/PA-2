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

class DeliveryController extends Controller {

    public function getTracking($id) {
        return response()->json(Delivery::with(['request.user', 'courier', 'trackings' => fn($q) => $q->latest()])->findOrFail($id));
    }

    public function makeReady($id) {
        return DB::transaction(function() use ($id) {
            $req = DrugRequest::findOrFail($id);
            $delivery = Delivery::create([
                'drug_request_id' => $req->id,
                'status' => 'ready',
                'tracking_number' => 'TRK-' . strtoupper(bin2hex(random_bytes(4)))
            ]);
            $req->update(['status' => 'shipping']);

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location' => 'Gudang Pusat',
                'description' => 'Pesanan siap dijemput kurir.'
            ]);

            AuditLog::create(['user_id' => auth()->id(), 'action' => "READY: Paket #REQ-{$id} siap dijemput"]);
            return response()->json(['message' => 'Siap diambil']);
        });
    }

    public function claim($id) {
        return DB::transaction(function() use ($id) {
            $delivery = Delivery::lockForUpdate()->findOrFail($id);
            if ($delivery->courier_id) return response()->json(['message' => 'Sudah diambil'], 422);

            $delivery->update(['courier_id' => auth()->id(), 'status' => 'claimed']);

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location' => 'Gudang Pusat',
                'description' => 'Kurir ' . auth()->user()->name . ' telah mengambil paket.'
            ]);

            return response()->json(['message' => 'Tugas diambil']);
        });
    }

    public function startShipping($id) {
        $delivery = Delivery::where('id', $id)->where('courier_id', auth()->id())->firstOrFail();
        $delivery->update(['status' => 'in_transit', 'picked_up_at' => now()]);
        ShipmentTracking::create(['delivery_id' => $delivery->id, 'location' => 'Perjalanan', 'description' => 'Kurir menuju lokasi']);
        return response()->json(['message' => 'Dalam perjalanan']);
    }

    public function complete(Request $request, $id) {
        $request->validate([
            'image' => 'required|image|max:2048',
            'receiver_name' => 'required|string', // Revisi Poin #5
            'receiver_relation' => 'required|string' // Revisi Poin #5
        ]);

        return DB::transaction(function() use ($request, $id) {
            $delivery = Delivery::where('id', $id)->where('courier_id', auth()->id())->firstOrFail();
            $path = $request->file('image')->store('proofs', 'public');

            $delivery->update([
                'status' => 'delivered',
                'proof_image' => $path,
                'receiver_name' => $request->receiver_name,
                'receiver_relation' => $request->receiver_relation,
                'delivered_at' => now()
            ]);

            $delivery->request->update(['status' => 'completed']);

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location' => 'Tujuan',
                'description' => "Paket diterima oleh {$request->receiver_name} ({$request->receiver_relation})"
            ]);

            AuditLog::create(['user_id' => auth()->id(), 'action' => "DELIVERED: Selesai antar #{$delivery->tracking_number}"]);
            return response()->json(['message' => 'Selesai!']);
        });
    }

    public function getCourierStats() {
        $userId = auth()->id();
        $myVehicle = CourierDetail::where('user_id', $userId)->first()?->vehicle_type;

        return response()->json([
            'available' => Delivery::where('status', 'ready')->whereNull('courier_id')
                ->whereHas('request', fn($q) => $q->where('required_vehicle', $myVehicle))->count(),
            'active' => Delivery::where('courier_id', $userId)->whereIn('status', ['claimed', 'in_transit'])->count(),
            'completed' => Delivery::where('courier_id', $userId)->where('status', 'delivered')->count(),
        ]);
    }

    public function getAvailableDeliveries() {
        $myVehicle = CourierDetail::where('user_id', auth()->id())->first()?->vehicle_type;
        return Delivery::with(['request.user', 'request.items.drug'])
            ->where('status', 'ready')->whereNull('courier_id')
            ->whereHas('request', fn($q) => $q->where('required_vehicle', $myVehicle))->get();
    }

    public function getActiveDeliveries() {
        return Delivery::with(['request.user', 'request.items.drug'])->where('courier_id', auth()->id())->whereIn('status', ['claimed', 'in_transit'])->get();
    }

    public function getCourierHistory() {
        try {
            $user = auth()->user();

            // Ambil data yang statusnya 'delivered' milik kurir ini
            $history = Delivery::with(['request.user', 'request.items.drug'])
                ->where('courier_id', $user->id)
                ->where('status', 'delivered')
                ->latest('delivered_at')
                ->get();

            return response()->json($history, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memuat riwayat: ' . $e->getMessage()], 500);
        }
    }
}