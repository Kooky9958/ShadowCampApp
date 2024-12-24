<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoPlaylist;
use Illuminate\Support\Facades\Auth;
use App\Models\WatchVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiVideoPlaylistController extends Controller
{
    /**
     * Render playlist watch view
     * 
     * @param Request $request
     * @param string $url_id Local ID for playlist
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_playlist_Videos(Request $request, $url_id)
    {
        $validator = Validator::make($request->all(), [
            'audience' => 'required|string|in:precall,delta', // Audience must be 'precall' or 'delta'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get the authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized user. Please check your token and try again.',
            ], 401);
        }

        $audience = $request->input('audience');

        $playlist = VideoPlaylist::where('url_id', $url_id)->first();
        
        if ($playlist) {
            $searchTerm = $request->input('search', '');
            $tagFilter = $request->input('tagFilter', '');
            $sortDate = $request->input('sort_date', '');

            $videos = $this->getPlaylistAllVideos($playlist, $searchTerm, $tagFilter, $sortDate, $audience);

            // Fetch watch_videos data based on the video URL IDs for the authenticated user
            $videoIds = $videos->pluck('url_id');
            $watch_videos = WatchVideo::whereIn('url_id', $videoIds)
                ->where('user_id', $user->id)
                ->get();

            return response()->json([
                'videos' => $videos,
                'playlist' => $playlist,
                'watch_videos' => $watch_videos,
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Playlist not found',
        ], 200);
    }

    protected function getPlaylistAllVideos(VideoPlaylist $playlist, $searchTerm = '', $tagFilter = '', $sortDate = '', $audience)
    {
        $url_id = $playlist->url_id;

        $videos_query = Video::where('playlist', 'like', "%\"{$url_id}\"%");

        if (!empty($audience)) {
            $videos_query->whereJsonContains('audience', $audience);
        }

        // Search by name, description, or tags
        if (!empty($searchTerm)) {
            $keywords = preg_split('/[\s,]+/', $searchTerm);
            foreach ($keywords as $keyword) {
                $videos_query->where(function($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('tags', 'like', "%{$keyword}%");
                });
            }
        }

        // Filter by tags
        if (!empty($tagFilter)) {
            $tags = json_decode($tagFilter, true);
            if (is_array($tags)) {
                $videos_query->where(function ($query) use ($tags) {
                    foreach ($tags as $tag) {
                        $query->orWhere('tags', 'like', "%{$tag}%");
                    }
                });
            }
        }

        // Sorting by date
        if ($sortDate === 'newest') {
            $videos_query->orderBy('created_at', 'desc');
        } elseif ($sortDate === 'oldest') {
            $videos_query->orderBy('created_at', 'asc');
        }

        // Fetch the sorted and filtered videos
        return $videos_query->get();
    }

    public function get_how_to_basic(Request $request, $url_id)
    {
        $audience = '';

        $playlist = VideoPlaylist::where('url_id', $url_id)->first();

        if ($playlist) {
            $searchTerm = $request->input('search', '');
            $tagFilter = $request->input('tagFilter', '');
            $sortDate = $request->input('sort_date', '');

            $videos = $this->getPlaylistAllVideos($playlist, $searchTerm, $tagFilter, $sortDate, $audience);

            return response()->json([
                'videos' => $videos,
                'playlist' => $playlist,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Playlist not found',
        ], 200);
    }

    public function get_all_playlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'audience' => 'required|string',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Initialize the video query
        $playlist_query = VideoPlaylist::query();

        // Get search term and tag filter, audience filter from the request
        $searchTerm = $request->input('search', '');
        $tagFilter = $request->input('tagFilter', '');
        $audienceFilter = $request->input('audience', 'precall'); // Default to 'precall' if not provided
        
        // Compulsory filter by audience
        if (!empty($audienceFilter)) {
            $playlist_query->whereJsonContains('audience', $audienceFilter);
        }

        // Search by name, description, or tags
        if (!empty($searchTerm)) {
            $keywords = preg_split('/[\s,]+/', $searchTerm); // Split search term by spaces or commas

            foreach ($keywords as $keyword) {
                $playlist_query->where(function($query) use ($keyword) {
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
                $playlist_query->where(function ($query) use ($tags) {
                    foreach ($tags as $tag) {
                        $query->orWhere('tags', 'like', "%{$tag}%"); // Match any of the tags
                    }
                });
            }
        }

        // Fetch the videos based on the query
        $playlist = $playlist_query->get();

        // Get the count of the playlist
        $videoCount = $playlist->count();

        // Return response
        return response()->json([
            'status' => 'Success',
            'message' => 'All playlist fetched successfully',
            'count' => $videoCount,
            'response' => $playlist,
        ], 200);
    }

    public function getAllVideoPlaylistByTags(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'audience' => 'required|string|in:precall,delta', // Audience must be 'precall' or 'delta'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $audience = $request->query('audience');

            $videoPlaylist = VideoPlaylist::whereJsonContains('audience', $audience)->get();

            $allTags = [];

            foreach ($videoPlaylist as $playlist) {
                $tags = json_decode($playlist->tags, true);

                if (is_array($tags)) {
                    $allTags = array_merge($allTags, $tags);
                }
            }

            $allTags = array_unique($allTags);

            return response()->json([
                'success' => true,
                'message' => 'All Tags Retrieved Successfully',
                'response' => array_values($allTags),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong while fetching tags.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getAllVideoPlaylistByTagsid(Request $request, $url_id)
    {
        // Find the playlist by url_id
        $playlist = VideoPlaylist::where('url_id', $url_id)->first();

        if (!$playlist) {
            return response()->json([
                'status' => false,
                'message' => 'Playlist not found',
            ], 200);
        }

        // Get the search term, tag filter, and sort date from the request
        $searchTerm = $request->query('search', '');
        $tagFilter = $request->query('tagFilter', '');
        $sortDate = $request->query('sort_date', '');

        $audience = $request->query('audience', '');

        // Get all videos in this playlist
        $videos = $this->getPlaylistAllVideos($playlist, $searchTerm, $tagFilter, $sortDate, $audience);

        // Extract unique tags from the videos
        $allTags = $this->extractTagsFromVideos($videos);

        return response()->json([
            'success' => true,
            'message' => 'All Tags Retrieved Successfully',
            'tags' => $allTags,
        ], 200);
    }

    protected function extractTagsFromVideos($videos)
    {
        $allTags = [];

        foreach ($videos as $video) {
            $tags = json_decode($video->tags, true);
            if (is_array($tags)) {
                $allTags = array_merge($allTags, $tags);
            }
        }

        $allTags = array_unique($allTags);

        return array_values($allTags);
    }


}
