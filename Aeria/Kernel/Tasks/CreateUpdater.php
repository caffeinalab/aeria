<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;

/**
 * This task is in charge of creating the updater service.
 *
 * @category Kernel
 *
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class CreateUpdater extends Task
{
    public $priority = 11;
    public $admin_only = true;

    /**
     * The main task method. It registers the updater service and its infos.
     *
     * @param array $args the arguments to be passed to the Task
     *
     * @since  Method available since Release 3.0.0
     */
    public function do(array $args)
    {
    }
}
