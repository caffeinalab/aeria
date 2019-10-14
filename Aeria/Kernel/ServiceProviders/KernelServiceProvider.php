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
    CreateAdminScripts,
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

/**
 * KernelServiceProvider is in charge of registering the Kernel to the container
 *
 * @category Kernel
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class KernelServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers the service to the provided container, as a singleton
     *
     * @param Container $container Aeria's container
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register(Container $container)
    {
        $container->singleton('kernel', Kernel::class);
    }
    /**
     * In charge of booting the service. It loads the config,
     * then registers the tasks to the kernel. Finally, it boots the
     * kernel.
     *
     * @param Container $container Aeria's container
     *
     * @return bool true: service booted
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function boot(Container $container): bool
    {
        $kernel = $container->make('kernel');
        $config = $container->make('config');
        Loader::loadConfig($config, $container);
        $kernel->register(new CreateAdminScripts());
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
