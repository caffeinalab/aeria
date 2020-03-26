<?php

namespace Aeria\Structure;

use JsonSerializable;

class Map implements JsonSerializable
{
    protected $fields = [];

    /**
     * Constructs the Map.
     *
     * @param array $fields initial fields
     *
     * @since  Method available since Release 3.0.0
     */
    public function __construct(/* ?array */ $fields = null) // : void
    {
        if ($fields) {
            $this->load($fields);
        }
    }

    /**
     * Returns the complete dictionary.
     *
     * @return array the saved fields
     *
     * @since  Method available since Release 3.0.0
     */
    public function &all()
    {
        return $this->fields;
    }

    /**
     * Gets a specific value by key.
     *
     * @param string $key     the searched key
     * @param mixed  $default an optional default value
     *
     * @return mixed the searched element
     *
     * @since  Method available since Release 3.0.0
     */
    public function get($key, $default = null) // : mixed
    {
        $element = &$this->find($key);

        return (!is_null($element)) ? $element : $default;
    }

    /**
     * Sets a specific value by its key.
     *
     * @param string $key   the value's key
     * @param mixed  $value the setted value
     *
     * @return bool whether the value was saved or not
     *
     * @since  Method available since Release 3.0.0
     */
    public function set(string $key, /* mixed */ $value): bool
    {
        $element = &$this->find($key, true);
        $element = is_callable($value) ? call_user_func($value) : $value;

        return true;
    }

    /**
     * Deletes a specific value by its key.
     *
     * @param string $key     the value's key
     * @param bool   $compact whether the map has to be compacted
     *
     * @return mixed the searched element
     *
     * @since  Method available since Release 3.0.0
     */
    public function delete(string $key, bool $compact = true): bool
    {
        $result = $this->set($key, null);

        if ($compact) {
            $this->compact();
        }

        return $result;
    }

    /**
     * Checks if a specific key exists.
     *
     * @param string $key the value's key
     *
     * @return bool whether the value exists
     *
     * @since  Method available since Release 3.0.0
     */
    public function exists(string $key): bool
    {
        return !is_null($this->find($key));
    }

    /**
     * Clears the map.
     *
     * @return bool whether the clearing was done correctly
     *
     * @since  Method available since Release 3.0.0
     */
    public function clear(): bool
    {
        $this->fields = [];

        return true;
    }

    /**
     * Loads the input fields.
     *
     * @param array $fields the fields we want to load
     *
     * @return bool whether the loading was done correctly
     *
     * @since  Method available since Release 3.0.0
     */
    public function load(array $fields): bool
    {
        $this->fields = $fields;

        return true;
    }

    /**
     * Merges the inserted fields with the existing ones.
     *
     * @param array $array the fields we want to merge
     *
     * @return bool whether the merging was done correctly
     *
     * @since  Method available since Release 3.0.0
     */
    public function merge(array $array): bool
    {
        $this->fields = array_replace_recursive($this->fields, $array);

        return true;
    }

    /**
     * Compacts the map.
     *
     * @return bool whether the compacting was done correctly
     *
     * @since  Method available since Release 3.0.0
     */
    public function compact(): bool
    {
        $callback = function ($element) {
            return !is_null($element);
        };

        $this->fields = static::compact_array_filter($this->fields, $callback);

        return true;
    }

    /**
     * Helper function: recursively compacts arrays.
     *
     * @param array    $input    the array to compact
     * @param callable $callback the function to be called on the array
     *
     * @return array the compacted array
     *
     * @static
     *
     * @since  Method available since Release 3.0.0
     */
    protected static function compact_array_filter(
        array $input,
        /* ?callable */ $callback = null
    ): array {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = static::compact_array_filter($value, $callback);
            }
        }

        return array_filter($input, $callback);
    }

    /**
     * Finds pointers to the requested elements.
     *
     * @param string $key_path the key path
     * @param bool   $create   whether the fields have to be editable
     *
     * @return mixed the found fields
     *
     * @since  Method available since Release 3.0.0
     */
    public function &find(string $key_path, bool $create = false) // : mixed
    {
        if ($create) {
            $fields = &$this->fields;
        } else {
            $fields = $this->fields;
        }

        $key_parts = explode('.', $key_path);

        foreach ($key_parts as $key_part) {
            if (!$create && !isset($fields[$key_part])) {
                $fields = null;
                break;
            }

            $fields = &$fields[$key_part];
        }

        return $fields;
    }

    /**
     * Serializes the dictionary into a JSON object.
     *
     * @return array the serialized object
     *
     * @since  Method available since Release 3.0.0
     */
    public function jsonSerialize(): array
    {
        return $this->fields;
    }
}
