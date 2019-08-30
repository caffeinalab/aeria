<?php

namespace Aeria\Config\Drivers;

use Aeria\Config\Interfaces\DriverInterface;
use Aeria\Config\Exceptions\DecodeException;

class JsonDriver implements DriverInterface
{
    public $driver_name = 'json';

    public function getDriverName() : string
    {
        return $this->driver_name;
    }

    public function parse(string $json_filename) : array
    {
        $json = file_get_contents($json_filename);
        $result = json_decode($json, true);

        if (is_null($result)) {
            throw new DecodeException(static::class . ": invalid json parsed by Json config driver");
        }

        return $result;
    }
}