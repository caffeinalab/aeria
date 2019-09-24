<?php

namespace Aeria\Kernel\AbstractClasses;

use Aeria\Kernel\Exceptions\CallableNotDefinedException;
/**
 * This class describes what a Kernel task looks like
 * 
 * @category Kernel
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
abstract class Task
{
    public $priority;
    public $admin_only;
    /**
     * The main task method. This is what the task does.
     *
     * @param array $args the arguments to be passed to the Task
     *
     * @return mixed the returned value
     * @throws CallableNotDefinedException in case this abstract was used
     * as a real class
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function do(array $args)
    {
        throw new CallableNotDefinedException();
    }
}