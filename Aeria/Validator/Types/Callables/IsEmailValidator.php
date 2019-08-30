<?php

namespace Aeria\Validator\Types\Callables;

use Aeria\Validator\Types\Callables\AbstractValidator;

class IsEmailValidator extends AbstractValidator
{
    protected static $_key="isEmail";
    protected static $_message="Please insert a valid email. ";

    public static function getValidator(){
        return function ($field)
        {
            if(filter_var($field, FILTER_VALIDATE_EMAIL)) {
                return ["status" => false];
           }return ["status" => true,"message" => "Please insert a valid email. "];
        };
    }
}