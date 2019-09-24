<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Interfaces\FieldInterface;
/**
 * Picture is the class that represents a picture field
 * 
 * @category Field
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class PictureField extends BaseField
{

    public $is_multiple_field = false;
    /**
     * Gets the field's value
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return array the field's values, an array containing the image's thumb and full res
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter = false) 
    {
        $value = (int) parent::get($saved_fields, true);
        $thumb = wp_get_attachment_image_src($value)[0];
        $full = wp_get_attachment_image_src($value, "full")[0];
        if($skip_filter)
          return [
            'thumb' => $thumb,
            'full' => $full
          ];

        return apply_filters(
            'aeria_get_picture', 
            [
            'thumb' => $thumb,
            'full' => $full
            ],
            $this->config
        );
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
        $stored_value = parent::get($saved_fields, true);
        $result = [];
        $result['value'] = (int)$stored_value;
        $result['url'] = wp_get_attachment_image_src($result['value'])[0];

        return array_merge(
            $this->config,
            $result
        );
    }
}
