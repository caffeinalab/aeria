<?php

namespace Aeria\Kernel\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Kernel\Kernel;
use Aeria\Config\Config;
use Aeria\PostType\PostType;
use Aeria\Taxonomy\Taxonomy;

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

        $kernel->driverType($config->getDriverInUse());

        $kernel->loadConfig($config);
        $kernel->createPostType($container);
        $kernel->createField($container);
        $kernel->createMeta($container);
        $kernel->createTaxonomy($container);
        $kernel->createValidator($container);
        $kernel->createQuery($container);
        if (is_admin()) {
            $kernel->createUpdater($container);
            $kernel->createOptionsPage($container);
        }
        $kernel->createRenderer($container);

        // ... other create


        // and finally create the router and allow all services
        // to create own route
        $kernel->createControllers($container);
        $kernel->createRouter($container);
        do_action('aeria_booted');
        return true;
    }
}
