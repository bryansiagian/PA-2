<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $appends = ['proof_image_url'];
    protected $fillable = [
        'drug_request_id',
        'courier_id',
        'tracking_number',
        'status',
        'proof_image',
        'picked_up_at',
        'delivered_at'
    ];

    // Tambahkan 'drug_request_id' sebagai parameter kedua (Foreign Key)
    public function request()
    {
        return $this->belongsTo(DrugRequest::class, 'drug_request_id');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function trackings()
    {
        return $this->hasMany(ShipmentTracking::class, 'delivery_id');
    }

    public function getProofImageUrlAttribute()
    {
        if ($this->proof_image) {
            return asset('storage/' . $this->proof_image);
        }
        return null;
    }
}