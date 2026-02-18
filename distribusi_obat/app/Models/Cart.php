<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'drug_id', 'quantity'];

    public function drug() {
        return $this->belongsTo(Drug::class);
    }
}