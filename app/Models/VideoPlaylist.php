<?php

namespace App\Models;

use App\Traits\RelativeRelease;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Video;

class VideoPlaylist extends Model
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
     * Get the human readable name  of the implementing model
     * 
     * @return string human readable name of the implementing model
     */
    public static function getName() :string {
        return "VideoPlaylist";
    }

       /**
     * Get the human readable plural name of the implementing model
     * 
     * @return string human readable plural name  of the implementing model
     */
    public static function getNamePlural() :string {
        return "VideoPlaylists";
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
     * Get a playlist by its URL ID
     * 
     * @param string $url_id The URL ID of the playlist being sought
     */
    public static function getPlaylist($url_id) :self {
        if(stripos($url_id, "virt!!") !== false) {        
            $return = new self();

            $url_id_exploded = explode("!!", $url_id);
            switch ($url_id_exploded[1]) {
                case 'workouts':
                    $return->is_virtual = true;
                    $return->virtual_should_display = true;
                    $return->url_id = "virt!!workouts";
                    $return->name = "Workouts";
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
     * Get the videos belonging to a playlist
     * 
     * @return Illuminate\Support\Collection The videos belonging to the playlist
     */
    public static function getACDisplayBelongsToRelations() :array {
        return [];
    }

    public function getPlaylistVideos() {
        // Get videos
        $videos_query = Video::where('playlist', 'like', "%\"{$this->url_id}\"%")->get();

        //// Sort the videos by their position in the playlist
        $videos = [];
        while($video = $videos_query->pop()) {
            $video_playlist_field = json_decode($video->playlist, true);

            $videos[$video_playlist_field[$this->url_id]['pos']] = $video;
        }
        
        krsort($videos);

        return collect($videos);
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'playlist', 'url_id'); // Adjust this if the relationship is different
    }
}
