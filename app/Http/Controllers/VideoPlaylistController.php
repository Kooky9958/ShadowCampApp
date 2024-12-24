<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoPlaylist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class VideoPlaylistController extends Controller
{
    /**
     * Get generic variables for passing down to the view
     *
     * @param string $audience Audience ID
     * @return array Generic variables to be passed down to the view
     */
    private static function getGenericVarsForView($audience = null)
    {
        $audience_determined = \App\Helpers\CustomHelper::determineAudience($audience);

        return [
            'audience_determined' => $audience_determined,
            'playlists_filter_by' => self::getPlaylistsForAudience($audience_determined, true),
        ];
    }

    /**
     * Retrieve all playlists for the given audience
     *
     * @param string $audience Audience
     * @param bool $excludeHidden Exclude hidden playlists
     * @return \Illuminate\Support\Collection
     */
    public static function getPlaylistsForAudience($audience, $excludeHidden = false)
    {
        $playlists = VideoPlaylist::where('audience', 'like', "%\"$audience\"%")->get();

        if ($excludeHidden === true) {
            $playlists = $playlists->filter(function ($playlist) {
                return $playlist->shouldDisplay();
            });
        }

        return $playlists;
    }

    /**
     * Get all available playlists
     *
     * @return array The available playlists
     */
    public static function getAllPlaylists()
    {
        return VideoPlaylist::all();
    }

    /**
     * Create playlist
     *
     * @param Illuminate\Http\Request $request
     */
    public static function create(Request $request)
    {
        // Fetch unique tags from all videos
        $existingTags = VideoPlaylist::all()->flatMap(function ($video) {
            return json_decode($video->tags, true);
        })->unique();

        if ($request->isMethod('post')) {
            // Init
            $input_release_relative_persistent = ($request->input('release_relative_persistent') == 'on') ? true : false;

            $request->validate([
                'coverimage_file' => 'image|max:102400', // Set to 100MB
                'tags' => 'nullable|array',
                'tags.*'          => 'string|max:255',
            ]);

            // Store files
            $coverimage_path = null;
            if ($request->file('coverimage_file') != null) {
                $cloudflare_response = self::uploadToCloudflare($request->file('coverimage_file'));
                if ($cloudflare_response['success'] === true) {
                    $coverimage_path = $cloudflare_response['result']['variants'][0];
                }
            }

            // Create Transaction
            $playlist = VideoPlaylist::create([
                'name'                        => $request->input('name'),
                'description'                 => $request->input('description'),
                'tags'                        => json_encode($request->input('tags')),
                'audience'                    => json_encode([$request->input('audience')]),
                'published'                   => true,
                'release_relative_day'        => $request->input('release_relative_day'),
                'release_relative_persistent' => $request->input('release_relative_persistent') == 'on',
                'coverimage_path'             => $coverimage_path,
            ]);

            $playlist->url_id       = \App\Helpers\CustomHelper::generateUrlId($request->input('name'));
            $playlist->date_created = now();
            $playlist->save();

            return view('feedback', [
                'title'   => 'Video Playlist Create Success',
                'message' => 'Video playlist created successfully.',
            ]);
        }

        // If it's a GET request, show the form
        return view('admin.create_playlist', compact('existingTags'));
    }

    private static function uploadToCloudflare($file)
    {
        // Upload to Cloudflare
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('CLOUDFLARE_IMAGE_TOKEN'),
        ])->attach(
            'file',
            file_get_contents($file->getRealPath()),
            "Shadow_".$file->getClientOriginalName()
        )->post('https://api.cloudflare.com/client/v4/accounts/' . env('CLOUDFLARE_ACCOUNT_ID') . '/images/v1');
        if ($response->successful()) {
            return $response->json();
        }
        return [
            'error' => true,
            'message' => 'Failed to upload image to Cloudflare',
            'details' => $response->json(),
        ];
    }

    public function edit($id)
    {
        $playlist = VideoPlaylist::find($id);

        // Get the URL ID
        $urlId = $playlist->url_id;

        // Fetch videos where the playlist contains the specific URL ID
        $videos = Video::whereJsonContains('playlist', [$urlId => (object) []])->orderByRaw('position IS NULL OR position = 0 DESC')->orderBy('position', 'ASC')->paginate(50);

        // Retrieve all videos for dropdown
        $allVideos = Video::all();

        // Retrieve all tags from VideoPlaylist
        $playlistTags = VideoPlaylist::all()->flatMap(function ($videoPlaylist) {
            return json_decode($videoPlaylist->tags, true);
        });

        // Retrieve all tags from Video
        $videoTags = Video::all()->flatMap(function ($video) {
            return json_decode($video->tags, true);
        });

        // Combine both tag collections and ensure uniqueness
        $allTags = $playlistTags->merge($videoTags)->unique();

        return view('admin.edit_playlist', compact('playlist', 'allTags', 'videos', 'allVideos'));
    }

    public function update(Request $request, $id)
    {
        // Validate the input
        $request->validate([
            'coverimage_file' => 'nullable|image|max:32678',
            'tags'            => 'nullable|array',
            'tags.*'          => 'string|max:255',
        ]);

        // Find the video playlist to update
        $playlist = VideoPlaylist::find($id);
        if (!$playlist) {
            return view('error', [
                'title'   => 'Video Not Found',
                'message' => 'The video you are trying to update does not exist.',
            ]);
        }

        $coverimage_path = null;

        // Handle file storage
        if ($request->file('coverimage_file')) {
            // Upload the new image to Cloudflare
            $cloudflare_response = self::uploadToCloudflare($request->file('coverimage_file'));
            
            // Check for a successful response
            if ($cloudflare_response['success'] === true) {
                $coverimage_path = $cloudflare_response['result']['variants'][0]; // Get the image URL
            } else {
                return view('error', [
                    'title'   => 'Image Upload Failed',
                    'message' => 'There was an error uploading the cover image to Cloudflare.',
                ]);
            }
        }

        // Prepare the update data
        $updateData = [
            'name'                        => $request->input('name'),
            'description'                 => $request->input('description'),
            'audience'                    => $request->input('audience') ? json_encode([$request->input('audience')]) : $playlist->audience,
            'tags'                        => $request->input('tags') ? json_encode(array_values($request->input('tags'))) : $playlist->tags,
            'published'                   => true,
            'release_relative_day'        => $request->input('release_relative_day'),
            'release_relative_persistent' => $request->has('release_relative_persistent'),
            'coverimage_path'             => $coverimage_path,
        ];

        // Update the video playlist
        $playlist->update($updateData);

        return view('feedback', ['title' => 'VideoPlaylist Update Success', 'message' => 'VideoPlaylist was updated successfully.']);
    }
    public function video_update(Request $request, $id)
    {
        $request->validate([
            'video_id' => 'required|exists:videos,id',
        ]);

        // Find the specified playlist
        $playlist = VideoPlaylist::findOrFail($id);

        // Fetch the existing video by video_id (but we don't update it)
        $existingVideo = Video::findOrFail($request->input('video_id'));

        // Create the JSON structure for the playlist field
        $playlistData = [
            $playlist->url_id => [ // Use the playlist's url_id as the key
                'pos' => '4', // You can customize this value or get it dynamically
            ],
        ];

        // Create a new video record with the required fields
        $newVideo = Video::create([
            'name' => $request->input('name', $existingVideo->name),
            'description' => $request->input('description', $existingVideo->description),
            'tags' => json_encode($request->input('tags', json_decode($existingVideo->tags))),
            'audience' => json_encode([$request->input('audience', json_decode($existingVideo->audience))]),
            'published' => true,
            'release_relative_day' => $request->input('release_relative_day', $existingVideo->release_relative_day),
            'release_relative_persistent' => $request->has('release_relative_persistent') ? 
                                            $request->input('release_relative_persistent') == 'on' : 
                                            $existingVideo->release_relative_persistent,
            'playlist' => json_encode($playlistData), // Store the playlist data in the correct format
        ]);

        // Save the new video
        $newVideo->save();

        // Redirect back with a success message
        return redirect()->route('video_playlist.edit', $playlist->id)
                        ->with('success', 'New video added to playlist successfully!');
    }

     /**
     * Delete a specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $model_class_name The name of the model class
     * @param int $id The ID of the model to delete
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $model_class_name, $id)
    {
        $videoPlaylist = VideoPlaylist::find($id);
        $videoPlaylist->delete();

        // Redirect or return a response
        return redirect()->back()->with('status', 'videoplaylists deleted successfully!');
    }

    /**
     * Render playlist watch view
     *
     * @param Request POST request
     * @param string $url_id Local ID for playlist
     */
    public static function watch($request, $url_id)
    {
        // Init
        $generic_vars_for_view = self::getGenericVarsForView();

        $playlist = VideoPlaylist::where('url_id', $url_id)->first();

        $vars_for_view = array_merge($generic_vars_for_view, [
            'videos'   => $playlist->getPlaylistVideos(),
            'playlist' => $playlist,
        ]);

        return view('video/playlist', $vars_for_view);
    }

    /**
     * Render virtual playlist for given audience
     *
     * @param Request POST request
     * @param string $name Virtual playlist name
     * @param string $audience Audience ID
     */
    public static function watchVirtualPlaylist($request, $name, $audience = null)
    {
        //Init
        $generic_vars_for_view = self::getGenericVarsForView($audience);
        $videos                = [];

        // Prepare playlist videos
        switch ($name) {
            case 'workouts':
                $playlist = VideoPlaylist::getPlaylist('virt!!workouts');

                $videos = Video::where('audience', 'like', "%\"{$generic_vars_for_view['audience_determined']}\"%")
                    ->where('published', '=', '1')
                    ->get();
                break;

            default:
                // code...
                break;
        }

        $vars_for_view = array_merge($generic_vars_for_view, [
            'disable_filter_by_playlists' => false,
            'videos'                      => $videos,
            'playlist'                    => $playlist,
        ]);

        return view('video/playlist', $vars_for_view);
    }

    /**
     * Render list of playlists for given audience
     *
     * @param Request POST request
     * @param string $audience Audience ID for list of playlists
     */
    public static function viewPlaylistsForAudience($request, $audience = null)
    {
        $audience_determined = \App\Helpers\CustomHelper::determineAudience($audience);

        return view('video/list_playlists', ['playlists' => self::getPlaylistsForAudience($audience_determined)]);
    }

    public function delete_video($videoId, $playlistId)
    {
        $video = Video::find($videoId);
        if ($video) {
            $playlist         = VideoPlaylist::findOrFail($playlistId);
            $video            = Video::findOrFail($videoId);
            $existingPlaylist = json_decode($video->playlist, true) ?? [];
            $newUrlId         = $playlist->url_id;
            if (!empty($existingPlaylist)) {
                $firstKey = array_key_first($existingPlaylist);
                $position = $existingPlaylist[$firstKey]['pos'];
                $position--;
                unset($existingPlaylist[$firstKey]);
                $existingPlaylist[$firstKey] = ['pos' => $position];
            }
            $video->playlist = json_encode($existingPlaylist);
            $video->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Video not found'], 404);
    }

    public function saveOrder(Request $request)
    {
        $recordsPerPage = 50;
        $order = $request->input('order');
        foreach ($order as $index => $video) {
            $videoId = $video['id'];
            $position = $video['position'];
            $currentPage = $video['page'];
            $globalPosition = ($currentPage - 1) * $recordsPerPage + $position;

            $video = Video::find($videoId);
            if ($video) {
                $video->position = $globalPosition;
                $video->save();
            }
        }

        return response()->json(['success' => true]);
    }

}
