<?php

namespace Aeria\Query\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Query\Query;



class QueryServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->singleton('query', Query::class);
    }

    public function boot(Container $container): bool
    {
        return true;
    }
}
