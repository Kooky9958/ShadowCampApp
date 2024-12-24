<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_id',
    ];

    public function video(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Video::class, 'url_id');
    }
}
