<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;

/**
 * This task is in charge of creating custom post types.
 * 
 * @category Kernel
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class CreatePostType extends Task
{
    public $priority = 1;
    public $admin_only = false;
    /**
     * The main task method. It registers the post types.
     *
     * @param array $args the arguments to be passed to the Task
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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