<?php

namespace Aeria\Kernel\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Kernel\Kernel;
use Aeria\Config\Config;
use Aeria\PostType\PostType;
use Aeria\Taxonomy\Taxonomy;
use Aeria\Kernel\Loader;
use Aeria\Kernel\Tasks\{
    CreateControllers,
    CreateField,
    CreateMeta,
    CreateOptions,
    CreatePostType,
    CreateRenderer,
    CreateRouter,
    CreateTaxonomy,
    CreateUpdater
};


class KernelServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->singleton('kernel', Kernel::class);
    }

    public function boot(Container $container): bool
    {
        $kernel = $container->make('kernel');
        $config = $container->make('config');
        Loader::loadConfig($config, $container);
        $kernel->register(new CreateControllers());
        $kernel->register(new CreateField());
        $kernel->register(new CreateMeta());
        $kernel->register(new CreateOptions());
        $kernel->register(new CreatePostType());
        $kernel->register(new CreateRenderer());
        $kernel->register(new CreateRouter());
        $kernel->register(new CreateTaxonomy());
        $kernel->register(new CreateUpdater());
        $kernel->boot($container);
        do_action('aeria_booted');
        return true;
    }
}
