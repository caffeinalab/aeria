<?php

namespace Aeria\Field\Fields;

/**
 * Media is the class that represents a media field.
 *
 * @category Field
 *
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class MediaField extends BaseField
{
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
        $id = parent::get($saved_fields, true);
        if (is_null($id)) {
            return null;
        }
        $value = (int) $id;
        $attachment = get_post($id);
        $result = [
            'meta' => $attachment,
        ];
        if (strpos($attachment->post_mime_type, 'image') !== false) {
            if (isset($this->config['get_sizes'])) {
                $sizes = $this->config['get_sizes'];
            } else {
                $sizes = array_merge(['full'], get_intermediate_image_sizes());
            }
            foreach ($sizes as $size) {
                $result[$size] = wp_get_attachment_image_src($value, $size);
                if ($result[$size][2] && $result[$size][1]) {
                    $result[$size][3] = $result[$size][2] / $result[$size][1];
                }
            }
        } else {
            $result['full'] = [wp_get_attachment_url($value)];
            $media_meta = wp_get_attachment_metadata($value);
            if (isset($media_meta['width']) && isset($media_meta['height'])) {
                $result['full'][] = $media_meta['width'];
                $result['full'][] = $media_meta['height'];
                $result['full'][] = $media_meta['width'] / $media_meta['height'];
            }
        }

        if ($skip_filter) {
            return $result;
        }

        return apply_filters(
            'aeria_get_media',
            $result,
            $this->config
        );
    }

    /**
     * Gets the field's value and its errors.
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param array $errors       the saving errors
     *
     * @return array the field's config, hydrated with values and errors
     *
     * @since  Method available since Release 3.0.0
     */
    public function getAdmin(array $saved_fields, array $errors)
    {
        $stored_value = parent::get($saved_fields, true);
        $result = [];
        $result['url'] = '';
        $result['value'] = (int) $stored_value;
        $attachment = get_post($result['value']);

        if (is_object($attachment)) {
            $result['fileName'] = basename($attachment->guid);
            $result['mimeType'] = $attachment->post_mime_type;
            if (strpos($attachment->post_mime_type, 'image') !== false) {
                $result['url'] = wp_get_attachment_image_src($result['value'])[0];
            } else {
                $result['showFilename'] = true;
                $result['naturalSize'] = true;
                $result['url'] = wp_mime_type_icon($attachment->post_mime_type);
            }
        }

        return array_merge(
            $this->config,
            $result
        );
    }
}
