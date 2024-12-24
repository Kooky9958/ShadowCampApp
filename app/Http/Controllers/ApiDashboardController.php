<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ProductContentResource;
use App\Models\Video;
use App\Models\VideoLiveStream;
use App\Models\VideoPlaylist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// 22-08-2024    Changes Done by -> Vartik Anand  

class ApiDashboardController extends Controller
{
    /**
     * thiswill return the these function [ video_most_recent , resource_most_recent, playlist_most_recent, live_stream_upcoming ] 
     *
     * @return JsonResponse
     */
    public function get_dashboard_data(Request $request)
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

        $audience = $request->query('audience');

        $accountData = Account::getSessionAccount() ?? [];
        // Merge the account data with recent video resource data  and playlist data.
        $data = array_merge(
            $accountData,
            [
                'video_most_recent' => Video::getMostRecent(4, false, $audience),
                'resource_most_recent' => ProductContentResource::getMostRecent(2),
                'playlist_most_recent' => $this->get_recent_records(VideoPlaylist::class, $audience, 2),
                'live_stream_upcoming' => $this->get_recent_records(VideoLiveStream::class, $audience, 1)
            ]
        );

        // Return the combined data as a JSON response.
        return response()->json($data);
    }

    // 24-08-2024 commented by -> Vartik Anand START
    // // 23-08-2024    Coded by -> Vartik Anand   START
    // // Function to get most recent playlist 
    // /**
    //  * Get recent playlists based on audience
    //  *
    //  * @param string|null $audience Audience to filter by, or null for no filter
    //  * @param int $limit Number of playlists to return | default is set to 2
    //  * @return \Illuminate\Database\Eloquent\Collection
    //  */
    // function get_playlists(?string $audience = null, int $limit = 2)
    // {
    //     $query = VideoPlaylist::where('published', '=', '1');

    //     // Apply audience filter if provided
    //     if ($audience) {
    //         $query->where('audience', 'like', "%\"$audience\"%");
    //     }

    //     // Order by 'created_at' in descending order and limit the results
    //     return $query->orderBy('created_at', 'desc')->take($limit)->get();
    // }
    // // 23-08-2024    Coded by -> Vartik Anand   END
    // 24-08-2024 commented by -> vartik anand END



// 24-08-2024 Coded by -> Vartik Anand 
// Function to get recent records based on model and audience

/**
 * Get recent records from a specified model based on audience and order.
 *
 * This function retrieves the most recent records from a given Eloquent model, 
 * optionally filtering by audience and limiting the number of results returned. 
 * The results can also be ordered in ascending or descending order based on the 'created_at' field.
 *
 * @param string $model The Eloquent model class to query (e.g., 'VideoPlaylist', 'VideoLiveStream'). 
 *                      This should be a fully qualified class name.
 * @param string|null $audience (Optional) Audience to filter by, or null for no filter. 
 *                              If provided, the function will apply a 'like' filter on the 'audience' field.
 * @param int $limit (Optional) Number of records to return. Defaults to 1.
 * @param string $orderby (Optional) Order by 'created_at' field. Accepts 'desc' or 'asc'. Defaults to 'desc'.
 *                        This parameter determines whether the results are ordered in descending or ascending order.
 * 
 * @return \Illuminate\Database\Eloquent\Collection Returns a collection of the most recent records from the specified model,
 *                                                   filtered by audience if provided, ordered by 'created_at', and limited to 
 *                                                   the specified number of results.
 */
function get_recent_records(string $model, ?string $audience = null, int $limit = 1, string $orderby = 'desc')
{
    $query = $model::where('published', true); 

    // Apply audience filter if provided
    if ($audience !== null) {
        $query->where('audience', 'like', '%"'.$audience.'"%');
    }

    // Order by 'created_at' in default descending order and limit the results
    return $query->orderBy('created_at', $orderby)
                 ->take($limit)
                 ->get();
}
// 24-08-2024 Coded by -> Vartik Anand  END

    
}
