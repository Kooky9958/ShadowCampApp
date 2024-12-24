<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RelativeRelease;
use App\Traits\GenericContent;

class Video extends Model implements \App\Interfaces\AdminCRUD
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
        'cdn_vendor',
        // 'cdn_id',
        'cdn_data',
        'date_created'
    ];

    /**
     * Default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'url_id' => "",
        'cdn_vendor'  => "",
        'cdn_id' => "",
        'cdn_data' => "",
        'date_created' => "1970-01-01 00:00:00",
        // 'video_duration' => ""
    ];

    /**
     * Get the human readable name  of the implementing model
     *
     * @return string human readable name of the implementing model
     */
    public static function getName() :string {
        return "Video";
    }

    /**
     * Get the human readable plural name of the implementing model
     *
     * @return string human readable plural name  of the implementing model
     */
    public static function getNamePlural() :string {
        return "Videos";
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'content');
    }

    /**
     * Get the list of attributes which should not be displayed by the AdminCRUD extension
     *
     * @return array list of attributes which should not be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACHiddenAttributes() :array {
        return ['cdn_id'];
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
    public function getPlaylistNameAttribute()
    {
        $playlistJson = json_decode($this->attributes['playlist'], true);
        $urlId = key($playlistJson);

        $playlist = VideoPlaylist::where('url_id', $urlId)->first();

        return $playlist ? $playlist->name : null;
    }

    public function playlist()
    {
        return $this->belongsTo(VideoPlaylist::class, 'playlist', 'url_id'); // Assuming 'playlist' is the foreign key in videos
    }

    // Define a query scope for filtering by tags
    public function scopeWithTags($query, array $tags)
    {
        // Fetch videos that contain any of the tags
        return $query->where(function($query) use ($tags) {
            foreach ($tags as $tag) {
                $query->orWhereJsonContains('tags', $tag);
            }
        });
    }

    public function kvp()
    {
        return KVP::whereRaw("JSON_EXTRACT(value, '$.\"stream-media-id\"') = ?", [$this->cdn_id]);
    }
}
