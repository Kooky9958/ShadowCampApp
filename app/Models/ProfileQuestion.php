<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goals',
        'mental_health_issues',
        'hair_loss',
        'birth_control',
        'reproductive_disorder',
        'weight_change',
        'coffee_consumption',
        'alcohol_consumption',
        'other_goal',
    ];

    // If goals and mental_health_issues are stored as JSON, cast them properly
    protected $casts = [
        'goals' => 'array',
        'mental_health_issues' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
