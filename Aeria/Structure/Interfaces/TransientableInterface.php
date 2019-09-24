<?php

namespace Aeria\Structure\Interfaces;

/**
 * TransientableInterface describes objects that can be saved to a transient
 * 
 * @category Structure
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface TransientableInterface
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
    public function saveTransient(string $key);
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
    public static function decodeTransient(string $key);
}
