<?php

namespace Aeria\Meta\MetaTree;

use Aeria\Meta\Meta;
use Aeria\Meta\MetaTree\MetaField;
use Aeria\Meta\MetaTree\TreeFactory;

class SectionsField extends MetaField
{
    private function getSectionConfig($type){
      return Meta::$sections[$type];
    }

    private function getTitle($index){
      // TODO: decide if headerTitle is needed inside the theme
      return new MetaField(
        $this->key,
        ["id" => 'headerTitle', "validators" =>"isShort" ],
        $index
      );
    }

    public function get(array $metas) {
      $types = parent::get($metas);
      if(is_null($types) || $types == ''){
        return [];
      }

      $types = explode(',', $types);
      $children = [];

      foreach ($types as $i => $type) {
        $section_config = $this->getSectionConfig($type);
        $field_result = new \StdClass();
        $field_result->type = $type;
        $field_result->data = new \StdClass();

        foreach ($section_config['fields'] as $field_index => $field_config) {
          $field_result->data->{$field_config['id']} = TreeFactory::make(
            $this->key, $field_config, $i
          )->get($metas);
        }

        $children[] = $field_result;
      }

      return $children;
    }

    public function getAdmin(array $metas, array $errors) {
      $stored_value = parent::get($metas);
      $stored_value = (bool)$stored_value ? explode(',', $stored_value) : [];

      $children = [];

      foreach ($stored_value as $type_index => $type) {
        $section_config = $this->getSectionConfig($type);

        $fields = [];

        foreach ($section_config['fields'] as $field_index => $field_config) {
          $fields[] = array_merge(
            $field_config,
            TreeFactory::make(
              $this->key, $field_config, $type_index
            )->getAdmin($metas, $errors)
          );
        }

        $children[] = array_merge(
          $section_config,
          [
            'title' => $this->getTitle($type_index)->get($metas),
            'fields' => $fields
          ]
        );
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
      $stored_value = parent::set($metas, $validator_service, $query_service)["value"];
      $stored_value = (bool)$stored_value ? explode(',', $stored_value) : [];

      foreach ($stored_value as $type_index => $type) {
        $section_config = $this->getSectionConfig($type);

        // save title
        $this->getTitle($type_index)->set($metas, $validator_service, $query_service);
        // save children
        foreach ($section_config['fields'] as $field_index => $field_config) {
          TreeFactory::make(
            $this->key, $field_config, $type_index
          )->set($metas, $validator_service, $query_service, $query_service);
        }
        // remove orphans
        $this->deleteOrphanMeta($this->key.'-'.$type_index, $metas);
      }
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
