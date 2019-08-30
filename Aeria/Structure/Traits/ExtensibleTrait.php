<?php

namespace Aeria\Structure\Traits;

trait ExtensibleTrait
{

    static protected $class_prototypes = [];

    final public function __call(string $function_name, array $function_args)
    {
        if (is_callable(static::$class_prototypes[$function_name])) {
            return call_user_func_array(static::$class_prototypes[$function_name]->bindTo($this, $this), $function_args);
        }

        return parent::__call($function_name, $function_args);
    }

    final public static function __callStatic(string $function_name, array $function_args)
    {
        if (is_callable(static::$class_prototypes[$function_name])) {
            return forward_static_call_array(static::$class_prototypes[$function_name], $function_args);
        }

        return parent::__callStatic($function_name, $function_args);
    }

    public static function extend(string $method_name, callable $callback) : bool
    {
        static::$class_prototypes[$method_name] = $callback;
        return true;
    }

    public static function extends(array $methods_names) : bool
    {
        foreach ($methods_names as $method_name => $callback) {
            if (!is_callable($callback)) {
                throw new \BadMethodCallException(static::class . ": the method name '{$method_name}' must have a callable function");
            }
            static::$class_prototypes[$method_name] = $callback;
        }
    }
}