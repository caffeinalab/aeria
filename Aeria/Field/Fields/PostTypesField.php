<?php

namespace Aeria\Field\Fields;

/**
 * PostTypesField is the class that represents a select with all post-types as options.
 *
 * @category Field
 *
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class PostTypesField extends SelectField
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

        $post_types = get_post_types(
            ['public' => true],
            'objects'
        );

        $config['options'] = array_map(function ($post_type) {
            return [
                'value' => $post_type->name,
                'label' => $post_type->label,
            ];
        }, array_values($post_types));

        return parent::transformConfig($config);
    }
}
