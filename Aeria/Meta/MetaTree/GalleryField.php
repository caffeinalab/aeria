<?php

namespace Aeria\Meta\MetaTree;

use Aeria\Meta\MetaTree\PictureField;
use Aeria\Meta\MetaTree\TreeFactory;

class GalleryField extends MetaField
{
    public function get(array $metas) {
      $length = (int)parent::get($metas);
      $children = [];

      for ($i = 0; $i < $length; ++$i) {
        $children[] = (new PictureField(
          $this->key, ['id' => 'picture'], $i
        ))->get($metas);
      }

      return $children;
    }

    public function getAdmin(array $metas, array $errors) {
      $stored_value = parent::get($metas);
      $result = [];
      $result['value'] = (int)$stored_value;
      $result['children'] = [];

      for ($i = 0; $i < $result['value']; ++$i) {
        $result['children'][] = (new PictureField(
          $this->key, ['id' => 'picture'], $i
        ))->getAdmin($metas, $errors);
      }
      return array_merge(
        $this->config,
        $result
      );
    }

    public function set(array $metas, $validator_service, $query_service) {
      $stored_values = (int)parent::set($metas, $validator_service, $query_service)["value"];
      if(!$stored_values) {
        return;
      }
      for ($i = 0; $i < $stored_values; ++$i) {
        (new PictureField(
          $this->key, ['id' => 'picture'], $i
        ))->set($metas, $validator_service, $query_service);
      }
    }
}
