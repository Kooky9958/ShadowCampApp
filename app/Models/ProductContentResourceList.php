<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RelativeRelease;

class ProductContentResourceList extends Model
{
    use HasFactory;
    use RelativeRelease;

    /**
     * The attributes that are NOT mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [
        'url_id',
        'date_created'
    ];

    /**
     * Default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'url_id' => "",
        'date_created' => "1970-01-01 00:00:00"
    ];

    /**
     * Get a resource list by its URL ID
     * 
     * @param string $url_id The URL ID of the resource list being sought
     */
    public static function getResourceList($url_id) :self {
        if(stripos($url_id, "virt!!") !== false) {        
            $return = new self();

            $url_id_exploded = explode("!!", $url_id);
            switch ($url_id_exploded[1]) {
                case 'resources':
                    $return->is_virtual = true;
                    $return->virtual_should_display = true;
                    $return->url_id = "virt!!resources";
                    $return->name = "Resources";
                    break;
                
                default:
                    # code...
                    break;
            }
        }
        else
            $return = self::where('url_id', $url_id)->first();

        return $return;
    }

    /**
     * Get the resources belonging to a resource_list
     * 
     * @return array The resources belonging to the resource_list
     */
    public function getResourceListResources() {
        // Get resources
        $resources_query = ProductContentResource::where('resource_list', 'like', "%\"{$this->url_id}\"%")->get();

        $resources = [];
        while($resource = $resources_query->pop()) {
            $resource_resource_list_field = json_decode($resource->resource_list, true);

            $resources[$resource_resource_list_field[$this->url_id]['pos']] = $resource;
        }

        asort($resources);

        return collect($resources);
    }

    // ProductContentResourceList.php
    public function resources()
    {
        return ProductContentResource::whereRaw("JSON_CONTAINS(resource_list, '{\"{$this->url_id}\":{}}')");
    }
}
