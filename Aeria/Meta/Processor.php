<?php

namespace Aeria\Meta;

use Aeria\Meta\MetaTree\TreeFactory;

class Processor
{
    protected $savedMetas;
    protected $savedErrors = [];

    public function __construct($postID, $metaboxConfig)
    {
        $this->savedMetas = get_post_meta($postID);
        $transient=FieldError::decodeTransient("{$postID}-errors");
        if (method_exists($transient,"getList")) {
            $errors=$transient->getList();
        }
        if (isset($errors)) {
          $this->savedErrors = $errors;
        }
    }

    public function getAdminMeta($metaConfig)
    {
        foreach ($metaConfig['fields'] as $index=>$config) {
            $parentKey = $metaConfig['id'];
            $metaConfig['fields'][$index] = TreeFactory::make(
              $parentKey, $config
            )->getAdmin($this->savedMetas, $this->savedErrors);
        }
        return $metaConfig;
    }

    public function getMeta($metaConfig)
    {
        foreach ($metaConfig['fields'] as $index=>$config) {
            $parentKey = $metaConfig['id'];
            $metas[$config["id"]] = TreeFactory::make(
              $parentKey, $config
            )->get($this->savedMetas);
        }
        return $metas;
    }

    public function setMeta($metaConfig, $validator_service, $query_service)
    {
      $errors=null;
      foreach ($metaConfig['fields'] as $index=>$config) {
          $parentKey = $metaConfig['id'];
          $fieldError=TreeFactory::make(
            $parentKey, $config
          )->set($this->savedMetas, $validator_service, $query_service);
          if (isset($fieldError["status"])){
            $fieldKey=$parentKey.'-'.$config["id"];
            $errors[$fieldKey] = $fieldError;
          }
      }
      
      return $errors;
    }
}
