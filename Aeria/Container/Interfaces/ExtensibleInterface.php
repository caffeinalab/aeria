<?php

namespace Aeria\Container\Interfaces;
/**
 * This interface describes an extensible class
 * 
 * @category Container
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface ExtensibleInterface
{
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
    public static function extend(string $method_name, callable $callback) : bool;
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
    public static function extends(array $methods_names) : bool;
    /**
     * Overrides php __call. If the function is present in the class prototypes, it
     * gets called. 
     *
     * @param string $name the function name
     * @param array  $args the arguments to be passed to the function
     *
     * @return mixed the callable return value
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __call(string $name, array $args); // mixed
    /**
     * Overrides php __callStatic. If the function is present in the class prototypes, it
     * gets called. 
     *
     * @param string $name the function name
     * @param array  $args the arguments to be passed to the function
     *
     * @return mixed the callable return value
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function __callStatic(string $name, array $args); // mixed
}
