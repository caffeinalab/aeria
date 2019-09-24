<?php

namespace Aeria\Field;

use Aeria\Structure\Interfaces\TransientableInterface;
use Aeria\Structure\Traits\Transientable;
/**
 * FieldError manages the errors while saving fields
 * 
 * @category Field
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class FieldError implements TransientableInterface
{
    use Transientable;

    protected $listErrors = [];
    private static $_field_error_instances = [];
    /**
     * Constructs the FieldError singleton
     *
     * @param array $list the field errors list
     *
     * @return void
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function __construct($list = [])
    {
        $this->listErrors = $list;
    }
    /**
     * Creates the field error instance
     *
     * @param int $post_id the saved post ID
     *
     * @return FieldError the instance
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function make($post_id = null)
    {
        if ($post_id == null) {
            throw new \Exception('Unable to get errors, pass a post_id');
        }
        $id = "{$post_id}-errors";
        if (!isset(self::$_field_error_instances[$id])) {
            $field_error = self::decodeTransient($id);
            if (!$field_error) {
                static::$_field_error_instances[$id] = new FieldError();
            } else {
                static::$_field_error_instances[$id] = $field_error;
            }
        }
        return self::$_field_error_instances[$id];
    }
    /**
     * Returns the errors list
     *
     * @return array the errors list
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getList()
    {
        return $this->listErrors;
    }
    /**
     * Adds a new error
     *
     * @param string $key   the field's key
     * @param array  $error the error array
     * 
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function addError($key, $error)
    {
        $this->listErrors[$key] = $error;
    }
    /**
     * Serializes an error for the frontend
     *
     * @param string $input_ID the field's ID
     *
     * @return string|null the encoded error
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function serializeError($input_ID)
    {
        if (isset($this->listErrors[$input_ID])) {
            return json_encode($this->listErrors[$input_ID]);
        }
        return null;
    }

}
