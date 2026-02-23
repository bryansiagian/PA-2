<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Storage;
use App\Models\Rack;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Gudang
        $storage = Storage::create([
            'name' => 'Gudang Utama Farmasi',
            'location' => 'Lantai 1 Sayap Barat',
            'created_by' => 1 // ID Admin
        ]);

        // Buat Rak-rak di dalam gudang tersebut
        $racks = ['RAK-A1', 'RAK-A2', 'RAK-B1', 'RAK-B2'];
        foreach ($racks as $r) {
            Rack::create([
                'storage_id' => $storage->id,
                'name' => $r,
                'created_by' => 1
            ]);
        }
    }
}