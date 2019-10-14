<?php

namespace Aeria\Field;

class SelectField extends BaseField
{
    public $isMultipleField = false;

    public function get(array $savedFields) {
      $values = parent::get($savedFields);
      if(isset($this->config['multiple']) && $this->config['multiple']){
        $values = explode(',', $values);
      }
      return $values;
    }

    public function getAdmin(array $savedFields, array $errors) {
      $savedValues = parent::getAdmin($savedFields, $errors);

      return array_merge(
        $this->config,
        $savedValues
      );
    }
}
