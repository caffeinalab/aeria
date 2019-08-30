<?php

namespace Aeria\Meta\MetaTree;

class SelectField extends MetaField
{
    public function get(array $metas) {
      $values = parent::get($metas);
      if(isset($this->config['multiple']) && $this->config['multiple']){
        $values = explode(',', $values);
      }
      return $values;
    }

    public function getAdmin(array $metas, array $errors) {
      $savedValues = parent::getAdmin($metas,$errors);

      return array_merge(
        $this->config,
        $savedValues
      );
    }
}
