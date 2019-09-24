<?php

namespace Aeria\Config\Exceptions;

use UnexpectedValueException;
/**
 * ConfigValidationException gets thrown when a config isn't valid
 * 
 * @category Field
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class ConfigValidationException extends UnexpectedValueException
{
}