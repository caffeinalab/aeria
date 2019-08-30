<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Interfaces\FieldInterface;

class SwitchField extends BaseField
{
    public $isMultipleField = false;

    public function get(array $savedFields, bool $skipFilter = false) {
      $result = filter_var(parent::get($savedFields, true), FILTER_VALIDATE_BOOLEAN);
      if(!$skipFilter)
        $result = apply_filters('aeria_get_switch', $result, $this->config);
      return $result;
    }

    public function getAdmin(array $savedFields, array $errors) {
      $savedValues = parent::getAdmin($savedFields, $errors, true);
      $savedValues['value'] = filter_var($savedValues['value'], FILTER_VALIDATE_BOOLEAN);
      return $savedValues;
    }

    public function set($context_ID, $context_type, array $savedFields, array $newValues, $validator_service, $query_service)
    {
      if( !isset($newValues[$this->key]) || !filter_var($newValues[$this->key], FILTER_VALIDATE_BOOLEAN)) {
        $newValues[$this->key] = 'false';
      } else {
        $newValues[$this->key] = 'true';
      }
      parent::set($context_ID, $context_type, $savedFields, $newValues, $validator_service, $query_service);
    }
}
