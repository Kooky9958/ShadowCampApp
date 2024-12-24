<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerraActivity extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'start_time'        => 'datetime',
        'end_time'          => 'datetime',
        'raw_data'          => 'json',
        'metadata'          => 'json',
        'met_data'          => 'json',
        'laps_data'         => 'json',
        'calories_data'     => 'json',
        'activity_duration' => 'json',
        'oxygen_data'       => 'json',
        'energy_data'       => 'json',
        'tss_samples_data'  => 'json',
        'device_data'       => 'json',
        'distance_data'     => 'json',
        'polyline_map_data' => 'json',
        'heart_rate_data'   => 'json',
        'movement_data'     => 'json',
        'strain_data'       => 'json',
        'power_data'        => 'json',
        'position_data'     => 'json',
    ];
}
