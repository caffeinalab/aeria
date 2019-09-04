<?php

namespace Aeria\Kernel\AbstractClasses;

use Aeria\Kernel\Exceptions\CallableNotDefinedException;

abstract class Task
{
    public $priority;
    public $admin_only;

    public function do(array $args)
    {
        throw new CallableNotDefinedException();
    }
}