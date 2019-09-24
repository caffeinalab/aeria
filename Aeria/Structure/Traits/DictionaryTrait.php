<?php

namespace Aeria\Structure\Traits;

use Aeria\Structure\Map;
/**
 * This trait gives the possibility of saving a dictionary of key-values
 * 
 * @category Container
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
trait DictionaryTrait
{
    protected $map;
    /**
     * Constructs the Map
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct() // : void
    {
        $this->map = new Map();
    }
    /**
     * Returns the complete dictionary
     *
     * @return Map all the saved values
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function &all() : array
    {
        return $this->map->all();
    }
    /**
     * Gets a specific value by key
     *
     * @param string $key     the searched key
     * @param mixed  $default an optional default value
     * 
     * @return mixed the searched element
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get(string $key, $default = null) // : mixed
    {
        return $this->map->get($key, $default);
    }
    /**
     * Sets a specific value by its key
     *
     * @param string $key   the value's key
     * @param mixed  $value the setted value
     * 
     * @return mixed the searched element
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function set(string $key, $value) : bool
    {
        return $this->map->set($key, $value);
    }
    /**
     * Deletes a specific value by its key
     *
     * @param string $key     the value's key
     * @param mixed  $compact whether the map has to be compacted
     * 
     * @return mixed the searched element
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function delete(string $key, bool $compact = true) : bool
    {
        return $this->map->delete($key, $compact);
    }
    /**
     * Checks if a specific key exists
     *
     * @param string $key the value's key
     * 
     * @return bool whether the value exists
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function exists($key) : bool
    {
        return $this->map->exists($key);
    }
    /**
     * Clears the dictionary
     *
     * @return bool whether the clearing was done correctly
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function clear() : bool
    {
        return $this->map->clear();
    }
    /**
     * Loads the input fields
     *
     * @param array $fields the fields we want to load
     * 
     * @return bool whether the loading was done correctly
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function load($fields) : bool
    {
        return $this->map->load($fields);
    }
    /**
     * Merges the inserted fields with the existing ones
     *
     * @param array $array the fields we want to merge
     * 
     * @return bool whether the merging was done correctly
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function merge(array $array) : bool
    {
        return $this->map->merge($array);
    }
    /**
     * Serializes the dictionary into a JSON object
     * 
     * @return array the serialized object
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function jsonSerialize() : array
    {
        return $this->map->jsonSerialize();
    }
}
