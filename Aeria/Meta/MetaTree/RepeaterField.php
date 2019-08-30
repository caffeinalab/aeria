<?php

namespace Aeria\Meta\MetaTree;

use Aeria\Meta\MetaTree\MetaField;
use Aeria\Meta\MetaTree\TreeFactory;

class RepeaterField extends MetaField
{
    public function get(array $metas) {
      $length = (int)parent::get($metas);
      $children = [];

      $child_config = $this->config['field'];

      for ($i = 0; $i < $length; ++$i) {
        $children[] = TreeFactory::make(
          $this->key, $child_config, $i
        )->get($metas);
      }

      return $children;
    }

    public function getAdmin(array $metas, array $errors) {
      $stored_value = (int)parent::get($metas);

      $child_config = $this->config['field'];
      $children = [];

      for ($i = 0; $i < $stored_value; ++$i) {
        $children[] = TreeFactory::make(
          $this->key, $child_config, $i
        )->getAdmin($metas, $errors);
      }

      return array_merge(
        $this->config,
        [
          "value" => $stored_value,
          "children" => $children
        ]
      );
    }

    public function set(array $metas, $validator_service, $query_service) {
      $stored_values = (int)parent::set($metas, $validator_service, $query_service)["value"];
      if(!$stored_values) {
        return;
      }

      $child_config = $this->config['field'];

      for ($i = 0; $i < $stored_values; ++$i) {
        TreeFactory::make(
          $this->key, $child_config, $i
        )->set($metas, $validator_service, $query_service);
      }
      $this->deleteOrphanMeta($this->key,$metas);
    }

    private function deleteOrphanMeta ($parentKey, $metas)
    {
        $oldFields=static::pregGrepKeys("/^".$parentKey."/", $metas);
        $newFields=static::pregGrepKeys("/^".$parentKey."/", $_POST);
        $deletableFields=array_diff_key($oldFields,$newFields);
        foreach($deletableFields as $deletableKey => $deletableField){
            delete_post_meta($_POST['post_ID'],$deletableKey);
        }
    }

    private static function pregGrepKeys($pattern, $input) {
      return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input))));
    }
}
