<?php

namespace Aeria\RenderEngine\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Meta\Meta;
use Aeria\Container\Container;
use Aeria\RenderEngine\RenderEngine;

class RenderEngineServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->singleton('render_engine', RenderEngine::class);
    }
    public function boot(Container $container):bool
    {
        return true;
    }
}
