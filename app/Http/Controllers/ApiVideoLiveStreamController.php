<?php

namespace App\Http\Controllers;

use App\Models\VideoLiveStream;
use Illuminate\Http\Request;

class ApiVideoLiveStreamController extends Controller
{
    /**
     * Fetch and return a live stream video.
     *
     * @param Request $request The incoming HTTP request.
     * @param string $url_id The local ID for the live stream video.
     * @return \Illuminate\Http\JsonResponse The JSON response with the video data or error message.
     */
    public static function getLiveVideo(Request $request, $url_id)
    {
        $liveStreamVideo = VideoLiveStream::where('url_id', $url_id)->first();

        if ($liveStreamVideo) {
            return response()->json([
                'message' => 'Video fetched successfully',
                'live_video' => $liveStreamVideo,
            ], 200);
        }

        // If the video does not exist, return an error response
        return response()->json([
            'status' => false,
            'message' => 'Video not found',
        ], 200);
    }
}
