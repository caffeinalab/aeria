<?php

namespace Aeria\Config\ServiceProviders;

use Aeria\Container\Interfaces\ServiceProviderInterface;
use Aeria\Config\Exeptions\DriverException;
use Aeria\Container\Container;
use Aeria\Config\Config;

use Aeria\Config\Drivers\{
    JsonDriver,
    PHPDriver,
    ENVDriver,
    INIDriver
};
/**
 * ConfigProvider is in charge of registering the Config singleton to the container
 * 
 * @category Config
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class ConfigProvider implements ServiceProviderInterface
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
        $container->singleton('config', Config::class);
        $config = $container->make('config');

        if (defined('AERIA_CONFIG_ROOT_PATH')) {
            $config->setRootPath(AERIA_CONFIG_ROOT_PATH);
        }
        $config->addDrivers(
            [
                'json' => new JsonDriver,
                'php' => new PHPDriver,
                'env' => new ENVDriver,
                'ini' => new INIDriver,
            ]
        );
    }
    /**
     * In charge of booting the service. Config doesn't need any additional operation
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
