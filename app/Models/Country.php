<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

     // Define the table associated with the model
    protected $table = 'country';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'name',
    ];
}
