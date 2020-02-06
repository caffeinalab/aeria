<?php

namespace Aeria\Container;

use ReflectionClass;
use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Interfaces\ContainerInterface;
use Aeria\Container\Exceptions\UnknownServiceException;
use Aeria\Container\Exceptions\ServiceAlreadyBoundException;

/**
 * Container contains all of Aeria's services.
 *
 * @category Container
 *
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class Container implements ContainerInterface
{
    protected static $instance;

    private $services = [];
    private $singleton = [];
    private $keys = [];
    private $cache = [];
    private $providers = [];

    /**
     * Checks whether a service exists in the container.
     *
     * @param string $id the searched service ID
     *
     * @return bool whether the container has the service or not
     *
     * @since  Method available since Release 3.0.0
     */
    public function has(string $id): bool
    {
        return isset($this->keys[$id]);
    }

    /**
     * Binds a service to the container.
     *
     * @param string $abstract the "slug" we wanna refer the service as
     * @param mixed  $element  the element we want to bind
     * @param bool   $shared   whether the service is a singleton
     *
     * @return bool true if the binding was successful
     *
     * @throws ServiceAlreadyBoundException if the service was already bound
     *                                      in this container
     *
     * @since  Method available since Release 3.0.0
     */
    public function bind(
        string $abstract,
        $element = null,
        bool $shared = false
    ): bool {
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

    /**
     * Binds a singleton to the container.
     *
     * @param string $abstract the "slug" we wanna refer the service to
     * @param mixed  $element  the element we want to bind
     *
     * @return bool whether the container has the service or not
     *
     * @since  Method available since Release 3.0.0
     */
    public function singleton(string $abstract, $element = null): bool
    {
        $result = $this->bind($abstract, $element, true);

        return $result;
    }

    /**
     * Returns a service.
     *
     * @param string $abstract the "slug" we refer the service to
     *
     * @return mixed the requested service
     *
     * @since  Method available since Release 3.0.0
     */
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

    /**
     * Resolves a service.
     *
     * @param mixed $service the service we want to resolve
     *
     * @return mixed the callable result, or the service
     *
     * @since  Method available since Release 3.0.0
     */
    protected function resolve(/* mixed */ $service) // : mixed
    {
        if (is_callable($service)) {
            return call_user_func($service, $this);
        }

        if (class_exists($service)) {
            $reflectionService = new ReflectionClass($service);

            if ($reflectionService->isInstantiable()) {
                return new $service();
            }
        }

        return $service;
    }

    /**
     * Removes a service from the container.
     *
     * @param string $abstract the "slug" we refer the service to
     *
     * @return bool whether the service was deleted
     *
     * @since  Method available since Release 3.0.0
     */
    public function remove(string $abstract): bool
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

    /**
     * Flushes the container properties.
     *
     * @return bool true if everything was done correctly
     *
     * @since  Method available since Release 3.0.0
     */
    public function flush(): bool
    {
        $this->services = [];
        $this->singleton = [];
        $this->cache = [];
        $this->keys = [];

        return true;
    }

    /**
     * Returns the saved service.
     *
     * @param string $abstract the "slug" we refer the service to
     *
     * @return mixed the searched service
     *
     * @throws UnknownServiceException if the service wasn't found
     *
     * @since  Method available since Release 3.0.0
     */
    public function raw(string $abstract) // : mixed
    {
        if (!$this->has($abstract)) {
            throw new UnknownServiceException($abstract);
        }

        return $this->services[$abstract];
    }

    /**
     * Mutually registers the service provider and the container.
     *
     * @param ServiceProviderInterface $provider the service provider
     *
     * @return ContainerInterface this container
     *
     * @since  Method available since Release 3.0.0
     */
    public function register(
        ServiceProviderInterface $provider
    ): ContainerInterface {
        $provider->register($this);
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * Boots the container's services.
     *
     * @return bool true if the boot was successful
     *
     * @since  Method available since Release 3.0.0
     */
    public function bootstrap(): bool
    {
        foreach ($this->providers as $provider) {
            $provider->boot($this);
        }

        return true;
    }
}
