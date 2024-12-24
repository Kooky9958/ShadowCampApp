<?php

namespace App\Http\Controllers;

use App\Models\VideoLiveStream;
use Illuminate\Http\Request;

class VideoLiveStreamController extends Controller
{
    /**
     * Render live stream watch view
     * 
     * @param Request POST request
     * @param string $url_id Local ID for live stream
     */
    public static function watch(Request $request, $url_id) {
        return view('video/watch_live_stream', ['live_stream' => VideoLiveStream::where('url_id', $url_id)->first()]);
    }
}
