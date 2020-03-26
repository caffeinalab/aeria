<?php

namespace Aeria\Field\Fields;

/**
 * Picture is the class that represents a picture field.
 *
 * @category Field
 *
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class PictureField extends MediaField
{
    public static function transformConfig(array $config)
    {
        $config['type'] = 'media';
        $config['mimeTypes'] = ['image'];

        return parent::transformConfig($config);
    }

    /**
     * Gets the field's value.
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return array the field's values, an array containing the image's thumb and full res
     *
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter = false)
    {
        $result = parent::get($saved_fields, true);

        if ($skip_filter) {
            return $result;
        }

        return apply_filters(
            'aeria_get_picture',
            $result,
            $this->config
        );
    }
}
