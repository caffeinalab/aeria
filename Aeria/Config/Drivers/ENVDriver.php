<?php

namespace Aeria\Config\Drivers;

use Aeria\Config\Interfaces\DriverInterface;

class ENVDriver implements DriverInterface
{
    protected const FILE_FLAGS = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;

    public $driver_name = 'env';

    public function getDriverName() : string
    {
        return $this->driver_name;
    }

    public function parse(string $env_file_path) : array
    {
        $result = [];

        foreach($this->fileLinesGenerator($env_file_path) as $key => $value) {
            $key = $this->cleanKey($key);

            $value = $this->cleanValue($value);

            $result[$key] = replaceVariables($value, $result);
        }

        return $result;
    }

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