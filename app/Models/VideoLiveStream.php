<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RelativeRelease;

class VideoLiveStream extends Model
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
        'cdn_vendor',
        'cdn_id',
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
        'date_created' => "1970-01-01 00:00:00"
    ];

    public function isLive() : bool
    {
        return (bool) $this->live;
    }
}
