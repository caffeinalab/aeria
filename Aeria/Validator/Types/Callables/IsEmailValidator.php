<?php

namespace Aeria\Validator\Types\Callables;

use Aeria\Validator\Types\Callables\AbstractValidator;

/**
 * IsEmailValidator describes an email validator
 * 
 * @category Validator
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class IsEmailValidator extends AbstractValidator
{
    protected static $key="isEmail";
    protected static $message="Please insert a valid email. ";
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
        return function ($field) {
            if (filter_var($field, FILTER_VALIDATE_EMAIL)) {
                return ["status" => false];
            }
            return ["status" => true,"message" => "Please insert a valid email. "];
        };
    }
}