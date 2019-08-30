<?php

namespace Aeria\Meta\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Meta\Meta;
use Aeria\Container\Container;

class MetaProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->bind('meta', Meta::class);
    }

    public function boot(Container $container): bool
    {
      return true;
    }

}
