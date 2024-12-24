<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerraDaily extends Model
{
    use HasFactory;


    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'start_time'            => 'datetime',
        'end_time'              => 'datetime',
        'raw_data'              => 'json',
        'metadata'              => 'json',
        'calories_data'         => 'json',
        'device_data'           => 'json',
        'met_data'              => 'json',
        'oxygen_data'           => 'json',
        'heart_rate_data'       => 'json',
        'distance_data'         => 'json',
        'stress_data'           => 'json',
        'tag_data'              => 'json',
        'scores'                => 'json',
        'strain_data'           => 'json',
        'active_durations_data' => 'json',
    ];
}
