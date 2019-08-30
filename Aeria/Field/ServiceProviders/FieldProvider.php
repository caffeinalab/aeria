<?php

namespace Aeria\Field\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Field\FieldsRegistry;
use Aeria\Container\Container;

class FieldProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->singleton('field', FieldsRegistry::class);
    }

    public function boot(Container $container): bool
    {
      return true;
    }

}
