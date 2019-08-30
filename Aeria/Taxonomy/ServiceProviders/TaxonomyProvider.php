<?php

namespace Aeria\Taxonomy\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Taxonomy\Taxonomy;
use Aeria\Container\Container;

class TaxonomyProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->bind('taxonomy', Taxonomy::class);
    }

    public function boot(Container $container): bool
    {
      return true;
    }

}
