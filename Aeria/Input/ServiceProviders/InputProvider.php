<?php

namespace Aeria\Input\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Config\Config;
use Aeria\Input\Input;

class InputProvider implements ServiceProviderInterface
{

    public function register(Container $container)
    {
        $container->bind('input', Input::class);
    }

    public function boot(Container $container): bool
    {
      return true;
    }

}
