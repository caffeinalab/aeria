<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Fields\BaseField;
use Aeria\Field\FieldNodeFactory;
use Aeria\Field\Interfaces\FieldInterface;
/**
 * Repeater is the class that represents a repeater field
 * 
 * @category Field
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class RepeaterField extends BaseField
{
    public $is_multiple_field = true;
    /**
     * Gets the field's value
     *
     * @param array $metas      the FieldGroup's saved fields
     * @param bool  $skip_filter whether to skip or not WP's filter
     *
     * @return array the field's children
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get(array $metas, bool $skip_filter = false) {
        $stored_value = (int)parent::get($metas, true);
        $children = [];

        $fields = $this->config['fields'];

        for ($i = 0; $i < $stored_value; ++$i) {
            $child = new \StdClass();
            for ($j = 0; $j < count($fields); ++$j) {
                $field_config = $fields[$j];

                $child->{$field_config['id']} = FieldNodeFactory::make(
                    $this->key, $field_config, $this->sections, $i
                )->get($metas);
            }

            if (count($fields) === 1) {
                $children[] = $child->{$fields[0]['id']};
            } else {
                $children[] = $child;
            }
        }
        if(!$skip_filter)
          $children = apply_filters('aeria_get_repeater', $children, $this->config);
        return $children;
    }
    /**
     * Gets the field's value and its errors
     *
     * @param array $metas  the FieldGroup's saved fields
     * @param array $errors the saving errors
     *
     * @return array the field's config, hydrated with values and errors
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getAdmin(array $metas, array $errors) {
        $stored_value = (int)parent::get($metas, true);
        $children = [];

        $fields = $this->config['fields'];

        for ($i = 0; $i < $stored_value; ++$i) {
            $child = [];
            for ($j = 0; $j < count($fields); ++$j) {
                $field_config = $fields[$j];

                $child[] = FieldNodeFactory::make(
                    $this->key, $field_config, $this->sections, $i
                )->getAdmin($metas, $errors);
            }
            $children[] = $child;
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
    public function set($context_ID, $context_type, array $metas, array $new_values, $validator_service, $query_service) {
        $stored_values = (int)parent::set($context_ID, $context_type, $metas, $new_values, $validator_service, $query_service)["value"];
        if (!$stored_values) {
            return;
        }

        $fields = $this->config['fields'];

        for ($i = 0; $i < $stored_values; ++$i) {
            for ($j = 0; $j < count($fields); ++$j) {
                FieldNodeFactory::make(
                    $this->key, $fields[$j], $this->sections, $i
                )->set($context_ID, $context_type, $metas, $new_values, $validator_service, $query_service);
            }
        }
        $this->deleteOrphanMeta($this->key, $metas, $new_values);
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
