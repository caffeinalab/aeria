<?php

namespace Aeria\Config\Drivers;

use Aeria\Config\Interfaces\DriverInterface;
use Aeria\Config\Exceptions\DecodeException;
/**
 * PHPDriver parses PHP configuration files 
 * 
 * @category Config
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class PHPDriver implements DriverInterface
{
    public $driver_name = 'php';
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
     * @param string $php_file_path the file path
     *
     * @return array the parsed file
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function parse(string $php_file_path) : array
    {
        ob_start();
        $result = include $php_file_path;
        ob_end_clean();

        if (!is_array($result)) {
            throw new DecodeException(static::class . ": invalid php file parsed by PHP config driver");
        }

        return $result;
    }
}