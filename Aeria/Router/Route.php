<?php

namespace Aeria\Router;

use Aeria\Router\Request;

class Route
{
    protected $method;
    protected $callback;
    protected $path;
    protected $prefix;

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

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

}
