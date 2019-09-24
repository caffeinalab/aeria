<?php

namespace Aeria\Router;

use Aeria\Validator\Validator;
use Aeria\Router\Route;
use Aeria\Router\ControllerRegister;
use Aeria\Router\Factory\RouteFactory;

/**
 * Router handles the registration of routes
 * 
 * @category Router
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class Router
{

    private $routes = [];
    /**
     * Makes a new GET request
     *
     * @param string $path    the request path
     * @param string $handler the function callable
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get($path, $handler)
    {
        $this->register(
            RouteFactory::make(
                [
                  'path' => $path,
                  'method' => 'GET',
                  'handler' => $handler
                ]
            )
        );
    }
    /**
     * Makes a new POST request
     *
     * @param string $path    the request path
     * @param string $handler the function callable
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function post($path, $handler)
    {
        $this->register(
            RouteFactory::make(
                [
                  'path' => $path,
                  'method' => 'POST',
                  'handler' => $handler
                ]
            )
        );
    }
    /**
     * Makes a new PUT request
     *
     * @param string $path    the request path
     * @param string $handler the function callable
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function put($path, $handler)
    {
        $this->register(
            RouteFactory::make(
                [
                  'path' => $path,
                  'method' => 'PUT',
                  'handler' => $handler
                ]
            )
        );
    }
    /**
     * Makes a new DELETE request
     *
     * @param string $path    the request path
     * @param string $handler the function callable
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function delete($path, $handler)
    {
        $this->register(
            RouteFactory::make(
                [
                  'path' => $path,
                  'method' => 'DELETE',
                  'handler' => $handler
                ]
            )
        );
    }
    /**
     * Registers a new Route object
     *
     * @param Route $route the new route
     * 
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register(Route $route)
    {
        $this->routes[] = $route;
    }
    /**
     * Registers the routes to WordPress
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function boot()
    {
        $routes=$this->routes;
        add_action(
            'rest_api_init', function () use ($routes) {
                foreach ($this->routes as $route) {
                    $route->register();
                }
            }
        );
    }

}
