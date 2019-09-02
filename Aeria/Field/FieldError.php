<?php

namespace Aeria\Field;

use Aeria\Structure\Interfaces\TransientableInterface;
use Aeria\Structure\Traits\Transientable;

class FieldError implements TransientableInterface
{
    use Transientable;

    protected $listErrors = [];
    private static $_field_error_instances = [];

    private function __construct($list = [])
    {
        $this->listErrors = $list;
    }

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

    public function getList()
    {
        return $this->listErrors;
    }

    public function addError($key, $error)
    {
        $this->listErrors[$key] = $error;
    }

    public function serializeError($inputId)
    {
        if (isset($this->listErrors[$inputId])) {
            return json_encode($this->listErrors[$inputId]);
        }
        return null;
    }

}
