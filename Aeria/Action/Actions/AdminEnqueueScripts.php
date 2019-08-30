<?php

namespace Aeria\Action\Actions;

use Aeria\Action\Interfaces\EnqueuerInterface;
use Aeria\Action\Interfaces\ActionInterface;
use Aeria\Action\Traits\ListManagerTrait;
use Aeria\Container\Container;

class AdminEnqueueScripts implements ActionInterface
{
    use ListManagerTrait;

    public function getType(): string
    {
        return 'admin_enqueue_scripts';
    }

    public function register(EnqueuerInterface $enqueuer)
    {
        $this->push($enqueuer);
    }

    public function dispatch(Container $container)
    {
        foreach ($this->list as $enqueuer) {
            ($enqueuer->getEnqClosure($container))();
        }
    }
}
