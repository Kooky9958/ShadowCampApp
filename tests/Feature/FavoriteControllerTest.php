<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use RefreshDatabase; // Ensures the database is reset after each test

    /** @test */
    public function it_can_create_a_recipe_with_ingredients()
    {
        // Create a recipe using the factory
        $recipe = Recipe::factory()->create();

        // Create ingredients for the recipe
        $ingredients = Ingredient::factory()->count(3)->create([
            'recipe_id' => $recipe->id,
        ]);

        // Assert that the recipe was created
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'label' => $recipe->label,
        ]);

        // Assert that the ingredients were created
        foreach ($ingredients as $ingredient) {
            $this->assertDatabaseHas('ingredients', [
                'recipe_id' => $recipe->id,
                'text' => $ingredient->text,
            ]);
        }
    }

}
