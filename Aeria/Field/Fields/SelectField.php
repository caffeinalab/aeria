<?php

namespace Aeria\Field\Fields;

/**
 * SelectField is the class that represents a select field.
 *
 * @category Field
 *
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class SelectField extends BaseField
{
    /**
     * Transform the config array; note that this does not operate on
     * `$this->config`: this way it can be called from outside.
     *
     * @param array $config the field's config
     *
     * @return array the transformed config
     */
    public static function transformConfig(array $config)
    {
        if (isset($config['options'])) {
            $config['options'] = aeria_objects_filter($config['options'], $config);
        }

        return parent::transformConfig($config);
    }

    /**
     * Gets the field's value.
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return mixed the field's values, an array containing the selected values
     *
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter = false)
    {
        $values = parent::get($saved_fields, true);

        if (is_null($values) || empty($values)) {
            return null;
        }
        if (isset($this->config['multiple']) && $this->config['multiple']) {
            $values = explode(',', $values);
        }

        if (!$skip_filter) {
            $values = apply_filters('aeria_get_select', $values, $this->config);
        }

        return $values;
    }
}
