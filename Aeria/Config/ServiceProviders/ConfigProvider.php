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

class ConfigProvider implements ServiceProviderInterface
{

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

    public function boot(Container $container): bool
    {
        return true;
    }

}
