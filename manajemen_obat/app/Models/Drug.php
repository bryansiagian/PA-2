<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model {
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'image',
        'unit',
        'stock',
        'min_stock',
        'is_bulky',
        'rack_number',
        'row_number'
    ];

    protected $casts = [
        'is_bulky' => 'boolean',
    ];

    public function category() { return $this->belongsTo(Category::class, 'category_id'); }
    public function stockLogs() { return $this->hasMany(StockLog::class); }
}
