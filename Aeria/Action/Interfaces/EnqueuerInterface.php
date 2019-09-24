<?php

namespace Aeria\Action\Interfaces;

use Aeria\Container\Container;
use Closure;

/**
 * ActionInterface describes an Enqueuer class
 * 
 * @category Action
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface EnqueuerInterface
{
    /**
     * Constructs the ScriptsEnqueuer object
     * 
     * @param Container $container Aeria's container
     *
     * @return Closure the script enqueuer
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getEnqClosure(Container $container): Closure;
}
