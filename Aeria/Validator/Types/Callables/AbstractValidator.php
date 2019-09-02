<?php

namespace Aeria\Validator\Types\Callables;

abstract class AbstractValidator 
{
    protected static $_key;
    protected static $_message;
    public static function getKey()
    {
        return static::$_key;
    }

    public static function getMessage()
    {
        return static::$_message;
    }
}