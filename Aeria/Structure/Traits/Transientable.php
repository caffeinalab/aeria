<?php

namespace Aeria\Structure\Traits;

/**
 * The Transientable trait allows a class to be saved to a transient
 * 
 * @category Structure
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
trait Transientable
{
    /**
     * Saves the current object to a transient
     *
     * @param string $key the key to save the object with
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function saveTransient(string $key)
    {
        $serialized = serialize($this);
        set_transient($key, $serialized, 600);
    }
    /**
     * Decodes a saved transient
     *
     * @param string $key the key the object was saved with 
     *
     * @return mixed the object
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function decodeTransient(string $key)
    {
        $serialized = get_transient($key);
        $instance = unserialize($serialized);
        delete_transient($key);
        return $instance;
    }
}
