<?php

namespace Aeria\Field\Fields;

use Aeria\Aeria;
use Aeria\Field\FieldError;
use Aeria\Validator\Validator;
use Aeria\Structure\Node;
use Aeria\Field\Interfaces\FieldInterface;

/**
 * BaseField is the class that represents every Aeria field
 *
 * @category Field
 * @package  Aeria
 * @author   Andrea Longo <andrea.longo@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class BaseField extends Node implements FieldInterface
{
    public $is_multiple_field = false;

    /**
     * Transform the config array; note that this does not operate on
     * `$this->config`: this way it can be called from outside
     *
     * @param array $config    the field's config
     *
     * @return array        the transformed config
     */
    public static function transformConfig(array $config) {
        return $config;
    }
    /**
     * Constructs the field
     *
     * @param string $parent_key the field's parent key
     * @param array  $config      the field's config
     * @param array  $sections   Aeria's sections config
     * @param array  $index      index for multiple fields
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct($parent_key, $config, $sections, $index = null) {
        $this->parent_key = $parent_key;
        $this->config = static::transformConfig($config);
        $this->id = isset($config['id'])
          ? $config['id']
          : null;
        $this->index = $index;
        $this->key = $this->getKey();
        $this->sections = $sections;
    }
    /**
     * Checks whether a field should be child of another
     *
     * @param Node $possible_parent the field's possible parent
     *
     * @return bool whether the field should be child of $possible_parent
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function shouldBeChildOf(Node $possible_parent)
    {
        if ($possible_parent->is_multiple_field) {
            if (preg_match('/^'.$possible_parent->getKey().'.{1,}/', $this->getKey())) {
                return true;
            } else {
                return false;
            }
        }
        else if (get_class($possible_parent)=="RootNode") // Check if possible_parent is root
          return true;
        else
          return false;
    }
    /**
     * Gets the field full key
     *
     * @return string the field's key
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getKey()
    {
        return $this->parent_key
          . (!is_null($this->index) ? '-'.$this->index : '')
          . (!is_null($this->id) ? '-'.$this->id : '');
    }
    /**
     * Gets the field's value
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return mixed the field's value
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter = false) {
        if (!isset($saved_fields[$this->key])) {
            return null;
        }
        if (!$skip_filter) {
            $saved_fields[$this->key] = apply_filters("aeria_get_base", $saved_fields[$this->key], $this->config);
            $saved_fields[$this->key] = apply_filters("aeria_get_".$this->key, $saved_fields[$this->key], $this->config);
        }
        if (is_array($saved_fields[$this->key])) {
            return $saved_fields[$this->key][0];
        } else {
            return $saved_fields[$this->key];
        }
    }
    /**
     * Gets the field's value and its errors
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param array $errors      the saving errors
     *
     * @return array the field's config, hydrated with values and errors
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getAdmin(array $saved_fields, array $errors)
    {
        if (isset($errors[$this->key])) {
            $result = [
              'value' => $errors[$this->key]["value"],
              'error' => $errors[$this->key]["message"]
            ];
        } else {
            $result = [
              'value' => $this->get($saved_fields, true),
            ];
        }

        if (is_null($result['value'])) {
            return $this->config;
        }

        return array_merge(
            $this->config,
            $result
        );
    }
    /**
     * Saves the new values to the fields.
     *
     * @param int       $context_ID        the context ID. For posts, post's ID
     * @param string    $context_type      the context type. Right now, options|meta
     * @param array     $saved_fields       the saved fields
     * @param array     $new_values        the values we're saving
     * @param Validator $validator_service Aeria's validator service
     * @param Query     $query_service     Aeria's query service
     *
     * @return array the results of the saving
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function set($context_ID, $context_type, array $saved_fields, array $new_values, $validator_service, $query_service)
    {
        $value = isset($new_values[$this->key]) ? $new_values[$this->key] : null;
        $old = isset($saved_fields[$this->key][0]) ? $saved_fields[$this->key][0] : '';
        $value = apply_filters("aeria_set_".$this->key, $value, $this->config);
        if ($value == $old) return ["value" => $value];
        if (is_null($value) || $value == '') {
            $this->deleteField($context_ID, $context_type, $query_service);
            return ["value" => $value];
        } else {
            $validators=(isset($this->config["validators"])) ? $this->config["validators"] : "";
            $error=$validator_service->validate($value, $validators);

            if (!$error["status"]) {
                $this->saveField($context_ID, $context_type, $value, $old);
                return ["value" => $value];
            } else {
                FieldError::make($context_ID)
                    ->addError($this->key, $error);
                return $error;
            }
        }
    }
    /**
     * Saves a single field to the DB.
     *
     * @param int    $context_ID   the context ID. For posts, post's ID
     * @param string $context_type the context type. Right now, options|meta
     * @param mixed  $value        the new value
     * @param mixed  $old          the old value
     *
     * @return void
     * @throws Exception if the node context is invalid
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function saveField($context_ID, $context_type, $value, $old)
    {
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
    /**
     * Deletes a field value
     *
     * @param int    $context_ID    the context ID. For posts, post's ID
     * @param string $context_type  the context type. Right now, options|meta
     * @param Query  $query_service Aeria's query service
     *
     * @return void
     * @throws Exception if the node context is invalid
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
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
