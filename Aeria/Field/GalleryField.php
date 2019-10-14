<?php

namespace Aeria\Field;

use Aeria\Field\PictureField;

class GalleryField extends BaseField
{

    public $isMultipleField = false;


    public function get(array $savedFields) {
      $length = (int)parent::get($savedFields);
      $children = [];

      for ($i = 0; $i < $length; ++$i) {
        $children[] = (new PictureField(
          $this->key, ['id' => 'picture'], $this->sections, $i
        ))->get($savedFields);
      }
      return $children;
    }

    public function getAdmin(array $savedFields, array $errors) {
      $stored_value = parent::get($savedFields);
      $result = [];
      $result['value'] = (int)$stored_value;
      $result['children'] = [];

      for ($i = 0; $i < $result['value']; ++$i) {
        $result['children'][] = (new PictureField(
          $this->key, ['id' => 'picture'], $this->sections, $i
        ))->getAdmin($savedFields, $errors);
      }
      return array_merge(
        $this->config,
        $result
      );
    }

    public function set($context_ID, $context_type, array $savedFields, $validator_service, $query_service) {
      $stored_values = (int)parent::set($context_ID, $context_type, $savedFields, $validator_service, $query_service)["value"];
      if(!$stored_values) {
        return;
      }
      for ($i = 0; $i < $stored_values; ++$i) {
        (new PictureField(
          $this->key, ['id' => 'picture'], $this->sections, $i
        ))->set($context_ID, $context_type, $savedFields, $validator_service, $query_service);
      }
    }
}
