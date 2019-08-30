<?php

namespace Aeria\Action\Interfaces;

use Aeria\Action\Interfaces\EnqueuerInterface;
use Aeria\Container\Container;

interface ActionInterface
{
    public function getType(): string;
    public function register(EnqueuerInterface $enqueuer);
    public function dispatch(Container $container);
}
