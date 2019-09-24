<?php

namespace Aeria\Field\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Field\FieldsRegistry;
use Aeria\Container\Container;
/**
 * FieldProvider is in charge of registering the Field singleton to the container
 * 
 * @category Field
 * @package  Aeria
 * @author   Andrea Longo <andrea.longo@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class FieldProvider implements ServiceProviderInterface
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
        $container->singleton('field', FieldsRegistry::class);
    }
    /**
     * In charge of booting the service. Field doesn't need any additional operation.
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
        return true;
    }

}
