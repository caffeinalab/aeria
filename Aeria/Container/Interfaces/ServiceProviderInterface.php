<?php

namespace Aeria\Container\Interfaces;

use Aeria\Container\Container;
/**
 * This interface describes a generic service provider
 * 
 * @category Container
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface ServiceProviderInterface
{
    /**
     * Registers the service to the provided container
     *
     * @param Container $container Aeria's container
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register(Container $container);
    /**
     * In charge of booting the service
     *
     * @param Container $container Aeria's container
     *
     * @return bool true if service booted
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function boot(Container $container) : bool;
}
