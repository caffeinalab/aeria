<?php

namespace Aeria\Validator\Types\RegEx;

use Aeria\Validator\Types\RegEx\AbstractRegExValidator;

class IsShortValidator extends AbstractRegExValidator
{
    protected static $_key="isShort";
    protected static $_message="Please, insert a longer value.";
    protected static $_validator="/^.{4,}$/";
}