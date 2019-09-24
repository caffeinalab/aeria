<?php

namespace Aeria\Router\Factory;

use Aeria\Router\Route;
use Aeria\Router\Request;
use Aeria\Router\Exceptions\InvalidRouteConfigException;
/**
 * RouteFactory provides Route objects for a route configuration
 * 
 * @category Router
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class RouteFactory
{
    /**
     * Makes a new Route object from a config
     *
     * @param array $config the route's configuration
     * 
     * @return Route the object
     * @throws InvalidRouteConfigException when an invalid config is provided
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function make(array $config)
    {
        if (!isset($config['path']) 
            || !isset($config['method'])
            || !isset($config['handler'])
        ) {
            throw new InvalidRouteConfigException($config);
        }
        if (is_callable($config['handler'])) {
            $route = new Route($config['path'], $config['method'], $config['handler']);
        } else {
            $parsed = static::getNameAndMethodFromHandler($config['handler']);
            $controller_register = aeria('controller');
            $route = new Route(
                $config['path'],
                $config['method'],
                function (Request $request) use ($controller_register, $parsed) {
                    return $controller_register
                      ->callOn($request, $parsed['name'], $parsed['method']);
                }
            );
            $route->setPrefix(
                $controller_register->getControllerPrefix($parsed['name'])
            );
        }
        return $route;
    }
    /**
     * Helper method that gets name and method from a handler
     *
     * @param string $handler the config handler
     * 
     * @return array composed of name and method
     * @throws \Exception when it can't find a name and method
     *
     * @access private
     * @static
     * @since  Method available since Release 3.0.0
     */
    private static function getNameAndMethodFromHandler(string $handler)
    {
        $match;
        preg_match_all('/^([\w][A-Z0-9a-z_]*)@([\w_]*)/', $handler, $match);
        if (count($match[0]) == 0) {
            throw new \Exception("Invalid string signature {$handler}. Unable to detect a valid ControllerName@method pattern.");
        }
        $name = $match[1][0];
        $method = $match[2][0];
        return [
          'name' => $name,
          'method' => $method
        ];
    }
}
