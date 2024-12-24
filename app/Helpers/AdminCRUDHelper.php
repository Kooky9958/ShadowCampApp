<?php

namespace App\Helpers;

use App\Helpers\DsofeirLaravelHelper;

/**
 * Helper class providing utilities for common operations in AdminCRUD package
 */
class AdminCRUDHelper
{
    public static function modelGetDisplayableAttributes($model_fqcn) {
        // Get generic instance of model and non-guarded attributes
        $model_instance = new $model_fqcn;
        $model_object_vars = DsofeirLaravelHelper::modelGetNonGuardedAttributes($model_fqcn);

        // Hide specified values
        $model_object_vars = array_diff($model_object_vars, $model_instance->getACHiddenAttributes());

        // Append database ID field
        $model_object_vars[] = 'id';

        // Force display of specified values
        $model_object_vars = array_merge($model_object_vars, $model_instance->getACForceDisplayAttributes());

        return $model_object_vars;
    }
    
}
