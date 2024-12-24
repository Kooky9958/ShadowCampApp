<?php

/*
    Copyright 2023 David John Foley (dev@dfoley.ie)

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
*/

namespace App\Helpers;

use Illuminate\Support\Facades\Schema;

/**
 * Helper class providing utilities for common operations in PHP
 */
class DsofeirLaravelHelper
{
    /**
     * Extract the headers from a cURL result
     * 
     * @param $curl_handle cURL handle
     * @param $curl_result cURL result
     * @return array $return Array of headers
     */
    public static function curl_get_headers($curl_handle, $curl_result) {
        //// Extract headers from result string
        $curl_header_size = curl_getinfo($curl_handle , CURLINFO_HEADER_SIZE);
        $headers_string = substr($curl_result , 0 , $curl_header_size);

        //// Get headers into an array
        $headers_array = array();
        $headers_string_explode = explode("\r\n" , $headers_string);
        for ($i = 0 ; $i < count($headers_string_explode) ; ++$i) {
            if (strlen($headers_string_explode[$i]) > 0) {
                if (preg_match('/^.+:\s*.+/', $headers_string_explode[$i])) {
                    $header_name = substr($headers_string_explode[$i] , 0 , strpos($headers_string_explode[$i] , ":"));
                    $header_value = substr($headers_string_explode[$i] , strpos($headers_string_explode[$i] , ":")+1);
                    $headers_array[$header_name] = $header_value;
                }
            }
        }

        return $headers_array;
    }

    /**
     * Get a list of non-guarded attributes for a model
     * 
     * @param string $model_fqcn Full qualified class name for the model in question
     * @return array $return Array of non-guarded attributes for the model class name passed in.
     * @throws \Exception If the model class name passed in is not a valid model class name.
     * @throws \Illuminate\Database\QueryException If the model class name passed in is not a valid model class name.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the model class name passed in is not a valid model class name.
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException If the model class name passed in is not a valid model class name.
     * @throws \Illuminate\Database\Eloquent\Relations\RelationNotFoundException If the model class name passed in is not a valid model class name.
     * @throws \Illuminate\Database\Eloquent\Relations\MorphOneNotFoundException If the model class name passed in is not a valid model class name.
     */
    public static function modelGetNonGuardedAttributes(string $model_fqcn) {
        // Init
        $return = [];
        $system_protected_vars = ['id', 'created_at', 'updated_at'];
        $model_instance = new $model_fqcn;
        $model_object_vars = Schema::getColumnListing($model_instance->getTable());
        $model_guarded_vars = $model_instance->getGuarded();

        foreach($model_object_vars as $object_var) {
            if (!in_array($object_var, $model_guarded_vars) && !in_array($object_var, $system_protected_vars)) {
                $return[] = $object_var;
            }
        }

        return $return;
    }
    
}
