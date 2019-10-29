<?php

namespace Aeria\Field\Fields;

/**
 * TermsField is the class that represents a terms field.
 *
 * @category Field
 *
 * @author   Lorenzo Girardi <lorenzo.girardi@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class TermsField extends SelectField
{
    protected $original_config;
    /**
     * Transform the config array; note that this does not operate on
     * `$this->config`: this way it can be called from outside
     *
     * @param array $config    the field's config
     *
     * @return array        the transformed config
     */
    public static function transformConfig(array $config) {
        $config['type'] = 'select';

        $taxonomy = (isset($config['taxonomy'])) ? $config['taxonomy'] : 'category';
        $hide_empty = (isset($config['hide_empty'])) ? $config['hide_empty'] : true;

        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => $hide_empty,
        ));

        $config['options'] = array_map(
            function ($term) {
                return array(
                    'label' => $term->name,
                    'value' => $term->term_id,
                );
            },
            array_values($terms)
        );

        unset($config['taxonomy']);
        return parent::transformConfig($config);
    }
    /**
     * Constructs the field.
     *
     * @param string $parent_key the field's parent key
     * @param array  $config     the field's config
     * @param array  $sections   Aeria's sections config
     * @param array  $index      index for of the subfield
     *
     * @since  Method available since Release 3.0.8
     */
    public function __construct($parent_key, $config, $sections, $index = null)
    {
        $this->original_config = json_decode(json_encode($config));
        parent::__construct($parent_key, $config, $sections, $index);
    }
}
