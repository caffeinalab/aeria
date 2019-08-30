<?php

namespace Aeria\Action;

use Aeria\Action\Interfaces\ActionInterface;
use Aeria\Action\Traits\ListManagerTrait;
use Aeria\Container\Container;

class ActionDispatcher
{
    use ListManagerTrait;

    public function register(ActionInterface $action)
    {
        $this->push($action);
    }

    public function dispatch(Container $container)
    {
        foreach ($this->list() as $action) {
            add_action(
                $action->getType(),
                function () use ($action, $container) {
                    $action->dispatch($container);
                }
            );
        }
    }

}
