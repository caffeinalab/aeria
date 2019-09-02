<?php

namespace Aeria\Field\Fields;

use Aeria\Aeria;
use Aeria\Field\FieldError;
use Aeria\Validator\Validator;
use Aeria\Structure\Node;
use Aeria\Field\Interfaces\FieldInterface;

class BaseField extends Node implements FieldInterface
{
    public $isMultipleField = false;

    public function __construct($parentKey, $config, $sections, $index = null) {
      $this->parentKey = $parentKey;
      $this->config = $config;
      $this->id = isset($config['id'])
        ? $config['id']
        : null;
      $this->index = $index;
      $this->key = $this->getKey();
      $this->sections = $sections;
    }

    public function shouldBeChildOf(Node $possibleParent)
    {
      if ($possibleParent->isMultipleField) {
        if (preg_match('/^'.$possibleParent->getKey().'.{1,}/', $this->getKey())){
          return true;
        }
        else
          return false;
      }
      else if (get_class($possibleParent)=="RootNode") // Check if possibleParent is root
        return true;
      else
        return false;
    }

    public function getKey() {
      return $this->parentKey
        . (!is_null($this->index) ? '-'.$this->index : '')
        . (!is_null($this->id) ? '-'.$this->id : '');
    }

    public function get(array $savedFields, bool $skipFilter = false) {
      if (!isset($savedFields[$this->key]))
      {
        return null;
      }
      if(!$skipFilter){
        $savedFields[$this->key] = apply_filters("aeria_get_base", $savedFields[$this->key], $this->config);
        $savedFields[$this->key] = apply_filters("aeria_get_".$this->key, $savedFields[$this->key], $this->config);
      }
      if (is_array($savedFields[$this->key])) {
        return $savedFields[$this->key][0];
      } else {
        return $savedFields[$this->key];
      }
    }

    public function getAdmin(array $savedFields, array $errors) {

      if (isset($errors[$this->key])) {
        $result = [
          'value' => $errors[$this->key]["value"],
          'error' => $errors[$this->key]["message"]
        ];
      } else {
        $result = [
          'value' => $this->get($savedFields, true),
        ];
      }

      if(is_null($result['value'])){
        return $this->config;
      }

      return array_merge(
        $this->config,
        $result
      );
    }

    public function set($context_ID, $context_type, array $savedFields, array $newValues, $validator_service, $query_service)
    {
        $value = isset($newValues[$this->key]) ? $newValues[$this->key] : null;
        $old = isset($savedFields[$this->key][0]) ? $savedFields[$this->key][0] : '';
        $value = apply_filters("aeria_set_".$this->key, $value, $this->config);
        if ($value == $old) return ["value" => $value];
        if (is_null($value) || $value == '') {
          $this->deleteField($context_ID, $context_type, $query_service);
          return ["value" => $value];
        } else {
          $validators=(isset($this->config["validators"])) ? $this->config["validators"] : "";
          $error=$validator_service->validate($value, $validators);

          if(!$error["status"]){
            $this->saveField($context_ID, $context_type, $value, $old);
            return ["value" => $value];
          } else {
            FieldError::make($context_ID)
              ->addError($this->key, $error);
            return $error;
          }
        }
    }

    private function saveField($context_ID, $context_type, $value, $old){
      switch ($context_type) {
        case 'options':
          update_option($this->key, $value);
          break;
        case 'meta':
          update_post_meta($context_ID, $this->key, $value, $old);
          break;
        default:
          throw new Exception("Node context is not valid.");
          break;
      }
    }

    private function deleteField($context_ID, $context_type, $query_service){
      switch ($context_type) {
        case 'options':
          $query_service->deleteOption($this->key);
          break;
        case 'meta':
          $query_service->deleteMeta($context_ID, $this->key);
          break;

        default:
          throw new Exception("Node context is not valid.");
          break;
      }
    }
}
