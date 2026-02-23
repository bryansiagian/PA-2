<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable; // Pastikan Anda membuat Trait ini

class Drug extends Model {
    use HasFactory, Blameable;

    protected $fillable = [
        'category_id',
        'rack_id',
        'sku',
        'name',
        'image',
        'unit',
        'stock',
        'min_stock',
        'is_bulky',
        'active',
    ];

    protected $casts = [
        'is_bulky' => 'boolean',
        'active' => 'integer',
    ];

    // Relasi ke Kategori
    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relasi ke Rak (Revisi Poin #1)
    public function rack() {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    // Relasi ke Kartu Stok
    public function stockLogs() {
        return $this->hasMany(StockLog::class);
    }

    // Relasi ke User pembuat data (Blameable)
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}