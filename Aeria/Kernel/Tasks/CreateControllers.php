<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;

/**
 * This task is in charge of creating controllers.
 * 
 * @category Kernel
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class CreateControllers extends Task
{
    public $priority = 8;
    public $admin_only = false;
    /**
     * The main task method. It registers the controllers to Aeria.
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
        if (isset($args['config']['global']['controller'])) {
            foreach ($args['config']['global']['controller'] as $name => $config) {
                $args['service']['controller']->register($config['namespace']);
            }
        } 
    }

}