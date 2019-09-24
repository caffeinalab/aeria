<?php

namespace Aeria\Config\Drivers;

use Aeria\Config\Interfaces\DriverInterface;
use Aeria\Config\Exceptions\DecodeException;
/**
 * INIDriver is a driver for .ini files
 * 
 * @category Config
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class INIDriver implements DriverInterface
{
    public $driver_name = 'ini';
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
     * @param string $ini_file_path the file path
     *
     * @return array the parsed file
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function parse(string $ini_file_path) : array
    {
        $result = parse_ini_file($ini_file_path, true);

        if (empty($result)) {
            throw new DecodeException(static::class . ": invalid ini file parsed by INI config driver");
        }

        return $result;
    }
}