<?php

namespace Aeria\Field;
/**
 * FieldNodeFactory is in charge of making fields objects
 * 
 * @category Field
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class FieldNodeFactory
{
    /**
     * Returns an Aeria Field object
     * 
     * @param string $parent_key the parent key for the field
     * @param array  $config      the field's configuration
     * @param array  $sections   the sections' configuration
     * @param int    $index      the index for multiple fields
     * 
     * @return FieldInterface the field object
     * 
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function make($parent_key, $config, $sections, $index = null)
    {
        return aeria('field')->make($parent_key, $config, $sections, $index);
    }

}
