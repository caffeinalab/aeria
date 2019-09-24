<?php

namespace Aeria\Updater\ServiceProviders;
use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Updater\Updater;

/**
 * UpdaterServiceProvider is in charge of registering the updater service
 * to the container
 * 
 * @category PostType
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class UpdaterServiceProvider implements ServiceProviderInterface
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
        $container->singleton('updater', Updater::class);
    }
    /**
     * In charge of booting the service. Updater doesn't need any additional operation.
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
        return true;
    }
}
