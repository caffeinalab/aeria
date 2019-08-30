<?php

namespace Aeria\Field;

class PictureField extends BaseField
{

    public $isMultipleField = false;

    public function get(array $savedFields, bool $skipFilter = false) {
        $value = (int) parent::get($savedFields);
        $thumb = wp_get_attachment_image_src($value)[0];
        $full = wp_get_attachment_image_src($value, "full")[0];
        if($skipFilter)
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


    public function getAdmin(array $savedFields, array $errors) {
        $stored_value = parent::get($savedFields, true);
        $result = [];
        $result['value'] = (int)$stored_value;
        $result['url'] = wp_get_attachment_image_src($result['value'])[0];

        return array_merge(
            $this->config,
            $result
        );
    }
}
