<?php

namespace Aeria\Router;

use Aeria\Router\Request;
/**
 * Route describes a REST route
 * 
 * @category Router
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class Route
{
    protected $method;
    protected $callback;
    protected $path;
    protected $prefix;

    /**
     * Constructs a new Route
     *
     * @param string  $path     the route path
     * @param string  $method   the API method
     * @param Closure $callback the API method
     *
     * @return void
     * @throws \Exception if an invalid callback is provided
     * @throws \Exception if the provided method isn't accepted
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct($path, $method = 'GET', $callback = null)
    {
        if (!is_callable($callback)) {
            throw new \Exception('Use a valid callback');
        }
        if ($method != 'GET' && $method != 'POST' && $method != 'PUT' && $method != 'DELETE') {
            throw new \Exception("Invalid http method, {$method}");
        }
        $this->method = $method;
        $this->path = $path;
        $this->callback = $callback;
        $this->prefix = 'aeria';
    }
    /**
     * Registers the route
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register()
    {
        register_rest_route(
            $this->getPrefix(),
            $this->path,
            [
                'methods' => $this->method,
                'callback' => function ($wp_request) {
                    $req = new Request($wp_request);
                    $resp = call_user_func($this->callback, $req);
                    return $resp;
                }
            ]
        );
    }
    /**
     * Returns the route prefix
     *
     * @return string the prefix
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
    /**
     * Sets the route prefix
     *
     * @param string $prefix the desired prefix
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

}
