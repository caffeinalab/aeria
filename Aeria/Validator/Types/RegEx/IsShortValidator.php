<?php

namespace Aeria\Validator\Types\RegEx;

use Aeria\Validator\Types\RegEx\AbstractRegExValidator;

/**
 * IsShortValidator describes a string length validator
 * 
 * @category Validator
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class IsShortValidator extends AbstractRegExValidator
{
    protected static $key="isShort";
    protected static $message="Please, insert a longer value.";
    protected static $validator="/^.{4,}$/";
}