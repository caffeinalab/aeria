<?php

namespace Aeria\Config\Drivers;

use Aeria\Config\Interfaces\DriverInterface;
use Aeria\Config\Exceptions\DecodeException;

class PHPDriver implements DriverInterface
{
    public $driver_name = 'php';

    public function getDriverName() : string
    {
        return $this->driver_name;
    }

    public function parse(string $php_file_path) : array
    {
        ob_start();
        $result = include $php_file_path;
        ob_end_clean();

        if (is_null($result)) {
            throw new DecodeException(static::class . ": invalid php file parsed by PHP config driver");
        }

        return $result;
    }
}