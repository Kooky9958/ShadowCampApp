<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerraMenstruation extends Model
{
    use HasFactory;

    protected $fillable = [
        'terra_user_provider_id',
        'start_time',
        'end_time',
        'raw_data',
        'menstruation_data',
        'metadata',
    ];

    protected $casts = [
        'start_time'        => 'datetime',
        'end_time'          => 'datetime',
        'raw_data'          => 'json',
        'menstruation_data' => 'json',
        'metadata'          => 'json',
    ];
}
