<?php

namespace Aeria\Kernel;

use Aeria\Container\Container;
use Aeria\Kernel\AbstractClasses\Task;
use Aeria\Structure\Traits\DictionaryTrait;

class Kernel
{
    use DictionaryTrait;

    public function register(Task $task)
    {
        $key = get_class($task);
        $key = substr($key, strrpos($key, '\\')+1);
        $key = toSnake($key);
        $this->set($key, $task);
    }

    // Booter
    public function boot(Container $container)
    {
        // Services
        $service_abstracts = ['meta', 'validator', 'query', 'router', 'controller',
        'taxonomy', 'updater', 'render_engine', 'field', 'options', 'post_type'];
        foreach ($service_abstracts as $abstract) {
            $service[$abstract] = $container->make($abstract);
        }

        $args = [
            'config' => $container->make('config')->all(),
            'service' => $service,
            'container' => $container
        ];
        $tasks = $this->all();
        uasort($tasks, array($this, 'compareTasks'));
        foreach ($tasks as $task){
            if(is_admin() && $task->admin_only)
                $task->do($args);
            else if (!$task->admin_only)
                $task->do($args);
        }
    }

    private function compareTasks(Task $a, Task $b)
    {
        if ($a->priority == $b->priority)
            return 0;
        return ($a->priority < $b->priority) ? -1 : 1;
    }
}