<?php

namespace Aeria\Validator\Types\RegEx;

abstract class AbstractRegExValidator 
{
    protected static $_key;
    protected static $_message;
    protected static $_validator;
    public static function getKey()
    {
        return static::$_key;
    }

    public static function getValidator()
    {
        return static::$_validator;
    }

    public static function getMessage()
    {
        return static::$_message;
    }
}