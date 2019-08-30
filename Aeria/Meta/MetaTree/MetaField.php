<?php

namespace Aeria\Meta\MetaTree;

use Aeria\Meta\FieldError;
use Aeria\Validator\Validator;
use Aeria\Aeria;

class MetaField
{
    public function __construct($parentKey, $config, $index = null) {
      $this->parentKey = $parentKey;
      $this->config = $config;
      $this->id = isset($config['id'])
        ? $config['id']
        : null;
      $this->index = $index;
      $this->key = $this->getKey();
    }

    public function getKey() {
      return $this->parentKey
        . (!is_null($this->index) ? '-'.$this->index : '')
        . (!is_null($this->id) ? '-'.$this->id : '');
    }

    public function get(array $metas) {
      return isset($metas[$this->key])
        ? $metas[$this->key][0]
        : null;
    }

    public function getAdmin(array $metas, array $errors) {

      if (isset($errors[$this->key])) {
        $result = [
          'value' => $errors[$this->key]["value"],
          'error' => $errors[$this->key]["message"]
        ];
      } else {
        $result = [
          'value' => $this->get($metas),
        ];
      }
      return array_merge(
        $this->config,
        $result
      );
    }

    public function set(array $metas, $validator_service, $query_service) {
      $value = isset($_POST[$this->key]) ? $_POST[$this->key] : null;
      $old = isset($metas[$this->key][0]) ? $metas[$this->key][0] : '';
      $validators=(isset($this->config["validators"])) ? $this->config["validators"] : "";
      $error=$validator_service->validate($value, $validators);
      if ($value==$old) return ["value" => $value];

      if (is_null($value) || $value == '') {
          $query_service->deleteMeta($_POST['post_ID'], $this->key);
          return ["value" => $value];
      } else {
        if(!$error["status"]){
          update_post_meta($_POST['post_ID'], $this->key, $value, $old);
          return ["value" => $value];
        }
        else{
          FieldError::getSingleton()->addError($this->key, $error);
          return $error;
        }
      }
    }

}
