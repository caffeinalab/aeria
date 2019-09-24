<?php

namespace Aeria\Meta\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Meta\Meta;
use Aeria\Container\Container;
/**
 * MetaProvider is in charge of registering the Meta service to the container
 * 
 * @category Meta
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class MetaProvider implements ServiceProviderInterface
{
    /**
     * Registers the service to the provided container
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
        $container->bind('meta', Meta::class);
    }

    /**
     * In charge of booting the service. Meta doesn't need any additional operation.
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
