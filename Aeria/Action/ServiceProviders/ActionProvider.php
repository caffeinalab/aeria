<?php

namespace Aeria\Action\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Action\ActionDispatcher;
use Aeria\Action\Enqueuers\ScriptsEnqueuer;
use Aeria\Action\Actions\AdminEnqueueScripts as AdminEnqueueScriptsAction;

/**
 * ActionProvider is in charge of registering the Action service
 * to the container
 * 
 * @category Action
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class ActionProvider implements ServiceProviderInterface
{
    /**
     * Registers the service to the provided container, as a singleton
     *
     * @param Container $container Aeria's container
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register(Container $container)
    {
        $container->singleton('action', ActionDispatcher::class);
    }
    /**
     * In charge of booting the service.
     *
     * @param Container $container Aeria's container
     *
     * @return bool true: service booted
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function boot(Container $container): bool
    {
        $dispatcher = $container->make('action');
        $aeria_base_path = plugins_url('aeria');

        $aeria_js = new ScriptsEnqueuer(
            'aeria-js',
            "{$aeria_base_path}/assets/js/aeria.js",
            null,
            null,
            true
        );

        $admin_enqueue_scripts = new AdminEnqueueScriptsAction();

        $admin_enqueue_scripts->register($aeria_js);

        $dispatcher->register($admin_enqueue_scripts);
        $dispatcher->dispatch($container);
        return true;
    }

}
