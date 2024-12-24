<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class ReactionController extends Controller
{
    public function __construct()
    {
        // Apply rate limiting on the addOrUpdateReaction and removeReaction methods
        $this->middleware('throttle:10,1')->only(['addOrUpdateReaction', 'removeReaction']); // Adjust limit and duration as needed
    }

    public function addOrUpdateReaction(Request $request): JsonResponse
    {
        // Validate the request to include user_id, comment_id, and emoji
        $request->validate([
            'userId' => 'required|integer|exists:users,id',
            'commentId' => 'required|integer|exists:comments,id',
            'emoji' => 'nullable|string|max:20',
        ]);

        // Get the validated data
        $userId = $request->input('userId');
        $commentId = $request->input('commentId');
        $emoji = $request->input('emoji');

        // Cache key to prevent duplicate requests
        $cacheKey = "reaction_{$userId}_{$commentId}";

        // Check if there's already a reaction being processed for this user and comment
        if (Cache::has($cacheKey)) {
            return response()->json(['message' => 'Reaction request already in process'], 429);
        }

        // Cache this reaction request for a short period to prevent duplicate processing
        Cache::put($cacheKey, true, now()->addSeconds(2));

        // Find the comment by ID
        $comment = Comment::findOrFail($commentId);

        // Add or update the reaction
        $reaction = Reaction::updateOrCreate(
            ['user_id' => $userId, 'comment_id' => $comment->id],
            ['emoji' => $emoji]
        );

        // Remove the cache key after processing
        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Reaction added/updated successfully',
            'reaction' => $reaction,
        ]);
    }

    public function removeReaction(Request $request): JsonResponse
    {
        // Validate the request to include user_id and comment_id
        $request->validate([
            'userId' => 'required|integer|exists:users,id',
            'commentId' => 'required|integer|exists:comments,id',
        ]);

        $userId = $request->input('userId');
        $commentId = $request->input('commentId');

        // Cache key to prevent duplicate requests
        $cacheKey = "reaction_remove_{$userId}_{$commentId}";

        // Check if there's already a removal request being processed
        if (Cache::has($cacheKey)) {
            return response()->json(['message' => 'Reaction removal request already in process'], 429);
        }

        // Cache this removal request for a short period to prevent duplicate processing
        Cache::put($cacheKey, true, now()->addSeconds(2));

        // Delete the reaction
        $deleted = Reaction::where('user_id', $userId)
            ->where('comment_id', $commentId)
            ->delete();

        // Remove the cache key after processing
        Cache::forget($cacheKey);

        if ($deleted) {
            return response()->json(['message' => 'Reaction removed successfully']);
        }

        return response()->json(['message' => 'No reaction found to delete'], 404);
    }
}
