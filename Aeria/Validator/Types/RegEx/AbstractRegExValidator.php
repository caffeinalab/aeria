<?php

namespace Aeria\Validator\Types\RegEx;

/**
 * AbstractRegExValidator describes a validator based on RegExes
 * 
 * @category Validator
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
abstract class AbstractRegExValidator
{
    protected static $key;
    protected static $message;
    protected static $validator;
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
     * Returns the validator
     *
     * @return Closure the validator function
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function getValidator()
    {
        return static::$validator;
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