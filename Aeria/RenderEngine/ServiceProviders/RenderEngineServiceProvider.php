<?php

namespace Aeria\RenderEngine\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Meta\Meta;
use Aeria\Container\Container;
use Aeria\RenderEngine\RenderEngine;
/**
 * RenderEngineServiceProvider is in charge of registering the render service
 * to the container
 * 
 * @category Render
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class RenderEngineServiceProvider implements ServiceProviderInterface
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
        $container->singleton('render_engine', RenderEngine::class);
    }
    /**
     * In charge of booting the service. RenderEngine doesn't need any additional operation.
     *
     * @param Container $container Aeria's container
     *
     * @return bool true: service booted
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function boot(Container $container):bool
    {
        return true;
    }
}
