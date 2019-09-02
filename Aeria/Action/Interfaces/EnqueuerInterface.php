<?php

namespace Aeria\Action\Interfaces;

use Aeria\Container\Container;
use Closure;
interface EnqueuerInterface
{
    public function getEnqClosure(Container $container): Closure;
}
