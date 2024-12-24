<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['country_id', 'name'];

    /**
     * Define the relationship to the Country model.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
