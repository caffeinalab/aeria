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

        $taxonomy = (isset($config['taxonomy'])) ? $config['taxonomy'] : 'category';
        $hide_empty = (isset($config['hide_empty'])) ? $config['hide_empty'] : true;

        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => $hide_empty,
        ));

        $config['options'] = [];

        if (!empty($terms) && !is_wp_error($terms)) {
            $config['options'] = array_map(
                function ($term) {
                    return array(
                        'label' => $term->name,
                        'value' => $term->term_id,
                    );
                },
                array_values($terms)
            );
        }

        unset($config['taxonomy']);

        return parent::transformConfig($config);
    }
}
