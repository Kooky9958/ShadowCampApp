<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecipeController extends Controller
{
    /**
     * @throws \Exception
     */
    public function createOrUpdateRecipe(array $recipeData)
    {
        // Start a database transaction
        DB::beginTransaction();
        try {
            // Check if the recipe already exists by 'uri'
            $recipe = Recipe::where('uri', $recipeData['uri'] ?? null)->first();

            // Prepare data for recipe creation or update
            $recipeFields = [
                'uri' => $recipeData['uri'], // Ensure this is unique and included
                'label' => $recipeData['label'],
                'source' => $recipeData['source'] ?? null,
                'url' => $recipeData['url'] ?? null,
                'yield' => $recipeData['yield'] ?? null,
                'health_labels' => $recipeData['healthLabels'] ?? [], // Pass directly as an array
                'calories' => $recipeData['calories'] ?? null,
                'cuisine_type' => $recipeData['cuisineType'] ?? [], // Pass directly as an array
                'meal_type' => $recipeData['mealType'] ?? [], // Pass directly as an array
                'dish_type' => $recipeData['dishType'] ?? [], // Pass directly as an array
                'ingredient_lines' => $recipeData['ingredientLines'] ?? [], // Pass directly as an array
            ];

            // Handle the images (formerly thumbnail)
            if (isset($recipeData['images'])) {
                $recipeFields['images'] = [
                    'THUMBNAIL' => $recipeData['images']['THUMBNAIL'] ?? null,
                    'SMALL' => $recipeData['images']['SMALL'] ?? null,
                    'REGULAR' => $recipeData['images']['REGULAR'] ?? null,
                    'LARGE' => $recipeData['images']['LARGE'] ?? null,
                ];
            }

            if (!$recipe) {
                // If the recipe doesn't exist, create it
                $recipe = Recipe::create($recipeFields);
            } else {
                // If the recipe exists, update it as needed
                $recipe->update($recipeFields);
            }

            // Add or update ingredients
            if (isset($recipeData['ingredients'])) {
                // Clear existing ingredients or handle them as needed
                $recipe->ingredients()->delete(); // Optional: clear existing ingredients

                foreach ($recipeData['ingredients'] as $ingredient) {
                    if (!isset($ingredient['text'])) {
                        continue; // Skip this ingredient if 'text' is missing
                    }

                    Ingredient::create([
                        'recipe_id' => $recipe->id,
                        'text' => $ingredient['text'],
                        'quantity' => $ingredient['quantity'] ?? null,
                        'measure' => $ingredient['measure'] ?? null,
                        'food' => $ingredient['food'] ?? null,
                        'weight' => $ingredient['weight'] ?? null,
                        'foodId' => $ingredient['foodId'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return $recipe;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating or updating recipe: ' . $e->getMessage(), [
                'request_data' => $recipeData, // Log the incoming data for debugging
            ]);
            throw new \Exception('Error creating or updating recipe: ' . $e->getMessage());
        }
    }

    /**
     * Fetch all recipes.
     *
     * @param $recipe
     * @param $userId
     * @return array
     */
    // Extracted method to format the recipe and check if it's favorited
    private function formatRecipe($recipe, $userId): array
    {
        $isFavorited = DB::table('favorite_recipes')
            ->where('user_id', $userId)
            ->where('recipe_uri', $recipe->uri)
            ->exists();

        return [
            'recipe' => [
                'uri' => $recipe->uri,
                'label' => $recipe->label,
                'images' => [
                    'LARGE' => [
                        'url' => $recipe->images['LARGE']['url'] ?? '',
                        'width' => $recipe->images['LARGE']['width'] ?? 0,
                        'height' => $recipe->images['LARGE']['height'] ?? 0,
                    ],
                    'SMALL' => [
                        'url' => $recipe->images['SMALL']['url'] ?? '',
                        'width' => $recipe->images['SMALL']['width'] ?? 0,
                        'height' => $recipe->images['SMALL']['height'] ?? 0,
                    ],
                    'REGULAR' => [
                        'url' => $recipe->images['REGULAR']['url'] ?? '',
                        'width' => $recipe->images['REGULAR']['width'] ?? 0,
                        'height' => $recipe->images['REGULAR']['height'] ?? 0,
                    ],
                    'THUMBNAIL' => [
                        'url' => $recipe->images['THUMBNAIL']['url'] ?? '',
                        'width' => $recipe->images['THUMBNAIL']['width'] ?? 0,
                        'height' => $recipe->images['THUMBNAIL']['height'] ?? 0,
                    ],
                ],
                'source' => $recipe->source,
                'url' => $recipe->url,
                'yield' => $recipe->yield,
                'healthLabels' => $recipe->health_labels,
                'calories' => $recipe->calories,
                'cuisineType' => $recipe->cuisine_type,
                'mealType' => $recipe->meal_type,
                'dishType' => $recipe->dish_type,
                'ingredientLines' => $recipe->ingredient_lines,
                'ingredients' => $recipe->ingredients->map(function ($ingredient) {
                    return [
                        'text' => $ingredient->text,
                        'quantity' => $ingredient->quantity,
                        'measure' => $ingredient->measure,
                        'food' => $ingredient->food,
                        'weight' => $ingredient->weight,
                        'foodId' => $ingredient->foodId,
                    ];
                })
            ],
            'favorite' => $isFavorited, // Set favorite to true or false based on the check
        ];
    }

    // Function to fetch all recipes
    public function fetchAllRecipes(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id(); // Get the authenticated user's ID
            $recipes = Recipe::with('ingredients')->get(); // Fetch all recipes with ingredients

            // Format the recipes using the extracted method
            $formattedRecipes = $recipes->map(function ($recipe) use ($userId) {
                return $this->formatRecipe($recipe, $userId);
            });

            return response()->json($formattedRecipes, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching recipes: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch recipes'], 500);
        }
    }

    // Function to fetch only the user's favorite recipes
    public function fetchFavoriteRecipes(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id(); // Get the authenticated user's ID

            // Fetch only the recipes that are favorited by the user
            $favoriteRecipeIds = DB::table('favorite_recipes')
                ->where('user_id', $userId)
                ->pluck('recipe_uri'); // Get the list of favorite recipe URIs

            $recipes = Recipe::with('ingredients')
                ->whereIn('uri', $favoriteRecipeIds)
                ->get();

            // Format the favorite recipes using the extracted method
            $formattedRecipes = $recipes->map(function ($recipe) use ($userId) {
                return $this->formatRecipe($recipe, $userId);
            });

            return response()->json($formattedRecipes, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching favorite recipes: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch favorite recipes'], 500);
        }
    }
}
