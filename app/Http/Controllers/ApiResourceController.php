<?php

namespace App\Http\Controllers;

use App\Models\ProductContentResource;
use App\Models\ProductContentResourceList; // Ensure you import the model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class ApiResourceController extends Controller
{

    public function get_all_resource_list(Request $request)
    {
        // Initialize the resource list query
        $resource_list_query = ProductContentResourceList::query();
        
        $audienceFilter = $request->query('audience', null); // Expecting a specific audience value

        // Filter by audience if provided
        if ($audienceFilter) {
            $resource_list_query->whereJsonContains('audience', $audienceFilter);
        }

        // Add condition to filter records where release_relative_day is null
        $resource_list_query->whereNull('release_relative_day');

        // Fetch the resource lists based on the query
        $resourceLists = $resource_list_query->get();

        // Get the count of the resource lists
        $resourceListCount = $resourceLists->count();

        // Return response
        return response()->json([
            'status' => 'Success',
            'message' => 'Filtered resource lists fetched successfully',
            'count' => $resourceListCount,
            'response' => $resourceLists,
        ], 200);
    }

    public function get_all_resource(Request $request)
    {
        $playlist = [];
        $playlist_count = 0;

        $resource_query = ProductContentResource::query();
        
        // Get URL IDs from the request
        $urlIds = $request->input('url_id', []);

        // If $urlIds is a string, decode it into an array
        if (is_string($urlIds)) {
            $urlIds = json_decode(str_replace("'", '"', $urlIds), true);
        }

            if (!empty($urlIds) && is_array($urlIds)) {
                $resource_query->where(function ($query) use ($urlIds) {
                    foreach ($urlIds as $urlId) {
                        $query->orWhereRaw("JSON_CONTAINS(resource_list, JSON_OBJECT(?, JSON_OBJECT()))", [$urlId]);
                    }
                });
            }

            // Get search term from the request
            $searchTerm = $request->input('search', '');
            $sortDate = $request->input('sort_date', 'newest');

            // Search by name or description
            if (!empty($searchTerm)) {
                $keywords = preg_split('/[\s,]+/', $searchTerm); // Split search term by spaces or commas

                $resource_query->where(function($query) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $query->where(function($subQuery) use ($keyword) {
                            $subQuery->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('description', 'like', "%{$keyword}%");
                        });
                    }
                });
            }

            // Sorting by date
            if ($sortDate === 'newest') {
                $resource_query->orderBy('created_at', 'desc');
            } elseif ($sortDate === 'oldest') {
                $resource_query->orderBy('created_at', 'asc');
            }

            // Fetch the resources
            $playlist = $resource_query->get();
            $playlist_count = $playlist->count();
              
        return response()->json([
            'status' => 'Success',
            'message' => 'All playlist fetched successfully',
            'count' =>  $playlist_count,
            'response' => $playlist,
        ], 200);           
    }

    public function get_all_precall_resource_list(Request $request)
    {
        // Initialize the resource list query
        $resource_list_query = ProductContentResourceList::query();
        
        $audienceFilter = $request->query('audience', null); // Expecting a specific audience value

        // Filter by audience if provided
        if ($audienceFilter) {
            $resource_list_query->whereJsonContains('audience', $audienceFilter);
        }

        // Fetch the resource lists based on the query
        $resourceLists = $resource_list_query->get();

        // Get the count of the resource lists
        $resourceListCount = $resourceLists->count();

        // Return response
        return response()->json([
            'status' => 'Success',
            'message' => 'Filtered resource lists fetched successfully',
            'count' => $resourceListCount,
            'response' => $resourceLists,
        ], 200);
    }

    public function get_all_precall_resource(Request $request)
    {
        $playlist = [];
        $playlist_count = 0;

        $resource_query = ProductContentResource::query();
        
        // Get URL IDs from the request
        $urlIds = $request->input('url_id', []);

        // If $urlIds is a string, decode it into an array
        if (is_string($urlIds)) {
            $urlIds = json_decode(str_replace("'", '"', $urlIds), true);
        }

        // Set the audience filter to "precall" by default
        $audienceFilter = $request->input('audience', 'precall');

        if (!empty($urlIds) && is_array($urlIds)) {
            $resource_query->where(function ($query) use ($urlIds) {
                foreach ($urlIds as $urlId) {
                    $query->orWhereRaw("JSON_CONTAINS(resource_list, JSON_OBJECT(?, JSON_OBJECT()))", [$urlId]);
                }
            });
        }

        // Search term and audience filtering
        $searchTerm = $request->input('search', '');
        $sortDate = $request->input('sort_date', 'newest');

        // Search by name or description
        if (!empty($searchTerm)) {
            $keywords = preg_split('/[\s,]+/', $searchTerm); // Split search term by spaces or commas

            $resource_query->where(function($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->where(function($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('description', 'like', "%{$keyword}%");
                    });
                }
            });
        }

        // Audience filter: Always filter by "precall"
        $resource_query->whereRaw("JSON_CONTAINS(audience, ?)", [json_encode([$audienceFilter])]);

        // Sorting by date
        if ($sortDate === 'newest') {
            $resource_query->orderBy('created_at', 'desc');
        } elseif ($sortDate === 'oldest') {
            $resource_query->orderBy('created_at', 'asc');
        }

        // Fetch the resources
        $playlist = $resource_query->get();
        $playlist_count = $playlist->count();

        return response()->json([
            'status' => 'Success',
            'message' => 'All playlist fetched successfully',
            'count' => $playlist_count,
            'response' => $playlist,
        ], 200);           
    }

}

