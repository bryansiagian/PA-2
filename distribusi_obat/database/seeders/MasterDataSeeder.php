<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Drug;
use App\Models\Rack;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $cat1 = Category::create(['name' => 'Analgetik']);
        $cat2 = Category::create(['name' => 'Antibiotik']);

        $rack1 = Rack::first()->id; // Rak A1

        Drug::create([
            'category_id' => $cat1->id,
            'rack_id' => $rack1,
            'sku' => 'OBT-001',
            'name' => 'Paracetamol 500mg',
            'unit' => 'Strip',
            'stock' => 100,
            'price' => 5000,
            'is_bulky' => false,
            'created_by' => 1
        ]);

        Drug::create([
            'category_id' => $cat2->id,
            'rack_id' => $rack1,
            'sku' => 'OBT-002',
            'name' => 'Amoxicillin Box',
            'unit' => 'Box',
            'stock' => 50,
            'price' => 150000,
            'is_bulky' => true, // Ini akan memicu mobil
            'created_by' => 1
        ]);
    }
}