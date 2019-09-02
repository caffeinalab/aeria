<?php

namespace Aeria\Router\Factory;

use Aeria\Router\Route;
use Aeria\Router\Request;
use Aeria\Router\Exceptions\InvalidRouteConfigException;

class RouteFactory
{
    public static function make(array $config)
    {
        if (
            !isset($config['path']) ||
            !isset($config['method']) ||
            !isset($config['handler'])
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
