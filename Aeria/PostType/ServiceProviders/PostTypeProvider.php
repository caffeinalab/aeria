<?php

namespace Aeria\PostType\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\PostType\PostType;
use Aeria\Container\Container;
use Aeria\Config\Config;
use Aeria\PostType\Models\PostTypeModel;

class PostTypeProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->bind('post_type', PostType::class);
    }

    public function boot(Container $container): bool
    {
      return true;
    }
}
