<?php

namespace Aeria\Config\Interfaces;
/**
 * DriverInterface describes what functions a driver should have 
 * 
 * @category Config
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface DriverInterface
{
    /**
     * Parse the requested file
     *
     * @param string $path the file path
     *
     * @return array the parsed file
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function parse(string $path) : array;
    /**
     * Return this driver's name
     *
     * @return string the driver name
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getDriverName() : string;
}