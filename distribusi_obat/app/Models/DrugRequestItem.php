<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugRequestItem extends Model {
    protected $fillable = [
        'drug_request_id',
        'drug_id',
        'quantity',
        'custom_drug_name',
        'custom_unit'
    ];

    public function drug() { return $this->belongsTo(Drug::class); }

    public function request()
    {
        // Nama foreign key harus sesuai dengan di migration: drug_request_id
        return $this->belongsTo(DrugRequest::class, 'drug_request_id');
    }
}
