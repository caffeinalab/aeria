<?php

namespace Aeria\Config\Drivers;

use Aeria\Config\Interfaces\DriverInterface;
/**
 * ENVDriver is a driver for .env files
 * 
 * @category Config
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class ENVDriver implements DriverInterface
{
    protected const FILE_FLAGS = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;

    public $driver_name = 'env';
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
     * @param string $env_file_path the file path
     *
     * @return array the parsed file
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function parse(string $env_file_path) : array
    {
        $result = [];

        foreach ($this->fileLinesGenerator($env_file_path) as $key => $value) {
            $key = $this->cleanKey($key);

            $value = $this->cleanValue($value);

            $result[$key] = replaceVariables($value, $result);
        }

        return $result;
    }
    /**
     * Parse the requested file
     *
     * @param string $file_path the file path
     *
     * @return array the parsed file
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    protected function fileLinesGenerator(string $file_path) : Iterable {
        foreach(file($dir, static::FILE_FLAGS) as $line) {
            $line = trim($line);

            if ($line[0] === '#' || strpos($line, '=' ) === false) continue;

            list($key, $value) = explode('=', $line, 2);

            yield $key => $value;
        }
    }

    protected function cleanKey(string $key) : string {
        return trim(str_replace(['export ', "'", '"'], '', $key));
    }

    protected function cleanValue(string $value) : string {
        return stripslashes(trim($value, '"\''));
    }

    protected function replaceVariables(string $value, array $loaded_env = []) : string {
        return preg_replace_callback(
            '/\${?([a-zA-Z0-9_]+)}?/',
            function ($match) use ($loaded_env) {
                return $loaded_env[$match[1]] ?? '';
            },
            $value
        );
    }
}