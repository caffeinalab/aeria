<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;


class CreateControllers extends Task
{
    public $priority = 8;
    public $admin_only = false;

    public function do(array $args)
    {
        if (isset($args['config']['global']['controller'])) {
            foreach ($args['config']['global']['controller'] as $name => $config) {
                $args['service']['controller']->register($config['namespace']);
            }
        } 
    }

}