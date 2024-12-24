<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => \App\Models\Recipe::factory(), // Use the Recipe factory to create a recipe for this ingredient
            'text' => $this->faker->word, // Random ingredient name
            'quantity' => $this->faker->randomFloat(2, 1, 10), // Random quantity between 1 and 10
            'measure' => $this->faker->randomElement(['g', 'kg', 'ml', 'l', 'cup', 'tbsp', 'tsp']), // Random measure unit
            'food' => $this->faker->word, // Random food item name
            'weight' => $this->faker->numberBetween(50, 500), // Random weight between 50 and 500
            'foodId' => $this->faker->uuid, // Random unique identifier for the food
        ];
    }
}
