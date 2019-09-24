<?php

namespace Aeria\Config\Exceptions;

use UnexpectedValueException;
/**
 * NoCallableArgumentException gets thrown when a declared 
 * callable isn't actually a callable
 * 
 * @category Field
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class NoCallableArgumentException extends UnexpectedValueException
{
}