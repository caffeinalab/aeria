<?php

namespace Aeria\Router\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Router\Router;

class RouterServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->singleton('router', Router::class);
    }

    public function boot(Container $container): bool
    {
        return true;
    }
}
