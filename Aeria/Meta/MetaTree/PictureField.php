<?php

namespace Aeria\Meta\MetaTree;

class PictureField extends MetaField
{
    public function get(array $metas) {
      $value = (int) parent::get($metas);
      $thumb = wp_get_attachment_image_src($value)[0];
      $full = wp_get_attachment_image_src($value, "full")[0];

      return [
        'thumb' => $thumb,
        'full' => $full
      ];
    }

    public function getAdmin(array $metas, array $errors) {
      $stored_value = parent::get($metas);
      $result = [];
      $result['value'] = (int)$stored_value;
      $result['url'] = wp_get_attachment_image_src($result['value'])[0];

      return array_merge(
        $this->config,
        $result
      );
    }
}
