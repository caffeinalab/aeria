<?php

namespace Aeria\Field\Fields;

/**
 * Relation is the class that represents a relationship field.
 *
 * @category Field
 *
 * @author   Andrea Longo <andrea.longo@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class RelationField extends SelectField
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
        $config['type'] = 'select';
        $config['ajax'] = $config['relation'];
        unset($config['relation']);

        return parent::transformConfig($config);
    }

    /**
     * Gets the field's value.
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return array the field's related posts
     *
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter = false)
    {
        $values = parent::get($saved_fields, true);
        if ($skip_filter) {
            return $values;
        }

        $multiple = (isset($this->config['multiple']) && $this->config['multiple']);

        if (!empty($values)) {
            $post_type = isset($this->config['relation']['type'])
                ? $this->config['relation']['type']
                : 'any';

            $values = get_posts(
                [
                    'post__in' => $multiple ? $values : [$values],
                    'post_type' => $post_type,
                ]
            );
        }

        $values = apply_filters('aeria_get_relation', $values, $this->original_config);

        if (is_null($values) || empty($values)) {
            return null;
        }

        return $multiple ? $values : $values[0];
    }
}
