<?php

namespace App\Interfaces;

interface AdminCRUDSearchable
{
    /**
     * Get an ORM query which searches for the given search query string
     * 
     * @param string $search_query_string The search query string to search for
     * @return Illuminate\Database\Eloquent\Builder The ORM query to execute
     */
    public static function getSearchORMQuery(string $search_query_string);
}