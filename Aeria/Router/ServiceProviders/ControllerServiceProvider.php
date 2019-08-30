<?php

namespace Aeria\Router\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Router\ControllerRegister;

class ControllerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->singleton('controller', ControllerRegister::class);
    }

    public function boot(Container $container): bool
    {
        return true;
    }
}
