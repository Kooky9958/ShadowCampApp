<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductContentResourceList;
use App\Models\ProductContentResource;

class ProductContentResourceListController extends Controller
{
    /**
     * Get generic variables for passing down to the view
     * 
     * @param string $audience Audience ID
     * @return array Generic variables to be passed down to the view
     */
    private static function getGenericVarsForView($audience=null) {
        $audience_determined = \App\Helpers\CustomHelper::determineAudience($audience);

        return [
            'audience_determined' => $audience_determined,
            'resource_lists_filter_by' => self::getResourceListsForAudience($audience_determined, true),
        ];
    }

    /**
     * Retrieve all resource lists for the given audience
     * 
     * @param string $audience Audience
     * @param bool $excludeHidden Exclude hidden resource lists
     * @return \Illuminate\Support\Collection
     */
    private static function getResourceListsForAudience($audience, $excludeHidden=false) {
        $resource_lists = ProductContentResourceList::where('audience', 'like', "%\"$audience\"%")->get();
        
        if($excludeHidden === true) {
            $resource_lists = $resource_lists->filter(function($resource_list) {
               return $resource_list->shouldDisplay();
            });
        }

        return $resource_lists;
    }

    /**
     * Get all available resource_lists
     * 
     * @return array The available resource_lists
     */
    public static function getAllResourceLists() {
        return ProductContentResourceList::all();
    }

    /**
     * Create resource_list
     * 
     * @param Illuminate\Http\Request $request
     */
    public static function create(Request $request) {
        // Init
        $input_release_relative_persistent = ($request->input('release_relative_persistent') == 'on') ? true : false ;

        // Create Transaction
        $resource_list = ProductContentResourceList::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'audience' => json_encode([$request->input('audience')]),
            'published' => true,
            'release_relative_day' => $request->input('release_relative_day'),
            'release_relative_persistent' => $input_release_relative_persistent
        ]);
        $resource_list->url_id = \App\Helpers\CustomHelper::generateUrlId($request->input('name'));
        $resource_list->date_created = date('Y-m-d H:i:s');
        $resource_list->save();

        return view('feedback', ['title' => 'Resource List Create Success', 'message' => 'Resource list created successfully.']);
    }

    /**
     * Render resource_list see view
     * 
     * @param Request POST request
     * @param string $url_id Local ID for resource_list
     */
    public static function see($request, $url_id) {
        $resource_list = ProductContentResourceList::where('url_id', $url_id)->first();
        return view('resources/pc/resource_list', ['resources' => $resource_list->getResourceListResources(), 'resource_list' => $resource_list]);

        // Init
        $generic_vars_for_view = self::getGenericVarsForView();

        $resource_list = ProductContentResourceList::where('url_id', $url_id)->first();

        $vars_for_view = array_merge($generic_vars_for_view, [
            'resources' => $resource_list->getResourceListResources(),
            'resource_list' => $resource_list,
        ]);

        return view('resources/pc/resource_list', $vars_for_view);
    }

    /**
     * Render virtual resource list for given audience
     * 
     * @param Request POST request
     * @param string $name Virtual resource list name
     * @param string $audience Audience ID
     */
    public static function seeVirtualResourceList($request, $name, $audience=null) {
        //Init
        $generic_vars_for_view = self::getGenericVarsForView($audience);
        $resources = [];

        // Prepare resource list videos
        switch ($name) {
            case 'resources':
                $resource_list = ProductContentResourceList::getResourceList('virt!!resources');
                
                $resources = ProductContentResource::where('audience', 'like', "%\"{$generic_vars_for_view['audience_determined']}\"%")
                            ->where('published', '=', '1')
                            ->get();
                break;
            
            default:
                // code...
                break;
        }

        $vars_for_view = array_merge($generic_vars_for_view, [
            'disable_filter_by_resource_lists' => false,
            'resources' => $resources,
            'resource_list' => $resource_list,
        ]);

        return view('resources/pc/resource_list', $vars_for_view);
    }

    /**
     * Render list of resource_lists for given audience
     * 
     * @param Request POST request
     * @param string $audience Audience ID for list of resource_lists
     */
    public static function viewResourceListsForAudience($request, $audience=null) {
        $audience_determined = \App\Helpers\CustomHelper::determineAudience($audience);

        return view('resources/pc/list_resource_lists', ['resource_lists' => self::getResourceListsForAudience($audience_determined)]);
    }
}