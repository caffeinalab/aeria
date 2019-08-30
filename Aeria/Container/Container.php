<?php

namespace Aeria\Container;

use ReflectionClass;

use Aeria\Container\Interfaces\{
    ServiceProviderInterface,
    ContainerInterface
};
use Aeria\Container\Exceptions\{
    UnknownServiceException,
    ServiceAlreadyBoundException
};

class Container implements ContainerInterface
{
    protected static $instance;

    private $services = [];
    private $singleton = [];
    private $keys = [];
    private $cache = [];
    private $providers = [];

    public function has(string $id) : bool
    {
        return isset($this->keys[$id]);
    }

    public function bind(
        string $abstract,
        $element = null,
        bool $shared = false
    ) : bool {
        if ($this->has($abstract)) {
            throw new ServiceAlreadyBoundException($abstract);
        }

        $this->services[$abstract] = $element ?? $abstract;
        $this->keys[$abstract] = true;

        if ($shared) {
            $this->singleton[$abstract] = true;
        }

        return true;
    }

    public function singleton(string $abstract, $element = null) : bool
    {
        $result = $this->bind($abstract, $element, true);

        return $result;
    }

    public function make(string $abstract) // : mixed
    {
        if (!$this->has($abstract)) {
            throw new UnknownServiceException($abstract);
        }

        $element = $this->services[$abstract];

        if (!is_callable($element) && !class_exists($element)) {
            return $element;
        }

        if (isset($this->cache[$abstract])) {
            return $this->cache[$abstract];
        }

        if (!isset($this->singleton[$abstract])) {
            return $this->resolve($element);
        }

        $this->cache[$abstract] = $this->resolve($element);

        return $this->cache[$abstract];
    }

    protected function resolve(/* mixed */ $service) // : mixed
    {
        if (is_callable($service)) {
            return call_user_func($service, $this);
        }

        if (class_exists($service)) {
            $reflectionService = new ReflectionClass($service);

            if ($reflectionService->isInstantiable()) {
                return new $service;
            }
        }

        return $service;
    }

    public function remove(string $abstract) : bool
    {
        if (!$this->has($abstract)) {
            return false;
        }

        unset(
            $this->services[$abstract],
            $this->singleton[$abstract],
            $this->cache[$abstract],
            $this->keys[$abstract]
        );

        return true;
    }

    public function flush() : bool
    {
        $this->services = [];
        $this->singleton = [];
        $this->cache = [];
        $this->keys = [];
        return true;
    }

    public function raw(string $abstract) // : mixed
    {
        if (!$this->has($abstract)) {
            throw new UnknownServiceException($abstract);
        }

        return $this->services[$abstract];
    }

    public function register(
        ServiceProviderInterface $provider
    ) : ContainerInterface {
        $provider->register($this);
        $this->providers[] = $provider;
        return $this;
    }

    public function bootstrap() : bool
    {
        foreach ($this->providers as $provider) {
            $provider->boot($this);
        }
        return true;
    }

    /*
    public function extend($abstract, Closure $closure) {
        return;
    }
    */
}
