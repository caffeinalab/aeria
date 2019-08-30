<?php

namespace Aeria\Config\Drivers;

use Aeria\Config\Interfaces\DriverInterface;
use Aeria\Config\Exceptions\DecodeException;

class INIDriver implements DriverInterface
{
    public $driver_name = 'ini';

    public function getDriverName() : string
    {
        return $this->driver_name;
    }

    public function parse(string $ini_file_path) : array
    {
        $result = parse_ini_file($ini_file_path, true);

        if (false === $result) {
            throw new DecodeException(static::class . ": invalid ini file parsed by INI config driver");
        }

        return $result;
    }
}