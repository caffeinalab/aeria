<?php

namespace Aeria\Container\Interfaces;

use Aeria\Container\Container;

interface ServiceProviderInterface
{
    public function register(Container $container);
    public function boot(Container $container) : bool;
}
