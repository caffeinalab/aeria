<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;


class CreatePostType extends Task
{
    public $priority = 1;
    public $admin_only = false;

    public function do(array $args)
    {
        if (isset($args['config']['aeria']['post-type'])) {
            foreach ($args['config']['aeria']['post-type'] as $name => $data) {
                $post_type = $args['service']['post_type']->create(
                    array_merge(
                        ['post_type' => $name],
                        $data
                    )
                );
            }
        }
    }
}