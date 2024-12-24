<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTargetCalorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'target_calorie',
    ];

    protected $cast = [
        'date' => 'date',
    ];

    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
