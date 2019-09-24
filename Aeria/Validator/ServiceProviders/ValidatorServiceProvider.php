<?php

namespace Aeria\Validator\ServiceProviders;
use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\Validator\Validator;

/**
 * ValidatorServiceProvider is in charge of registering the validator service
 * to the container
 * 
 * @category Validator
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class ValidatorServiceProvider implements ServiceProviderInterface
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
        $container->singleton('validator', Validator::class);
    }
    /**
     * In charge of booting the service. Validator doesn't need any additional operation.
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
