<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLog extends Model {
    protected $fillable = [
        'drug_id',
        'user_id',
        'type',
        'quantity',
        'reference'
    ];
    
    public function drug() { return $this->belongsTo(Drug::class); }
}
