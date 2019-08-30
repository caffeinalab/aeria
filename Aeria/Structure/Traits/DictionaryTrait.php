<?php

namespace Aeria\Structure\Traits;

use Aeria\Structure\Map;

trait DictionaryTrait
{
    protected $map;

    public function __construct() // : void
    {
        $this->map = new Map();
    }

    public function &all() : array
    {
        return $this->map->all();
    }

    public function get(string $key, $default = null) // : mixed
    {
        return $this->map->get($key, $default);
    }

    public function set(string $key, $value) : bool
    {
        return $this->map->set($key, $value);
    }

    public function delete(string $key, bool $compact = true) : bool
    {
        return $this->map->delete($key, $compact);
    }

    public function exists($key) : bool
    {
        return $this->map->exists($key);
    }

    public function clear() : bool
    {
        return $this->map->clear();
    }

    public function load($fields) : bool
    {
        return $this->map->load($fields);
    }

    public function merge(array $array) : bool
    {
        return $this->map->merge($array);
    }

    public function jsonSerialize() : array
    {
        return $this->map->jsonSerialize();
    }
}
