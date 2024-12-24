<?php

namespace App\Http\Controllers;

use App\Helpers\DsofeirLaravelHelper;
use App\Models\KVP;
use App\Models\Video;
use App\Models\VideoPlaylist;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /**
     * Create video from upload
     *
     * @param Illuminate\Http\Request $request
     */
    public static function upload(Request $request)
    {
        // Fetch unique tags from all videos
        $existingTags = Video::all()->flatMap(function ($video) {
            return json_decode($video->tags, true);
        })->unique();

        if ($request->isMethod('post')) {
            // Init
            $input_release_relative_persistent = ($request->input('release_relative_persistent') == 'on') ? true : false;

            $request->validate([
                'coverimage_file' => 'image|max:102400',
                'tags'            => 'nullable|array',
                'tags.*'          => 'string|max:255',
            ]);

            // Get video upload metadata
            $kvp_video_upload_metadata = KVP::where('type', 'cloudflare_video_upload_metadata')->where('key', $request->input('session_uuid'))->first();
            if ($kvp_video_upload_metadata == null) {
                return view('error', ['title' => 'Video file not uploaded', 'message' => 'Video file was not uploaded.']);
            }
            $kvp_video_upload_metadata_value_jdcode = json_decode($kvp_video_upload_metadata->value, true);

            // Store files
            $coverimage_path = null;
            if ($request->file('coverimage_file') != null) {
                // Upload to Cloudflare
                $cloudflare_response = self::uploadToCloudflare($request->file('coverimage_file'));
                if ($cloudflare_response['success'] === true) {
                    $coverimage_path = $cloudflare_response['result']['variants'][0];
                }
            }

            // Find playlist position
            $playlist_pos = ($request->input('playlist_pos') != null) ? $request->input('playlist_pos') : Video::where('playlist', 'like', '%' . $request->input('playlist') . '%')->count() + 20;

            // Create Video
            $video = Video::create([
                'name'                        => $request->input('name'),
                'description'                 => $request->input('description'),
                'audience'                    => json_encode([$request->input('audience')]),
                'playlist'                    => json_encode([$request->input('playlist') => ['pos' => $playlist_pos]]),
                'tags'                        => json_encode($request->input('tags')),
                'published'                   => true,
                'release_relative_day'        => $request->input('release_relative_day'),
                'release_relative_persistent' => $input_release_relative_persistent,
                'coverimage_path'             => $coverimage_path,
            ]);
            $video->url_id       = \App\Helpers\CustomHelper::generateUrlId($request->input('name'));
            $video->cdn_vendor   = 'cloudflare';
            $video->date_created = date('Y-m-d H:i:s');
            $video->cdn_id       = $kvp_video_upload_metadata_value_jdcode['stream-media-id'];
            if ($video->cdn_id) {
                $durationResponse = self::getVideoDurationNew($video->cdn_id);
                if ($durationResponse['success']) {
                    $video->duration = $durationResponse['duration']; // assuming 'duration' field in Video table
                    $video->save();
                } else {
                    return view('error', [
                        'title'   => 'Duration Retrieval Failed',
                        'message' => 'Unable to fetch video duration from Cloudflare.',
                    ]);
                }
            }
            $video->save();

            // Check if the request is coming from the edit playlist context
            if ($request->is_playlist === '1') {
                return redirect()->to('/admin/crud/list/VideoPlaylist')
                    ->with('status', 'Video file was uploaded successfully.');
            }

            return view('feedback', ['title' => 'Video Upload Success', 'message' => 'Video file was uploaded successfully.']);
        }

        // If it's a GET request, just show the form
        return view('admin.video_upload', compact('existingTags'));
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

        // Check if the response was successful
        if ($response->successful()) {
            return $response->json(); // Return the Cloudflare response
        }

        // Handle any errors
        return [
            'error' => true,
            'message' => 'Failed to upload image to Cloudflare',
            'details' => $response->json(),
        ];
    }

    public function edit($id)
    {
        $video = Video::find($id);
        if (!$video) {
            return view('error', [
                'title'   => 'Video Not Found',
                'message' => 'The video you are trying to edit does not exist.',
            ]);
        }
        // Retrieve all tags from Video
        $videoTags = Video::all()->flatMap(function ($video) {
            return json_decode($video->tags, true);
        });

        // Retrieve all tags from VideoPlaylist
        $playlistTags = VideoPlaylist::all()->flatMap(function ($playlist) {
            return json_decode($playlist->tags, true);
        });

        // Combine both tag collections and ensure uniqueness
        $allTags = $videoTags->merge($playlistTags)->unique();

        return view('admin.video_edit', compact('video', 'allTags'));
    }

    public function update(Request $request, $id)
    {

        // Initialize
        $input_release_relative_persistent = $request->has('release_relative_persistent');

        // Validate the input
        $request->validate([
            'coverimage_file' => 'nullable|image|max:102400',
            'tags'            => 'nullable|array',
            'tags.*'          => 'string|max:255',
        ]);

        // Find the video to update
        $video = Video::find($id);
        if (!$video) {
            return view('error', [
                'title'   => 'Video Not Found',
                'message' => 'The video you are trying to update does not exist.',
            ]);
        }

        // Handle file storage
        $coverimage_path = $video->coverimage_path;

        // Handle cover image upload
        if ($request->file('coverimage_file')) {
            // Upload the new image to Cloudflare
            $cloudflare_response = self::uploadToCloudflare($request->file('coverimage_file'));
            
            // Check for a successful response
            if ($cloudflare_response['success'] === true) {
                $coverimage_path = $cloudflare_response['result']['variants'][0]; // Get the image URL
            } else {
                // Handle the error as needed (e.g., log it, notify the user, etc.)
                return view('error', [
                    'title'   => 'Image Upload Failed',
                    'message' => 'There was an error uploading the cover image to Cloudflare.',
                ]);
            }
        }

        // Prepare data for update
        $playlistId  = $request->input('playlist');
        $playlistPos = $request->input('playlist_pos');

        $playlistData = $playlistId ? [$playlistId => ['pos' => $playlistPos]] : $video->playlist;

        // Check if a new video is being uploaded by fetching metadata from KVP
        $newCdnId = null;
        if ($request->has('session_uuid')) {
            // Get video upload metadata from KVP
            $kvp_video_upload_metadata = KVP::where('type', 'cloudflare_video_upload_metadata')
                ->where('key', $request->input('session_uuid'))
                ->first();

            if ($kvp_video_upload_metadata) {
                // Decode metadata to get stream-media-id (cdn_id)
                $kvp_video_upload_metadata_value = json_decode($kvp_video_upload_metadata->value, true);
                $newCdnId = $kvp_video_upload_metadata_value['stream-media-id'] ?? null; // Extract the cdn_id
            }
        }

        $updateData = [
            'name'                        => $request->input('name'),
            'description'                 => $request->input('description'),
            'audience'                    => $request->input('audience') ? json_encode([$request->input('audience')]) : $video->audience,
            'tags'                        => $request->input('tags') ? json_encode($request->input('tags')) : $video->tags,
            'playlist'                    => $playlistData ? json_encode($playlistData) : $video->playlist,
            'playlist_pos'                => $request->input('playlist_pos'),
            'published'                   => true,
            'release_relative_day'        => $request->input('release_relative_day'),
            'release_relative_persistent' => $request->has('release_relative_persistent'),
            'coverimage_path'             => $coverimage_path,
            'cdn_id'                      => $newCdnId ?? $video->cdn_id,
        ];
        // Update the video
        $video->update($updateData);

        return view('feedback', ['title' => 'Video Update Success', 'message' => 'Video was updated successfully.']);
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
        $video = Video::find($id);
        $video->delete();

        // Redirect or return a response
        return redirect()->back()->with('status', 'videos deleted successfully!');
    }

    /**
     * Render video watch view
     *
     * @param Request POST request
     * @param string $url_id Local ID for video
     */
    public static function watch(Request $request, $url_id)
    {
        return view('video/watch', ['video' => Video::where('url_id', $url_id)->get()->pop()]);
    }

    public static function cloudflare_get_tusurl(Request $request)
    {
        $headersCorsPermissive = [
            'Access-Control-Allow-Origin'   => '*',
            'Access-Control-Expose-Headers' => '*',
            'Access-Control-Allow-Methods'  => '*',
            'Access-Control-Allow-Headers'  => '*',
        ];
  
        $httpRequestHeaders = getallheaders();
    
        // Prepare Cloudflare API URL
        $url = "https://api.cloudflare.com/client/v4/accounts/" . env('CLOUDFLARE_ACCOUNT_ID') . "/stream?direct_user=true";
    
        // Create a Guzzle HTTP client
        $client = new Client();
        $options = [
            'headers' => [
                'Authorization'    => 'Bearer ' . env('CLOUDFLARE_STREAM_TOKEN'),
                'Tus-Resumable'    => '1.0.0',
                'Upload-Length'    => $httpRequestHeaders['Upload-Length'],
                'Upload-Metadata'  => $httpRequestHeaders['Upload-Metadata'],
                'Upload-Creator'   => 'shadowc',
            ],
            'allow_redirects' => true,
            'http_errors'     => false,
        ];
    
        try {
            // Asynchronous Guzzle request
            $promise = $client->postAsync($url, $options);
    
            // Wait for the promise to resolve
            $response = $promise->wait();
    
            // Extract headers from the response
            $responseHeaders = self::parseGuzzleHeaders($response);
    
            // Save the metadata returned from Cloudflare
            KVP::create([
                'type'  => 'cloudflare_video_upload_metadata',
                'key'   => $request->input('session_uuid'),
                'value' => json_encode([
                    'stream-media-id' => $responseHeaders['stream-media-id'] ?? null,
                    'session_uuid'    => $request->input('session_uuid'),
                    'Upload-Length'   => $httpRequestHeaders['Upload-Length'],
                    'Upload-Metadata' => $httpRequestHeaders['Upload-Metadata'],
                    'CF-Ray'          => $responseHeaders['CF-Ray'] ?? null,
                ]),
            ]);
    
            // Return response with headers, including the TUS URL
            return response('', 200)
                ->withHeaders(array_merge(['Location' => $responseHeaders['Location'] ?? ''], $headersCorsPermissive));
    
        } catch (RequestException $e) {
            // Handle any exceptions that occur during the request
            return response()->json(['error' => 'Failed to initialize upload.'], 500);
        }
    }
    
    // Helper function to parse Guzzle response headers
    private static function parseGuzzleHeaders($response)
    {
        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }
        return $headers;
    }

    private static function getVideoDurationNew($videoId)
    {
        $apiKey = env('CLOUDFLARE_STREAM_TOKEN');
        $accountId = env('CLOUDFLARE_ACCOUNT_ID');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get("https://api.cloudflare.com/client/v4/accounts/{$accountId}/stream/{$videoId}");

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['result']['duration'])) {
                $seconds = $data['result']['duration'];
                $formattedDuration = gmdate("H:i:s", $seconds); // Format to H:i:s (hh:mm:ss)
                return ['success' => true, 'duration' => $formattedDuration];
            }
        }
        return ['success' => false];
    }
}
