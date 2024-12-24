<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerraBody extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'start_time'          => 'datetime',
        'end_time'            => 'datetime',
        'raw_data'            => 'json',
        'metadata'            => 'json',
        'blood_pressure_data' => 'json',
        'device_data'         => 'json',
        'glucose_data'        => 'json',
        'heart_data'          => 'json',
        'hydration_data'      => 'json',
        'ketone_data'         => 'json',
        'measurements_data'   => 'json',
        'oxygen_data'         => 'json',
        'temperature_data'    => 'json',
    ];
}
