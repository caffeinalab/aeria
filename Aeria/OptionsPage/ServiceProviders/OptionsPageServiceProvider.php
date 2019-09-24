<?php

namespace Aeria\OptionsPage\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Container\Container;
use Aeria\OptionsPage\OptionsPage;
/**
 * OptionsPageServiceProvider is in charge of registering the options service
 * to the container
 * 
 * @category Options
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class OptionsPageServiceProvider implements ServiceProviderInterface
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
    public function register(Container $container)
    {
        $container->bind('options', OptionsPage::class);
    }

    /**
     * In charge of booting the service. Options doesn't need any additional operation.
     *
     * @param Container $container Aeria's container
     *
     * @return bool true: service booted
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function boot(Container $container):bool
    {
        return true;
    }
}
