<?php

namespace App\Traits;

use App\Helpers\CustomHelper;

trait GenericContent
{
    /**
     * Get the most recent content
     * 
     * @param int $limit number of items to return
     * @param bool $ignore_audience default=false, set true to ignore the audience of the user
     * @return mixed
     */
    public static function getMostRecent($limit=10, $ignore_audience=false, ?string $audience = null) {
        $query = self::where('published', '=', '1'); 

        if (!$ignore_audience && $audience !== null) {
            $query = $query->where('audience', 'like', '%"'.$audience.'"%');
        }

        return $query->orderBy('date_created', 'desc')->limit($limit)->get();
    }
}