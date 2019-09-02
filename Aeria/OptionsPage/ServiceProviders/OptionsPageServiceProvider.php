<?php

namespace Aeria\OptionsPage\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\OptionsPage\OptionsPage;

class OptionsPageServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->bind('options', OptionsPage::class);
    }
    public function boot(Container $container):bool
    {
        return true;
    }
}
