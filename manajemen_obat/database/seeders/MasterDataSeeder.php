<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Drug;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat Kategori
        $cat1 = Category::create(['name' => 'Analgetik']);
        $cat2 = Category::create(['name' => 'Antibiotik']);
        $cat3 = Category::create(['name' => 'Vitamin']);
        $cat4 = Category::create(['name' => 'Cairan & Infus']); // Tambahan untuk testing bulky

        // 1. Paracetamol (Kecil - Motor)
        Drug::create([
            'category_id' => $cat1->id,
            'sku' => 'DRG-001',
            'name' => 'Paracetamol 500mg',
            'image' => 'https://bernofarm.com/wp-content/uploads/2021/11/PARACETAMOL-500-mg-KAPLET.png',
            'unit' => 'Tablet',
            'stock' => 500,
            'min_stock' => 50,
            'rack_number' => 'RAK-A', // Lokasi Fisik
            'row_number' => 'BARIS-1', // Lokasi Fisik
            'is_bulky' => false       // Bisa dibawa motor
        ]);

        // 2. Amoxicillin (Kecil - Motor)
        Drug::create([
            'category_id' => $cat2->id,
            'sku' => 'DRG-002',
            'name' => 'Amoxicillin 250mg',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTCXFaAW69XyDQ6UH_bx6hauxsbJOwYZPKoAw&s',
            'unit' => 'Kapsul',
            'stock' => 200,
            'min_stock' => 30,
            'rack_number' => 'RAK-A',
            'row_number' => 'BARIS-2',
            'is_bulky' => false
        ]);

        // 3. Vitamin C (Kecil - Motor)
        Drug::create([
            'category_id' => $cat3->id,
            'sku' => 'DRG-003',
            'name' => 'Vitamin C 1000mg',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQGVkqxEmYXdgJCgALurXFG3jGyjdvTfWR4yA&s',
            'unit' => 'Botol',
            'stock' => 100,
            'min_stock' => 20,
            'rack_number' => 'RAK-B',
            'row_number' => 'BARIS-1',
            'is_bulky' => false
        ]);

        // 4. Cairan Infus (Besar/Bulky - Wajib Mobil)
        Drug::create([
            'category_id' => $cat4->id,
            'sku' => 'DRG-004',
            'name' => 'Infus NaCl 0.9% (Karton)',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTVG8qF6j4tQ3Sj7u3P6uM6vL9fD0-K0B1Q3g&s',
            'unit' => 'Karton',
            'stock' => 50,
            'min_stock' => 10,
            'rack_number' => 'RAK-C',
            'row_number' => 'Lantai',
            'is_bulky' => true // Memicu logika mobil
        ]);
    }
}