<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;

class CreateUpdater extends Task
{
    public $priority = 5;
    public $admin_only = true;

    public function do(array $args)
    {
        $args['service']['updater']->config(
            [
            // "access_token" => "",
            "slug" => 'aeria/aeria.php',
            "version" => $args['container']->version(),
            "proper_folder_name" => 'aeria'
            ]
        );
    }
}