<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerraSleep extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'start_time'           => 'datetime',
        'end_time'             => 'datetime',
        'raw_data'             => 'json',
        'metadata'             => 'json',
        'device_data'          => 'json',
        'heart_rate_data'      => 'json',
        'readiness_data'       => 'json',
        'respiration_data'     => 'json',
        'sleep_durations_data' => 'json',
        'temperature_data'     => 'json',
    ];
}
