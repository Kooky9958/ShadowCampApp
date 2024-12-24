<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mood extends Model
{
    use HasFactory;

    // Define the table associated with the model (optional if it follows the naming convention)
    protected $table = 'moods';

    // The attributes that are mass assignable
    protected $fillable = [
        'name',
        'icon',
    ];

    // Define any relationships (if required, for example with user_moods)
    public function userMoods(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserMood::class);
    }
}
