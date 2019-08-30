<?php

namespace Aeria\Action\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Action\ActionDispatcher;
use Aeria\Action\Enqueuers\ScriptsEnqueuer;
use Aeria\Action\Actions\AdminEnqueueScripts as AdminEnqueueScriptsAction;

class ActionProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->singleton('action', ActionDispatcher::class);
    }

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
