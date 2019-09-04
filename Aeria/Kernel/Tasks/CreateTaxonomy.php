<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;


class CreateTaxonomy extends Task
{
    public $priority = 3;
    public $admin_only = false;

    public function do(array $args)
    {
        if (isset($args['config']['aeria']['taxonomy'])) {
            foreach ($args['config']['aeria']['taxonomy'] as $name => $data) {
                $taxonomy = $args['service']['taxonomy']->create(
                    array_merge(
                        ['taxonomy' => $name],
                        $data
                    )
                );
            }
        }
    }

}