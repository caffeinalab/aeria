<?php

namespace Aeria\Action\Actions;

use Aeria\Action\Interfaces\EnqueuerInterface;
use Aeria\Action\Interfaces\ActionInterface;
use Aeria\Action\Traits\ListManagerTrait;
use Aeria\Container\Container;
/**
 * AdminEnqueueScripts is in charge of enqueuing scripts to WP
 * 
 * @category Action
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class AdminEnqueueScripts implements ActionInterface
{
    use ListManagerTrait;
    /**
     * Returns the type
     *
     * @return string the type = 'admin_enqueue_scripts'
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getType(): string
    {
        return 'admin_enqueue_scripts';
    }
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
    public function register(EnqueuerInterface $enqueuer)
    {
        $this->push($enqueuer);
    }
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
    public function dispatch(Container $container)
    {
        wp_enqueue_editor();
        wp_enqueue_media();
        foreach ($this->list as $enqueuer) {
            ($enqueuer->getEnqClosure($container))();
        }
    }
}
