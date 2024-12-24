<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RelativeRelease;

class WatchVideo extends Model
{
    use HasFactory;
    use RelativeRelease;

    protected $fillable = [
        'url_id', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
