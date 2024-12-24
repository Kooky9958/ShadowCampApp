<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecipeTest extends TestCase
{
    use RefreshDatabase; // This trait rolls back changes after each test

    /** @test */
    public function it_can_store_a_new_recipe_and_add_to_favorites()
    {
        // Create a user and authenticate them
        $user = User::factory()->create();
        $this->actingAs($user); // Set the user as the current authenticated user

        // Mock recipe data
        $data = [
            'label' => 'Basic White Rice',
            'thumbnail' => 'https://example.com/rice.jpg',
            'source' => 'Serious Eats',
            'recipe_url' => 'https://example.com/recipe',
            'yield' => 3,
            'health_labels' => ['Vegan', 'Low-Fat'],
            'calories' => 200,
            'cuisine_type' => ['American'],
            'meal_type' => ['Lunch'],
            'dish_type' => ['Main Course'],
        ];

        // Make a post request to the route responsible for storing recipes
        $response = $this->post('/recipes', $data);

        // Assert that the recipe is inserted into the recipes table
        $this->assertDatabaseHas('recipes', [
            'recipe_url' => 'https://example.com/recipe'
        ]);

        // Assert that a favorite is created for the authenticated user
        $this->assertDatabaseHas('favorite_recipes', [
            'user_id' => $user->id,
        ]);

        // Check for the successful response
        $response->assertStatus(201);
    }

    /** @test */
    public function it_does_not_create_duplicate_recipes()
    {
        // Create a user and authenticate them
        $user = User::factory()->create();
        $this->actingAs($user);

        // Mock existing recipe
        $recipe = Recipe::factory()->create([
            'label' => 'Basic White Rice',
            'thumbnail' => 'https://example.com/rice.jpg',
            'source' => 'Serious Eats',
            'recipe_url' => 'https://example.com/recipe',
            'yield' => 3,
        ]);

        // Send a post request with the same recipe data
        $data = [
            'label' => 'Basic White Rice', // Same label as the existing recipe
            'thumbnail' => 'https://example.com/rice.jpg',
            'source' => 'Serious Eats',
            'recipe_url' => 'https://example.com/recipe',
            'yield' => 3,
            'calories' => 200,
            'health_labels' => ['Vegan', 'Low-Fat'],
        ];

        $response = $this->post('/recipes', $data);

        // Assert that the recipe count in the database remains the same
        $this->assertCount(1, Recipe::all());

        // Assert that a favorite is created for the authenticated user
        $this->assertDatabaseHas('favorite_recipes', [
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        $response->assertStatus(201);
    }
}
