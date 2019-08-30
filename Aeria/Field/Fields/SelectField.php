<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Interfaces\FieldInterface;

class SelectField extends BaseField
{
    public $isMultipleField = false;

    public function get(array $savedFields, bool $skipFilter = false) {
        $values = parent::get($savedFields, true);

        if (isset($this->config['multiple']) && $this->config['multiple'] && $values!="") {
            $values = explode(',', $values);
        }
        if (empty($values)) {

            return null;
        }
        if(!$skipFilter)
          $values = apply_filters("aeria_get_select", $values, $this->config);
        return $values;
    }

    public function getAdmin(array $savedFields, array $errors) {
        $savedValues = parent::getAdmin($savedFields, $errors, true);
        return array_merge(
            $this->config,
            $savedValues
        );
    }
}
