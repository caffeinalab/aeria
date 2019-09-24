<?php

namespace Aeria\Container\Exceptions;

use Exception;
/**
 * ServiceAlreadyBoundException gets thrown when a Aeria tries to register
 * an existent service
 * 
 * @category Field
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class ServiceAlreadyBoundException extends Exception
{

}