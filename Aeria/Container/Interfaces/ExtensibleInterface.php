<?php

namespace Aeria\Container\Interfaces;

interface ExtensibleInterface
{
    public static function extend(string $method_name, callable $callback) : bool;
    public static function extends(array $methods_names) : bool;
    public function __call(string $name, array $args); // mixed
    public static function __callStatic(string $name, array $args); // mixed
}
