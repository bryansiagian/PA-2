<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugRequest extends Model {
    protected $fillable = [
        'user_id',
        'status',
        'request_type',
        'required_vehicle',
        'notes'
    ];
    public function items() {
        return $this->hasMany(DrugRequestItem::class, 'drug_request_id')->with('drug');
    }
    public function user() { return $this->belongsTo(User::class); } // Customer
    public function delivery() { return $this->hasOne(Delivery::class); }
}
