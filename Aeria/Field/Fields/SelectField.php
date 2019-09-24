<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Interfaces\FieldInterface;
/**
 * SelectField is the class that represents a select field
 * 
 * @category Field
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class SelectField extends BaseField
{
    public $is_multiple_field = false;
    /**
     * Gets the field's value
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return mixed the field's values, an array containing the selected values
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter = false) 
    {
        $values = parent::get($saved_fields, true);

        if (isset($this->config['multiple']) && $this->config['multiple'] && $values!="") {
            $values = explode(',', $values);
        }
        if (empty($values)) {

            return null;
        }
        if(!$skip_filter)
          $values = apply_filters("aeria_get_select", $values, $this->config);
        return $values;
    }
    /**
     * Gets the field's value and its errors
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param array $errors      the saving errors
     *
     * @return array the field's config, hydrated with values and errors
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getAdmin(array $saved_fields, array $errors) 
    {
        $savedValues = parent::getAdmin($saved_fields, $errors, true);
        return array_merge(
            $this->config,
            $savedValues
        );
    }
}
