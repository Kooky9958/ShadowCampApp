<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RelativeRelease;

class VideoEvent extends Model
{
    use HasFactory;
    use RelativeRelease;

    protected $fillable = [
        'user_id', 'live_video_id', 'message', 'start_available_date', 'end_available_date', 'start_event_time', 'end_event_time',
    ];

     /**
     * Get the human readable name  of the implementing model
     * 
     * @return string human readable name of the implementing model
     */
    public static function getName() :string {
        return "VideoEvent";
    }

    /**
     * Get the human readable plural name of the implementing model
     * 
     * @return string human readable plural name  of the implementing model
     */
    public static function getNamePlural() :string {
        return "VideoEvents";
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
     * Get the videos belonging to a playlist
     * 
     * @return Illuminate\Support\Collection The videos belonging to the playlist
     */
    public static function getACDisplayBelongsToRelations() :array {
        return [];
    }

    public function liveStream()
    {
        return $this->belongsTo(VideoLiveStream::class, 'live_video_id');
    }
}
