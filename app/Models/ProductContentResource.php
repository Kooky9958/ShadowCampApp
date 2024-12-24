<?php

namespace App\Models;

use App\Traits\RelativeRelease;
use App\Traits\GenericContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductContentResource extends Model implements \App\Interfaces\AdminCRUD
{
    use HasFactory;
    use RelativeRelease;
    use GenericContent;

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
        'date_created' => "1970-01-01 00:00:00",
        'type' => "",
        'resource_location' => ""
    ];

    /**
     * Get the human readable name  of the implementing model
     * 
     * @return string human readable name of the implementing model
     */
    public static function getName() :string {
        return "Resource";
    }

    /**
     * Get the human readable plural name of the implementing model
     * 
     * @return string human readable plural name  of the implementing model
     */
    public static function getNamePlural() :string {
        return "Resources";
    }

    /**
     * Get the list of attributes which should not be displayed by the AdminCRUD extension
     * 
     * @return array list of attributes which should not be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACHiddenAttributes() :array {
        return [];
    }

    /**
     * Get the list of attributes which MUST be displayed by the AdminCRUD extension
     * 
     * @return array list of attributes which MUST be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACForceDisplayAttributes() :array {
        return [];
    }

    /**
     * Get the list of belongs-to relations which should be displayed by the AdminCRUD extension
     * 
     * @return array list of field which contain belongs-to relations  and should be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACDisplayBelongsToRelations() :array {
        return [];
    }

    public function getResourcelistNameAttribute()
    {
        $resourcelistJson = json_decode($this->attributes['resource_list'], true);
        $urlId = key($resourcelistJson);

        $resourcelist = ProductContentResourceList::where('url_id', $urlId)->first();

        return $resourcelist ? $resourcelist->name : null;
    }

    public function resourceList()
    {
        return $this->belongsTo(ProductContentResourceList::class, 'url_id', 'url_id');
    }
}
