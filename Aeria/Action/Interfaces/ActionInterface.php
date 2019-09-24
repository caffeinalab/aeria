<?php

namespace Aeria\Action\Interfaces;

use Aeria\Action\Interfaces\EnqueuerInterface;
use Aeria\Container\Container;

/**
 * ActionInterface describes an Action class
 * 
 * @category Action
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface ActionInterface
{
    /**
     * Returns the type
     *
     * @return string the type
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getType(): string;
    /**
     * Registers a new Enqueuer
     * 
     * @param EnqueuerInterface $enqueuer the new enqueuer
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register(EnqueuerInterface $enqueuer);
    /**
     * Dispatches the enqueuers
     *
     * @param Container $container Aeria's container
     * 
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function dispatch(Container $container);
}
