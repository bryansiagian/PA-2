<?php

namespace App\Services;

use App\Models\ShipmentTracking;
use App\Models\Delivery;

class ShipmentService {
    // Logika ini bisa dipisah menjadi service mandiri (Microservice logic)
    public function updateLocation($deliveryId, $location, $desc) {
        return ShipmentTracking::create([
            'delivery_id' => $deliveryId,
            'location' => $location,
            'description' => $desc
        ]);
    }
}