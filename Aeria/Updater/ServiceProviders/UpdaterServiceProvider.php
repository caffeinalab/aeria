<?php

namespace Aeria\Updater\ServiceProviders;
use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Updater\Updater;


class UpdaterServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->singleton('updater', Updater::class);
    }

    public function boot(Container $container): bool
    {
        return true;
    }
}
