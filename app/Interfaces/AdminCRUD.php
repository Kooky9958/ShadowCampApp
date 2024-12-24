<?php

namespace App\Interfaces;

interface AdminCRUD
{
    /**
     * Get the human readable name  of the implementing model
     * 
     * @return string human readable name  of the implementing model
     */
    public static function getName() :string;

    /**
     * Get the human readable plural name of the implementing model
     * 
     * @return string human readable plural name  of the implementing model
     */
    public static function getNamePlural() :string;

    /**
     * Get the list of attributes which should not be displayed by the AdminCRUD extension
     * 
     * @return array list of attributes which should not be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACHiddenAttributes() :array;

    /**
     * Get the list of attributes which MUST be displayed by the AdminCRUD extension
     * 
     * @return array list of attributes which MUST be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACForceDisplayAttributes() :array;

    /**
     * Get the list of belongs-to relations which should be displayed by the AdminCRUD extension
     * 
     * @return array list of field which contain belongs-to relations  and should be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACDisplayBelongsToRelations() :array;
}