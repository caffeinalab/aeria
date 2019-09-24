<?php

namespace Aeria\Validator\Types\Callables;

/**
 * AbstractValidator describes a callable validator
 * 
 * @category Validator
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
abstract class AbstractValidator
{
    protected static $key;
    protected static $message;
    /**
     * Returns the validator key
     *
     * @return string the key
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function getKey()
    {
        return static::$key;
    }    
    /**
     * Returns the validator message
     *
     * @return string the message
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function getMessage()
    {
        return static::$message;
    }
}