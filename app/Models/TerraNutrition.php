<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerraNutrition extends Model
{
    use HasFactory;

    protected $fillable = [
        'terra_user_provider_id',
        'start_time',
        'end_time',
        'raw_data',
        'summary',
        'meals',
        'drink_samples',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'raw_data' => 'json',
        'summary' => 'json',
        'meals' => 'json',
        'drink_samples' => 'json',
    ];
}
