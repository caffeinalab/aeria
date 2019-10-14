<?php

namespace Aeria\Field;

class SwitchField extends BaseField
{
    public $isMultipleField = false;

    public function get(array $savedFields) {
      return filter_var(parent::get($savedFields), FILTER_VALIDATE_BOOLEAN);
    }

    public function getAdmin(array $savedFields, array $errors) {
      $savedValues = parent::getAdmin($savedFields, $errors);
      $savedValues['value'] = filter_var($savedValues['value'], FILTER_VALIDATE_BOOLEAN);
      return $savedValues;
    }

    public function set($context_ID, $context_type, array $savedFields, $validator_service, $query_service)
    {
      if( !isset($_POST[$this->key]) || !filter_var($_POST[$this->key], FILTER_VALIDATE_BOOLEAN)) {
        $_POST[$this->key] = 'false';
      } else {
        $_POST[$this->key] = 'true';
      }
      parent::set($context_ID, $context_type, $savedFields, $validator_service, $query_service);
    }
}
