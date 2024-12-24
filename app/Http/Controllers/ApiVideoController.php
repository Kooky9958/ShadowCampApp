<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\KVP;
use App\Models\WatchVideo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiVideoController extends Controller
{
    /**
     * Fetch and return a video by its URL ID.
     *
     * @param Request $request
     * @param string $url_id The local ID for the video.
     * @return JsonResponse
     */
    public function get_single_video(Request $request, string $url_id, string $user_id): JsonResponse
    {
        // Validate the incoming parameters
        if (empty($url_id) || empty($user_id)) {
            return response()->json(['message' => 'Invalid URL or User ID'], 400);
        }

        try {
            // Attempt to retrieve the video
            $video = Video::where('url_id', $url_id)->first();

            // Check if the video was found
            if (is_null($video)) {
                return response()->json(['message' => 'Video not found'], 404);
            }
        if (is_null($video)) {
            // If the video does not exist, return an error response
            return response()->json([
                'status' => false,
                'message' => 'Video not found',
            ], 200);
        }

            // Check if the video is favorited by the user
            $isFavorite = DB::table('favorite_videos')
                ->where('user_id', $user_id)
                ->where('video_id', $url_id)
                ->exists();

            // Fetch related videos
            $relatedVideos = $this->fetchRelatedVideos($video);


            // Return the successful response
            return response()->json([
                'message' => 'Video fetched successfully',
                'video' => $video,
                'favorite' => $isFavorite,
                'related_videos' => $relatedVideos,
            ], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Error fetching video: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['message' => 'An error occurred while fetching the video'], 500);
        }
    }


    public function get_all_video(Request $request): JsonResponse
    {
        $validator = $this->validateAudience($request);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $videos = $this->queryVideos($request);

        return response()->json([
            'status' => 'Success',
            'message' => 'All videos fetched successfully',
            'count' => $videos->count(),
            'response' => $videos,
        ], 200);
    }

    public function getVideosByTags(Request $request, $tags): JsonResponse
    {
        $validator = $this->validateAudience($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $videos = $this->fetchVideosByTags($request->query('audience'), explode(',', $tags));

        return response()->json([
            'success' => true,
            'message' => 'Tag-wise Videos fetched successfully',
            'response' => $videos,
        ], 200);
    }

    public function getAllVideosByTags(Request $request): JsonResponse
    {
        // Validate the audience parameter
        $validator = $this->validateAudience($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed for audience parameter',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Check if 'audience' parameter is present in the request
            $audience = $request->query('audience');
            if (is_null($audience)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Audience parameter is required.',
                ], 400);
            }

            // Retrieve videos by audience tags
            $videos = Video::whereJsonContains('audience', $audience)->get();

            // Handle empty video result set
            if ($videos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No tags found for the specified audience.',
                    'response' => [],
                ], 200);
            }

            // Extract unique tags from the videos
            $allTags = array_unique(
                array_merge(
                    ...$videos->pluck('tags')
                    ->map(fn($tags) => !empty($tags) ? json_decode($tags, true) : [])
                    ->filter(fn($decodedTags) => is_array($decodedTags))
                    ->toArray()
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'All tags retrieved successfully',
                'response' => array_values($allTags),
            ], 200);

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error retrieving videos by tags: ' . $e->getMessage());

            // Return a generic error message
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving tags.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Private methods to reduce duplication
    private function validateAudience(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'audience' => 'required|string|in:precall,delta',
        ]);
    }

    private function fetchRelatedVideos(Video $video)
    {
        $videoTags = $video->tags ? json_decode($video->tags) : [];
        $videoName = $video->name;
        $videoPlaylistKey = optional(json_decode($video->playlist, true))[0] ?? null;

        return Video::query()
            ->where('id', '!=', $video->id)
            ->when(empty($videoTags), function ($query) use ($videoName, $videoPlaylistKey) {
                $query->where(fn($query) =>
                $query->where(DB::raw('LOWER(name)'), 'like', '%' . strtolower($videoName) . '%')
                    ->orWhere('playlist', 'like', '%' . ($videoPlaylistKey ?? '') . '%')
                );
            })
            ->when(!empty($videoTags), function ($query) use ($videoName, $videoTags, $videoPlaylistKey) {
                $query->where(fn($query) =>
                $query->whereJsonContains('tags', $videoTags)
                    ->orWhere(DB::raw('LOWER(name)'), 'like', '%' . strtolower($videoName) . '%')
                    ->orWhere('playlist', 'like', '%' . ($videoPlaylistKey ?? '') . '%')
                );
            })
            ->limit(4)
            ->get();
    }

    private function queryVideos(Request $request): \Illuminate\Database\Eloquent\Collection|array
    {
        $videos_query = Video::query();
        $audienceFilter = $request->input('audience', 'precall');

        $videos_query->whereJsonContains('audience', $audienceFilter);

        $searchTerm = $request->input('search', '');
        $tagFilter = $request->input('tagFilter', '');

         // Compulsory filter by audience
         if (!empty($audienceFilter)) {
            $videos_query->whereJsonContains('audience', $audienceFilter);
        }

        // Search by name, description, or tags
        if (!empty($searchTerm)) {
            $keywords = preg_split('/[\s,]+/', $searchTerm); // Split search term by spaces or commas

            foreach ($keywords as $keyword) {
                $videos_query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('tags', 'like', "%{$keyword}%"); // Searching in tags
                });
            }
        }

        // Filter by tags
        if (!empty($tagFilter)) {
            $tags = json_decode($tagFilter, true); // Decode the tagFilter from JSON
            if (is_array($tags)) {
                $videos_query->where(function ($query) use ($tags) {
                    foreach ($tags as $tag) {
                        $query->orWhere('tags', 'like', "%{$tag}%"); // Partial match for each tag
                    }
                });
            }
        }

        $sortDate = $request->input('sort_date', 'newest');
        if ($request->has('sort_duration')) {
            if ($request->input('sort_duration') === 'longest') {
                $videos_query->orderByRaw(
                    'CASE WHEN duration IS NOT NULL THEN 0 ELSE 1 END ASC, 
                    (HOUR(duration) * 3600 + MINUTE(duration) * 60 + SECOND(duration)) DESC'
                );
            } elseif ($request->input('sort_duration') === 'shortest') {
                $videos_query->orderByRaw(
                    'CASE WHEN duration IS NULL THEN 0 ELSE 1 END ASC, 
                    (HOUR(duration) * 3600 + MINUTE(duration) * 60 + SECOND(duration)) ASC'
                );
            }
        }
        $videos_query->orderBy('created_at', $sortDate === 'newest' ? 'desc' : 'asc');

        return $videos_query->get();
    }

    private function fetchVideosByTags($audience, array $tagsArray)
    {
        return Video::whereJsonContains('audience', $audience)
            ->where(function ($query) use ($tagsArray) {
                foreach ($tagsArray as $tag) {
                    $query->orWhere('tags', 'like', "%{$tag}%");
                }
            })
            ->get();
    }

    /**
     * Fetch and return all favorite videos for a user.
     *
     * @param Request $request
     * @param string $user_id The ID of the user.
     * @return JsonResponse
     */
    public function getFavoriteVideos(Request $request, string $user_id): JsonResponse
    {
        // Validate the incoming user_id
        if (empty($user_id)) {
            return response()->json(['message' => 'Invalid User ID'], 400);
        }

        try {
            // Fetch favorite videos
            $favoriteVideos = DB::table('favorite_videos')
                ->where('user_id', $user_id)
                ->pluck('video_id');

            // Fetch the corresponding video details with pagination
            $videos = Video::whereIn('url_id', $favoriteVideos)
                ->paginate($request->input('per_page', 10)); // Default to 10 items per page

            // Return response
            return response()->json([
                'message' => 'Favorite videos fetched successfully',
                'favorite_videos' => $videos,
            ], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Error fetching favorite videos: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['message' => 'An error occurred while fetching favorite videos'], 500);
        }
    }
    public function watch_video(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url_id' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'audience' => 'required|string|in:precall,delta',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'response' => [],
                'user' => [],
                'video' => []
            ], 422);
        }

        try {
            $user = User::findOrFail($request->input('user_id'));
            $video = Video::where('url_id', $request->input('url_id'))->firstOrFail();

            // Decode the audience and check if the requested audience is allowed
            $audienceArray = json_decode($video->audience, true);
            if (!is_array($audienceArray) || !in_array($request->input('audience'), $audienceArray)) {
                return response()->json([
                    'status' => false,
                    'message' => 'This video is not available for the requested audience.',
                    'response' => [],
                    'video' => [],
                    'user' => [],
                ], 403);
            }

            // Check if a watch record exists and either update or create it
            $watchVideo = WatchVideo::updateOrCreate(
                [
                    'url_id' => $request->input('url_id'),
                    'user_id' => $request->input('user_id'),
                ],
                [
                    'url_id' => $request->input('url_id'),
                    'user_id' => $request->input('user_id'),
                ]
            );

            // Determine the response message (created or updated)
            $message = $watchVideo->wasRecentlyCreated
                ? 'Watch video created successfully'
                : 'Watch video record updated successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
                'response' => $watchVideo,
                'user' => $user,
                'video' => $video
            ], $watchVideo->wasRecentlyCreated ? 201 : 200); // Return 201 Created for new records

        } catch (\Exception $e) {
            // Handle case where user or video is not found
            return response()->json([
                'status' => false,
                'message' => 'video not found',
                'response' => [],
                'video' => [],
                'user' => [],
            ], 200); // 200 OK
        }
    }

    public function get_watch_video(Request $request, $url_id)
    {
        try {
            // Fetch data by 'url_id'
            $watch_video = WatchVideo::where('url_id', $url_id)->with('user')->orderBy('created_at', 'desc')->get();

            // Count the number of records
            $count = $watch_video->count();

            // If no record is found, return a not found message
            if ($count == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No videos found for this URL ID',
                    'count' => $count,
                    'data' => [],
                ], 200);
            }

            // Return the filtered data
            return response()->json([
                'success' => true,
                'message' => 'Watch Video data retrieved successfully',
                'count' => $count,
                'data' => $watch_video,
            ], 200);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please provide a valid token.',
                'error' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Watch Video data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateCompletionStatus(Request $request, $url_id)
    {
        // Get the authenticated user via the token from the Authorization header
        $user = Auth::user(); // This will use the token from the request header automatically
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized user. Please check your token and try again.',
            ], 401);
        }

        $request->validate([
            'is_complete' => 'required|boolean',
        ]);

        // Fetch the entry in the watch_videos table by url_id and user_id (assuming you want to relate to a specific user)
        $watchVideo = WatchVideo::where('url_id', $url_id)->where('user_id', $user->id)->first(); // Ensure user is also checked

        if (is_null($watchVideo)) {
            return response()->json([
                'status' => false,
                'message' => 'Watch record not found',
            ], 404);
        }

        // Update the is_complete field
        $watchVideo->is_complete = $request->is_complete; // 1 or 0 based on the input
        $watchVideo->save();

        return response()->json([
            'status' => true,
            'message' => 'Watch video completion status updated successfully',
            'response' => $watchVideo,
        ], 200);
    }

}
