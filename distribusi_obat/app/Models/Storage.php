<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class Storage extends Model {
    use Blameable;
    protected $fillable = ['name', 'location', 'active', 'created_by', 'updated_by'];
    public function racks() { return $this->hasMany(Rack::class); }
}
