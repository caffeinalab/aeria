<?php

namespace Aeria\Router;

use Aeria\Validator\Validator;
use Aeria\Router\Route;
use Aeria\Router\ControllerRegister;
use Aeria\Router\Factory\RouteFactory;

class Router
{

    private $routes = [];

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

    public function register(Route $route)
    {
        $this->routes[] = $route;
    }

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
