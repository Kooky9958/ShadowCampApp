<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate($this->validationRules());

        // Ensure either `newComment` or `gif` is present, otherwise return error
        if (empty($validated['newComment']) && empty($validated['gif'])) {
            return response()->json(['error' => 'Either comment text or GIF is required.'], 422);
        }

        try {
            // Log the received GIF data if present
            if (!empty($validated['gif'])) {
                Log::info('GIF data received', [
                    'gif' => $validated['gif'],
                ]);
            } else {
                Log::info('No GIF data received');
            }

            // Prepare comment data
            $commentData = $this->prepareCommentData($validated);

            // Create the new comment and store it in the database
            $comment = Comment::create($commentData);

            DB::commit(); // Ensure transaction management

            // Return success response with comment ID
            return response()->json([
                'message' => 'Comment added successfully!',
                'comment' => $comment,
            ], 201);
        } catch (ValidationException $ve) {
            Log::error("Validation failed for storing comment", [
                'errors' => $ve->validator->errors(),
                'data' => $request->all(),
            ]);
            return response()->json(['error' => 'Validation failed', 'details' => $ve->validator->errors()], 422);
        } catch (\Throwable $e) {
            Log::error("Failed to store comment", [
                'error' => $e->getMessage(),
                'data' => $validated,
                'user_id' => $validated['userId'],
            ]);
            return response()->json(['error' => 'Failed to add comment. Please try again.'], 500);
        }
    }

    public function fetchCommentsFor(
        string $contentType,
        string $contentId,
        int $depth = 3,
        string $sortOrder = 'desc',
        int $page = 1,
        int $perPage = 10
    ): JsonResponse {
        if (!$this->isValidSortOrder($sortOrder)) {
            return response()->json(['error' => 'Invalid sort order. Use "asc" or "desc".'], 400);
        }

        try {
            $comments = $this->getPaginatedComments($contentType, $contentId, $depth, $sortOrder, $page, $perPage);

            if ($comments->isEmpty()) {
                return response()->json(['message' => 'No comments found for the specified content.'], 404);
            }

            return response()->json($comments, 200);
        } catch (\Throwable $e) {
            $this->logError($e, $contentType, $contentId);
            return response()->json(['error' => 'Failed to retrieve comments. Please try again later.'], 500);
        }
    }

    private function isValidSortOrder(string $sortOrder): bool
    {
        return in_array($sortOrder, ['asc', 'desc']);
    }

    private function getPaginatedComments(
        string $contentType,
        string $contentId,
        int $depth,
        string $sortOrder,
        int $page,
        int $perPage
    ) {
        $withRelations = $this->buildRepliesWithRelations($depth);

        // Add 'reactions' relationship with summary counts
        return Comment::where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->whereNull('parent_comment_id')
            ->with(array_merge($withRelations, ['reactions'])) // Fetch the reactions for each comment
            ->orderBy('created_at', $sortOrder)
            ->paginate($perPage);
    }

    private function buildRepliesWithRelations(int $depth): array
    {
        $relations = ['user:id,name,profile_photo_path', 'reactions']; // Change reactionsSummary to reactions

        $currentLevel = &$relations;
        for ($i = 1; $i <= $depth; $i++) {
            $currentLevel['replies'] = [
                'user:id,name,profile_photo_path',
                'reactions' // Change reactionsSummary to reactions
            ];
            $currentLevel = &$currentLevel['replies'];
        }

        return $relations;
    }

    private function logError(\Throwable $e, string $contentType, string $contentId): void
    {
        Log::error("Failed to retrieve comments", [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * Validation rules for comments.
     *
     * @return array
     */
    private function validationRules(): array
    {
        return [
            'userId' => 'required|integer|exists:users,id',
            'contentId' => 'required|string',
            'contentType' => 'required|string',
            'parentCommentId' => 'nullable|integer|exists:comments,id',
            'newComment' => 'nullable|string|max:500|required_without:gif', // required if gif is not provided
            'gif' => 'nullable|array|required_without:newComment', // required if newComment is not provided
            'gif.gifUrl' => 'nullable|string|url', // Ensure valid gif URL if provided
            'gif.gifWidth' => 'nullable|integer',
            'gif.gifHeight' => 'nullable|integer',
        ];
    }

    /**
     * Prepare the data for a comment.
     *
     * @param array $validated
     * @return array
     */
    private function prepareCommentData(array $validated): array
    {
        return [
            'user_id' => $validated['userId'],
            'content_id' => $validated['contentId'],
            'content_type' => $validated['contentType'],
            'parent_comment_id' => $validated['parentCommentId'] ?? null,
            'comment_text' => $validated['newComment'] ?? null,
            'gif' => $validated['gif'] ?? null,
        ];
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'commentId' => 'required|integer|exists:comments,id',
            'commentText' => 'required|string|max:255',
            'gif' => 'nullable|array',
            'gif.gifUrl' => 'nullable|string|url',
            'gif.gifWidth' => 'nullable|integer',
            'gif.gifHeight' => 'nullable|integer',
        ]);

        $comment = Comment::findOrFail($validated['commentId']);

        // Authorization
        $this->authorize('update', $comment);

        DB::beginTransaction();
        try {
            $updateData = [
                'comment_text' => $validated['commentText'],
            ];

            if (!empty($validated['gif'])) {
                $updateData['gif'] = $validated['gif'];
            }

            $comment->update($updateData);
            DB::commit();

            return response()->json([
                'message' => 'Comment updated successfully!',
                'comment_id' => $comment->id,
            ], 200);
        } catch (ValidationException $ve) {
            DB::rollBack();
            Log::error("Validation failed for updating comment", [
                'errors' => $ve->validator->errors(),
                'data' => $request->all(),
            ]);
            return response()->json(['error' => 'Validation failed', 'details' => $ve->validator->errors()], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to update comment", [
                'comment_id' => $validated['commentId'] ?? null,
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);
            return response()->json(['error' => 'Failed to update comment. Please try again.'], 500);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        // Authorization
        $this->authorize('delete', $comment);

        DB::beginTransaction();
        try {
            $comment->delete();
            DB::commit();

            return response()->json(['message' => 'Comment deleted successfully'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to delete comment", [
                'comment_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to delete comment. Please try again.'], 500);
        }
    }



}
