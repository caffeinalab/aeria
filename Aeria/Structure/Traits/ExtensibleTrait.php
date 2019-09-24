<?php

namespace Aeria\Structure\Traits;
/**
 * This trait gives the possibility of saving class prototypes
 * and extending them
 * 
 * @category Container
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
trait ExtensibleTrait
{

    static protected $class_prototypes = [];
    /**
     * Overrides php __call. If the function is present in the class prototypes, it
     * gets called. 
     *
     * @param string $function_name the function name
     * @param array  $function_args the arguments to be passed to the function
     *
     * @return mixed the callable return value
     *
     * @access public
     * @final
     * @since  Method available since Release 3.0.0
     */
    final public function __call(string $function_name, array $function_args)
    {
        if (is_callable(static::$class_prototypes[$function_name])) {
            return call_user_func_array(static::$class_prototypes[$function_name]->bindTo($this, $this), $function_args);
        }

        return parent::__call($function_name, $function_args);
    }
    /**
     * Overrides php __callStatic. If the function is present in the class prototypes, it
     * gets called. 
     *
     * @param string $function_name the function name
     * @param array  $function_args the arguments to be passed to the function
     *
     * @return mixed the callable return value
     *
     * @access public
     * @static
     * @final
     * @since  Method available since Release 3.0.0
     */
    final public static function __callStatic(string $function_name, array $function_args)
    {
        if (is_callable(static::$class_prototypes[$function_name])) {
            return forward_static_call_array(static::$class_prototypes[$function_name], $function_args);
        }

        return parent::__callStatic($function_name, $function_args);
    }
    /**
     * Extends the saved functions with the provided callable
     *
     * @param string   $method_name the provided method name
     * @param callable $callback    the callable of the function
     *
     * @return bool the function was succesfully added
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public static function extend(string $method_name, callable $callback) : bool
    {
        static::$class_prototypes[$method_name] = $callback;
        return true;
    }
    /**
     * Checks whether the provided methods extend the saved functions 
     *
     * @param array $methods_names the provided method name
     *
     * @return bool the function was succesfully added
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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