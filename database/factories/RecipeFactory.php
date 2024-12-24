<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    public function definition()
    {
        return [
            'label' => $this->faker->word,
            'images' => json_encode([
                'THUMBNAIL' => [
                    'url' => $this->faker->imageUrl(100, 100), // Generating a thumbnail size image
                    'width' => 100,
                    'height' => 100
                ],
                'SMALL' => [
                    'url' => $this->faker->imageUrl(200, 200), // Generating a small size image
                    'width' => 200,
                    'height' => 200
                ],
                'REGULAR' => [
                    'url' => $this->faker->imageUrl(300, 300), // Generating a regular size image
                    'width' => 300,
                    'height' => 300
                ],
                'LARGE' => [
                    'url' => $this->faker->imageUrl(400, 400), // Generating a large size image
                    'width' => 400,
                    'height' => 400
                ]
            ]),
            'source' => $this->faker->company,
            'recipe_url' => $this->faker->url,
            'yield' => $this->faker->numberBetween(1, 10),
            'calories' => $this->faker->numberBetween(100, 500),
            'health_labels' => json_encode(['Vegan', 'Low-Fat']),
            'cuisine_type' => json_encode(['American']),
            'meal_type' => json_encode(['Lunch']),
            'dish_type' => json_encode(['Main Course']),
        ];
    }
}
