<?php

namespace Aeria\Container\Interfaces;

use Aeria\Container\Interfaces\ServiceProviderInterface;

interface ContainerInterface
{
    public function has(string $abstract) : bool;

    public function bind(
        string $abstract,
        $element = null,
        bool $shared = false
    ) : bool;

    public function singleton(string $abstract, $element = null) : bool;

    // public function extend(string $abstract, Closure $closure);

    public function raw(string $abstract); // : mixed

    public function remove(string $abstract) : bool;

    public function flush() : bool;

    public function make(string $abstract); // : mixed

    public function register(
        ServiceProviderInterface $provider
    ) : ContainerInterface;

    public function bootstrap() : bool;
}
