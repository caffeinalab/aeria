<?php

namespace Aeria\Config\Drivers;

use Aeria\Config\Interfaces\DriverInterface;
use Aeria\Config\Exceptions\DecodeException;
/**
 * JSONDriver is a driver for .json files
 * 
 * @category Config
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class JsonDriver implements DriverInterface
{
    public $driver_name = 'json';
    /**
     * Return this driver's name
     *
     * @return string the driver name
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getDriverName() : string
    {
        return $this->driver_name;
    }
    /**
     * Parse the requested file
     *
     * @param string $json_filename the file path
     *
     * @return array the parsed file
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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