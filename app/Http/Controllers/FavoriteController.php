<?php

namespace App\Http\Controllers;

use App\Models\FavoriteRecipe;
use App\Models\FavoriteVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    protected \App\Http\Controllers\RecipeController $recipeController;

    public function __construct(RecipeController $recipeController)
    {
        $this->recipeController = $recipeController;
    }

    /**
     * Add content to the user's favorites.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        Log::info('Payload received:', $request->all());

        // Validate request
        try {
            $request->validate([
                'content_id' => 'required|string',
                'user_id' => 'required|exists:users,id',
                'content_type' => 'required|in:recipe,video',
                'uri' => 'required_if:content_type,recipe|string|url',
                'label' => 'required_if:content_type,recipe|string',

                // Updated from 'thumbnail' to 'images'
                'images' => 'nullable|array',
                'images.THUMBNAIL.url' => 'nullable|url',
                'images.THUMBNAIL.width' => 'nullable|integer',
                'images.THUMBNAIL.height' => 'nullable|integer',
                'images.SMALL.url' => 'nullable|url',
                'images.SMALL.width' => 'nullable|integer',
                'images.SMALL.height' => 'nullable|integer',
                'images.REGULAR.url' => 'nullable|url',
                'images.REGULAR.width' => 'nullable|integer',
                'images.REGULAR.height' => 'nullable|integer',
                'images.LARGE.url' => 'nullable|url',
                'images.LARGE.width' => 'nullable|integer',
                'images.LARGE.height' => 'nullable|integer',

                'source' => 'nullable|string',
                'url' => 'nullable|url',
                'yield' => 'nullable|integer',
                'calories' => 'nullable|numeric',

                // Health labels, cuisine, meal, and dish types as arrays
                'healthLabels' => 'nullable|array',
                'cuisineType' => 'nullable|array',
                'mealType' => 'nullable|array',
                'dishType' => 'nullable|array',

                // Ingredient lines
                'ingredientLines' => 'nullable|array',

                // Ingredients as an array with structured fields
                'ingredients' => 'nullable|array',
                'ingredients.*.text' => 'nullable|string',
                'ingredients.*.quantity' => 'nullable|numeric',
                'ingredients.*.measure' => 'nullable|string',
                'ingredients.*.food' => 'nullable|string',
                'ingredients.*.weight' => 'nullable|numeric',
                'ingredients.*.foodId' => 'nullable|string',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation errors: ', $e->errors());
            return response()->json($e->errors(), 422);
        }

        DB::beginTransaction();
        try {
            // Handle recipe favorite logic
            if ($request->content_type === 'recipe') {
                $recipe = $this->recipeController->createOrUpdateRecipe($request->all());
                if (!$recipe) {
                    return response()->json(['message' => 'Failed to create or update the recipe.'], 500);
                }

                $favorite = FavoriteRecipe::where('user_id', $request->user_id)
                    ->where('recipe_uri', $recipe->uri)
                    ->first();

                if ($favorite) {
                    return response()->json(['message' => 'This recipe is already in your favorites.'], 409);
                }

                FavoriteRecipe::create([
                    'user_id' => $request->user_id,
                    'recipe_uri' => $recipe->uri,
                ]);
            } elseif ($request->content_type === 'video') {
                if (!$request->content_id) {
                    return response()->json(['message' => 'Content ID is required for videos.'], 400);
                }

                $favorite = FavoriteVideo::where('user_id', $request->user_id)
                    ->where('video_id', $request->content_id)
                    ->first();

                if ($favorite) {
                    return response()->json(['message' => 'This video is already in your favorites.'], 409);
                }

                FavoriteVideo::create([
                    'user_id' => $request->user_id,
                    'video_id' => $request->content_id,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Content added to favorites.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding to favorites: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => $request->user_id,
            ]);
            return response()->json(['message' => 'An error occurred while adding to favorites.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all favorites for a user.
     */
    public function index($userId): \Illuminate\Http\JsonResponse
    {
        $favoriteRecipes = FavoriteRecipe::where('user_id', $userId)->get();
        $favoriteVideos = FavoriteVideo::where('user_id', $userId)->get();

        return response()->json([
            'recipes' => $favoriteRecipes,
            'videos' => $favoriteVideos,
        ]);
    }

    public function getAllFavorites($userId): \Illuminate\Http\JsonResponse
    {
        try {
            $favoriteRecipes = FavoriteRecipe::where('user_id', $userId)->get();
            $favoriteVideos = FavoriteVideo::where('user_id', $userId)->get();

            return response()->json([
                'recipes' => $favoriteRecipes,
                'videos' => $favoriteVideos,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching favorites: ' . $e->getMessage(), ['user_id' => $userId]);
            return response()->json(['message' => 'An error occurred while fetching favorites.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific favorite by ID.
     */
    public function show($id): \Illuminate\Http\JsonResponse
    {
        $favoriteRecipe = FavoriteRecipe::find($id);
        $favoriteVideo = FavoriteVideo::find($id);

        if (!$favoriteRecipe && !$favoriteVideo) {
            return response()->json(['message' => 'Favorite not found.'], 404);
        }

        return response()->json($favoriteRecipe ?? $favoriteVideo);
    }

    /**
     * Delete a specific favorite.
     */
    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        // Extract parameters from the request
        $userId = $request->input('user_id');
        $contentId = $request->input('content_id');
        $contentType = $request->input('content_type');

        $favorite = null;

        if ($contentType === 'recipe') {
            $favorite = FavoriteRecipe::where([
                ['user_id', $userId],
                ['recipe_uri', $contentId],
            ])->first();
        } elseif ($contentType === 'video') {
            $favorite = FavoriteVideo::where([
                ['user_id', $userId],
                ['video_id', $contentId],
            ])->first();
        }

        if (!$favorite) {
            return response()->json(['message' => 'Favorite not found.'], 404);
        }

        try {
            $favorite->delete();
            return response()->json(['message' => 'Content removed from favorites.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting favorite: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting favorite.', 'error' => $e->getMessage()], 500);
        }
    }
}
