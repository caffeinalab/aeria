<?php

namespace Aeria\Field;

use Aeria\Meta\Meta;
use Aeria\Field\BaseField;
use Aeria\Field\SwitchField;
use Aeria\Field\FieldNodeFactory;

class SectionsField extends BaseField
{
  public $isMultipleField = true;

    private function getSectionConfig($type){
      return $this->sections[$type];
    }

    private function getTitle($index){
      // TODO: decide if headerTitle is needed inside the theme
      return new BaseField(
        $this->key,
        ["id" => 'headerTitle', "validators" =>"" ],
        $this->sections,
        $index
      );
    }

    private function getDraftMode($index){
      return new SwitchField(
        $this->key,
        ["id" => 'draft', "validators" => "" ],
        $this->sections,
        $index
      );
    }

    public function get(array $metas, bool $skipFilter = false) {
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
          $field_result->data->{$field_config['id']} = FieldNodeFactory::make(
            $this->key, $field_config, $this->sections, $i
          )->get($metas);
        }

        $isDraft = $this->getDraftMode($i)->get($metas);
        if(!$isDraft){
          $children[] = $field_result;
        }
      }
      if(!$skipFilter)
        $children = apply_filters('aeria_get_sections', $children, $this->config);
      return $children;
    }

    public function getAdmin(array $metas, array $errors) {
      $stored_value = parent::get($metas, true);
      $stored_value = (bool)$stored_value ? explode(',', $stored_value) : [];

      $children = [];

      foreach ($stored_value as $type_index => $type) {
        $section_config = $this->getSectionConfig($type);

        $fields = [];

        foreach ($section_config['fields'] as $field_index => $field_config) {
          $fields[] = array_merge(
            $field_config,
            FieldNodeFactory::make(
              $this->key, $field_config, $this->sections, $type_index
            )->getAdmin($metas, $errors)
          );
        }

        $children[] = array_merge(
          $section_config,
          [
            'title' => $this->getTitle($type_index)->get($metas),
            'isDraft' => $this->getDraftMode($type_index)->get($metas),
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

    public function set($context_ID, $context_type, array $metas, $validator_service, $query_service) {
      $stored_value = parent::set($context_ID, $context_type, $metas, $validator_service, $query_service)["value"];
      $stored_value = (bool)$stored_value ? explode(',', $stored_value) : [];

      foreach ($stored_value as $type_index => $type) {
        $section_config = $this->getSectionConfig($type);

        // save title
        $this->getTitle($type_index)->set($context_ID, $context_type, $metas, $validator_service, $query_service);

        // save status
        $this->getDraftMode($type_index)->set($context_ID, $context_type, $metas, $validator_service, $query_service);

        // save children
        foreach ($section_config['fields'] as $field_index => $field_config) {
          FieldNodeFactory::make(
            $this->key, $field_config, $this->sections, $type_index
          )->set($context_ID, $context_type, $metas, $validator_service, $query_service, $query_service);
        }
        // remove orphans
        $this->deleteOrphanMeta($this->key.'-'.$type_index, $metas);
      }
    }

    private function deleteOrphanMeta($parentKey, $metas)
    {
        $oldFields=static::pregGrepKeys("/^".$parentKey."/", $metas);
        $newFields=static::pregGrepKeys("/^".$parentKey."/", $_POST);
        $deletableFields = array_diff_key($oldFields, $newFields);
        foreach ($deletableFields as $deletableKey => $deletableField){
            delete_post_meta($_POST['post_ID'], $deletableKey);
        }
    }

    private static function pregGrepKeys($pattern, $input) {
      return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input))));
    }
}
