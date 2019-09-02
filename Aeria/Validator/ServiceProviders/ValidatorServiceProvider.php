<?php

namespace Aeria\Validator\ServiceProviders;
use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Validator\Validator;


class ValidatorServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->singleton('validator', Validator::class);
    }

    public function boot(Container $container): bool
    {
        return true;
    }
}
