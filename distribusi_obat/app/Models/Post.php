<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model {
    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'type',
        'user_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}