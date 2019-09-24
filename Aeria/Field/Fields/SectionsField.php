<?php

namespace Aeria\Field\Fields;

use Aeria\Meta\Meta;
use Aeria\Field\Fields\BaseField;
use Aeria\Field\Fields\SwitchField;
use Aeria\Field\FieldNodeFactory;
use Aeria\Field\Interfaces\FieldInterface;
use Aeria\Field\Exceptions\NonExistentConfigException;

/**
 * Sections is the class that represents a section field
 * 
 * @category Field
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class SectionsField extends BaseField
{
  public $isMultipleField = true;
    /**
     * Gets a section configuration
     *
     * @param string $type the searched section's configuration
     *
     * @return array the section's configuration
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function getSectionConfig($type)
    {
        if (isset($this->sections[$type]))
          return $this->sections[$type];
        else 
          throw new NonExistentConfigException();

    }
    /**
     * Gets a section's title
     *
     * @param int $index the section's index
     *
     * @return BaseField the section's title field
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function getTitle($index)
    {
        return new BaseField(
            $this->key,
            ["id" => 'headerTitle', "validators" =>"" ],
            $this->sections,
            $index
        );
    }
    /**
     * Gets the "draft" switch
     *
     * @param int $index the section's index
     *
     * @return array the section's draft switch
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function getDraftMode($index)
    {
        return new SwitchField(
            $this->key,
            ["id" => 'draft', "validators" => "" ],
            $this->sections,
            $index
        );
    }
    /**
     * Gets the field's value
     *
     * @param array $metas       the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return mixed the field's values, an array containing the sections' values
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get(array $metas, bool $skip_filter = false) 
    {
        $types = parent::get($metas, true);
        if (is_null($types) || $types == '') {
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
            if (!$isDraft) {
                $children[] = $field_result;
            }
        }
        if(!$skip_filter)
          $children = apply_filters('aeria_get_sections', $children, $this->config);
        return $children;
    }
    /**
     * Gets the field's value and its errors
     *
     * @param array $metas  the FieldGroup's saved fields
     * @param array $errors the saving errors
     *
     * @return array the sections' config, hydrated with values and errors
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getAdmin(array $metas, array $errors) 
    {
        $stored_value = parent::get($metas, true);
        $stored_value = (bool)$stored_value ? explode(',', $stored_value) : [];

        $children = [];

        foreach ($stored_value as $type_index => $type) {
            $section_config = $this->getSectionConfig($type);

            $fields = [];
            if (isset($section_config['fields'])) {
                foreach ($section_config['fields'] as $field_index => $field_config) {
                    $fields[] = array_merge(
                        $field_config,
                        FieldNodeFactory::make(
                            $this->key, $field_config, $this->sections, $type_index
                        )->getAdmin($metas, $errors)
                    );
                }
            }
            if (is_array($section_config)) {
                $children[] = array_merge(
                    $section_config,
                    [
                      'title' => $this->getTitle($type_index)->get($metas),
                      'isDraft' => $this->getDraftMode($type_index)->get($metas),
                      'fields' => $fields
                    ]
                );
            }
        }
        return array_merge(
            $this->config,
            [
              "value" => $stored_value,
              "children" => $children
            ]
        );
    }
    /**
     * Saves the new values to the fields.
     *
     * @param int       $context_ID        the context ID. For posts, post's ID
     * @param string    $context_type      the context type. Right now, options|meta
     * @param array     $metas             the saved fields
     * @param array     $new_values        the values we're saving
     * @param Validator $validator_service Aeria's validator service
     * @param Query     $query_service     Aeria's query service
     * 
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function set($context_ID, $context_type, array $metas, array $new_values, $validator_service, $query_service) 
    {
        $stored_value = parent::set($context_ID, $context_type, $metas, $new_values, $validator_service, $query_service)["value"];
        $stored_value = (bool)$stored_value ? explode(',', $stored_value) : [];

        foreach ($stored_value as $type_index => $type) {
            $section_config = $this->getSectionConfig($type);

            // save title
            $this->getTitle($type_index)->set($context_ID, $context_type, $metas, $new_values, $validator_service, $query_service);

            // save status
            $this->getDraftMode($type_index)->set($context_ID, $context_type, $metas, $new_values, $validator_service, $query_service);

            // save children
            foreach ($section_config['fields'] as $field_index => $field_config) {
                FieldNodeFactory::make(
                    $this->key, $field_config, $this->sections, $type_index
                )->set($context_ID, $context_type, $metas, $new_values, $validator_service, $query_service, $query_service);
            }
            // remove orphans
            $this->deleteOrphanMeta($this->key.'-'.$type_index, $metas, $new_values);
        }
    }
    /**
     * Deletes the metas that lose a parent :(
     *
     * @param string $parent_key the parent's key
     * @param array  $metas      the saved fields
     * @param array  $new_values the values we're saving
     * 
     * @return void
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function deleteOrphanMeta($parent_key, $metas, $new_values)
    {
        $oldFields=static::pregGrepKeys("/^".$parent_key."/", $metas);
        $newFields=static::pregGrepKeys("/^".$parent_key."/", $new_values);
        $deletableFields = array_diff_key($oldFields, $newFields);
        foreach ($deletableFields as $deletableKey => $deletableField) {
            delete_post_meta($new_values['post_ID'], $deletableKey);
        }
    }
    /**
     * Helper function for deleteOrphanMeta: gets the orphan keys
     *
     * @param string $pattern the parent's key RegEx
     * @param array  $input   the values we're saving
     * 
     * @return array the orphans to be deleted
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private static function pregGrepKeys($pattern, $input) 
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input))));
    }
}
