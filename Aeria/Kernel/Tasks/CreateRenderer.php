<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;
use Aeria\Kernel\Loader;


class CreateRenderer extends Task
{
    public $priority = 7;
    public $admin_only = false;

    public function do(array $args)
    {
        foreach ($args['service']['render_engine']->getRootPaths() as $root_path) {
            Loader::loadViews($root_path, $args['service']['render_engine']);
        }
    }

}