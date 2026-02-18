<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;

class CmsSeeder extends Seeder
{
    public function run()
    {
        // Buat Profil Tentang Kami
        Profile::create([
            'key' => 'about',
            'title' => 'Tentang Yayasan E-Pharma',
            'content' => 'Yayasan E-Pharma adalah lembaga nirlaba yang berfokus pada distribusi obat-obatan berkualitas untuk unit kesehatan masyarakat.'
        ]);

        // Buat Visi Misi
        Profile::create([
            'key' => 'vision_mission',
            'title' => 'Visi & Misi Kami',
            'content' => '<ul><li>Menjamin ketersediaan obat.</li><li>Distribusi cepat dan transparan.</li><li>Mendukung kesehatan masyarakat.</li></ul>'
        ]);

        // Kontak
        Profile::create(
        ['key' => 'contact_address', 'title' => 'Alamat Kantor', 'content' => 'Jl. Farmasi Raya No. 45, Jakarta Pusat'],
        ['key' => 'contact_phone', 'title' => 'Nomor Telepon', 'content' => '(021) 555-0123'],
        ['key' => 'contact_email', 'title' => 'Email Resmi', 'content' => 'cs@e-pharma.org'],
        ['key' => 'contact_wa', 'title' => 'WhatsApp Bisnis', 'content' => '081234567890']
        );
    }
}