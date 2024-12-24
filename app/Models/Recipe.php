<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'uri',
        'label',
        'images', // Replaced thumbnail with images (JSON object)
        'source',
        'url',
        'yield',
        'health_labels',
        'calories',
        'cuisine_type',
        'meal_type',
        'dish_type',
        'ingredient_lines',
    ];

    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'content');
    }

    /**
     * Define relationship with the Ingredient model.
     */
    public function ingredients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    protected $casts = [
        'images' => 'json', // Added to cast images field as JSON
        'health_labels' => 'json',
        'cuisine_type' => 'json',
        'meal_type' => 'json',
        'dish_type' => 'json',
        'ingredient_lines' => 'json',
    ];

    /**
     * Define relationship with the Favorite model.
     */
}
