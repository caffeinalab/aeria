<?php

namespace Aeria\Kernel;

use Aeria\Container\Container;
use Aeria\Kernel\AbstractClasses\Task;
use Aeria\Structure\Traits\DictionaryTrait;

/**
 * The kernel is in charge of the boot of Aeria.
 *
 * @category Kernel
 *
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class Kernel
{
    use DictionaryTrait;

    /**
     * This function registers a task to the kernel.
     *
     * @param Task $task the task we want to register
     *
     * @since  Method available since Release 3.0.0
     */
    public function register(Task $task)
    {
        $key = get_class($task);
        $key = substr($key, strrpos($key, '\\') + 1);
        $key = toSnake($key);
        $this->set($key, $task);
    }

    /**
     * This function boots all of the services and tasks.
     *
     * @param Container $container where we've bound the services
     *
     * @since  Method available since Release 3.0.0
     */
    public function boot(Container $container)
    {
        // Services
        $service_abstracts = ['config', 'meta', 'validator', 'query', 'router', 'controller',
        'taxonomy', 'updater', 'render_engine', 'field', 'options', 'post_type', ];
        foreach ($service_abstracts as $abstract) {
            $service[$abstract] = $container->make($abstract);
        }

        $args = [
            'config' => $container->make('config')->all(),
            'service' => $service,
            'container' => $container,
        ];
        $tasks = $this->all();
        uasort($tasks, array($this, 'compareTasks'));
        foreach ($tasks as $task) {
            if ((is_admin() && $task->admin_only) || !$task->admin_only) {
                $new_args = $task->do($args);
                if (isset($new_args)) {
                    $args = $new_args;
                }
            }
        }
    }

    /**
     * This function is required to order the tasks by their priorities.
     *
     * @param Task $a the first task
     * @param Task $b the second task
     *
     * @return 1 if a>b, -1 if not
     *
     * @since  Method available since Release 3.0.0
     */
    private function compareTasks(Task $a, Task $b)
    {
        if ($a->priority == $b->priority) {
            return 0;
        }

        return ($a->priority < $b->priority) ? -1 : 1;
    }
}
