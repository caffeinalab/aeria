<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Fields\SelectField;
use Aeria\Field\Interfaces\FieldInterface;
/**
 * Relation is the class that represents a relationship field
 * 
 * @category Field
 * @package  Aeria
 * @author   Andrea Longo <andrea.longo@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class RelationField extends SelectField
{

    protected $original_config;
    /**
     * Constructs the field
     *
     * @param string $parent_key the field's parent key
     * @param array  $config      the field's config
     * @param array  $sections   Aeria's sections config
     * @param array  $index      index for of the subfield
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct($parent_key, $config, $sections, $index = null) {
        parent::__construct($parent_key, $config, $sections, $index);

        $this->original_config = json_decode(json_encode($config));

        $this->config['type'] = 'select';
        $this->config['ajax'] = $this->config['relation'];
        unset($this->config['relation']);
    }
    /**
     * Gets the field's value
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return array the field's related posts.
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter = false) {
        $values = parent::get($saved_fields, true);

        if ($skip_filter) {
            return $values;
        }

        if (!empty($values)) {
            if (isset($this->config['multiple']) && $this->config['multiple']) {
                $post_type = isset($this->config['relation']['type'])
                    ? $this->config['relation']['type']
                    : 'any';
                $values = get_posts(
                    [
                        'post__in' => $values,
                        'post_type' => 'any'
                    ]
                );
            } else {
                $values = get_post($values);
            }
        }

        $values = apply_filters("aeria_get_relation", $values, $this->original_config);

        return $values;
    }

}
