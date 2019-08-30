<?php

namespace Aeria\Field;

class SelectField extends BaseField
{
    public $isMultipleField = false;

    public function get(array $savedFields, bool $skipFilter = false) {
        $values = parent::get($savedFields);
        if(isset($this->config['multiple']) && $this->config['multiple']){
          $values = explode(',', $values);
        }
        if(!$skipFilter)
          $values = apply_filters("aeria_get_select", $values, $this->config);
        return $values;
    }

    public function getAdmin(array $savedFields, array $errors) {
      $savedValues = parent::getAdmin($savedFields, $errors, true);
      if($savedValues["value"]==null){
        return $this->config;
      }
      return array_merge(
        $this->config,
        $savedValues
      );
    }
}
