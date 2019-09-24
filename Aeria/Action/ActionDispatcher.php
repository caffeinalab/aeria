<?php

namespace Aeria\Action;

use Aeria\Action\Interfaces\ActionInterface;
use Aeria\Action\Traits\ListManagerTrait;
use Aeria\Container\Container;

/**
 * ActionDispatcher is in charge of registering Actions to WP
 * 
 * @category Action
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class ActionDispatcher
{
    use ListManagerTrait;
    /**
     * Registers a new Action to the list
     *
     * @param ActionInterface $action the new action
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register(ActionInterface $action)
    {
        $this->push($action);
    }
    /**
     * Dispatches the saved actions to WP
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
        foreach ($this->list() as $action) {
            add_action(
                $action->getType(),
                function () use ($action, $container) {
                    $action->dispatch($container);
                }
            );
        }
    }

}
