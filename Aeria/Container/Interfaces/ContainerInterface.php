<?php

namespace Aeria\Container\Interfaces;

use Aeria\Container\Interfaces\ServiceProviderInterface;
/**
 * This interface describes a container
 * 
 * @category Container
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface ContainerInterface
{
    /**
     * Checks whether a service exists in the container
     *
     * @param string $abstract the searched service ID
     * 
     * @return bool whether the container has the service or not
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function has(string $abstract) : bool;
    /**
     * Binds a service to the container
     *
     * @param string $abstract the "slug" we wanna refer the service as
     * @param mixed  $element  the element we want to bind
     * @param bool   $shared   whether the service is a singleton
     * 
     * @return bool true if the binding was successful
     * @throws ServiceAlreadyBoundException if the service was already bound
     * in this container
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function bind(
        string $abstract,
        $element = null,
        bool $shared = false
    ) : bool;
    /**
     * Binds a singleton to the container
     *
     * @param string $abstract the "slug" we wanna refer the service to
     * @param mixed  $element  the element we want to bind
     * 
     * @return bool whether the container has the service or not
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function singleton(string $abstract, $element = null) : bool;

    /**
     * Returns the saved service
     *
     * @param string $abstract the "slug" we refer the service to
     * 
     * @return mixed the searched service
     * @throws UnknownServiceException if the service wasn't found
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function raw(string $abstract); // : mixed
    /**
     * Removes a service from the container
     *
     * @param string $abstract the "slug" we refer the service to
     * 
     * @return bool whether the service was deleted
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function remove(string $abstract) : bool;
    /**
     * Flushes the container properties
     *
     * @return bool true if everything was done correctly
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function flush() : bool;
    /**
     * Returns a service
     *
     * @param string $abstract the "slug" we refer the service to
     * 
     * @return mixed the requested service
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function make(string $abstract); // : mixed
    /**
     * Mutually registers the service provider and the container 
     *
     * @param ServiceProviderInterface $provider the service provider
     * 
     * @return ContainerInterface this container
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register(
        ServiceProviderInterface $provider
    ) : ContainerInterface;
    /**
     * Boots the container's services
     *
     * @return bool true if the boot was successful
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */ 
    public function bootstrap() : bool;
}
