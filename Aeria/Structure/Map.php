<?php

namespace Aeria\Structure;

use JsonSerializable;

class Map implements JsonSerializable
{

    protected $fields = [];

    public function __construct(/* ?array */ $fields = null) // : void
    {
        if ($fields) {
            $this->load($fields);
        }
    }

    public function &all() {
        return $this->fields;
    }

    public function get($key, $default = null) // : mixed
    {
        $element =& $this->find($key);

        return (!is_null($element)) ? $element : $default;
    }

    public function set(string $key, /* mixed */ $value) : bool
    {
        $element =& $this->find($key, true);
        $element = is_callable($value) ? call_user_func($value) : $value;
        return true;
    }

    public function delete(string $key, bool $compact = true) : bool
    {
        $result = $this->set($key, null);

        if ($compact) {
            $this->compact();
        }

        return $result;
    }

    public function exists(string $key) : bool
    {
        return !is_null($this->find($key));
    }

    public function clear() : bool
    {
        $this->fields = [];
        return true;
    }

    public function load(array $fields) : bool
    {
        $this->fields = $fields;
        return true;
    }

    public function merge(array $array) : bool
    {
        $this->fields = array_replace_recursive($this->fields, $array);
        return true;
    }

    public function compact() : bool
    {
        $callback = function ($element) {
            return !is_null($element);
        };

        $this->fields = static::compact_array_filter($this->fields, $callback);

        return true;
    }

    protected static function compact_array_filter(
        array $input,
        /* ?callable */ $callback = null
    ) : array {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = static::compact_array_filter($value, $callback);
            }
        }

        return array_filter($input, $callback);
    }

    public function &find(string $key_path, bool $create = false) // : mixed
    {
        if ($create) {
            $fields =& $this->fields;
        } else {
            $fields = $this->fields;
        }

        $key_parts = explode('.', $key_path);

        foreach ($key_parts as $key_part) {
            if (!$create && !isset($fields[$key_part])) {
                $fields = null;
                break;
            }

            $fields =& $fields[$key_part];
        }

        return $fields;
    }

    public function jsonSerialize() : array
    {
        return $this->fields;
    }
}
