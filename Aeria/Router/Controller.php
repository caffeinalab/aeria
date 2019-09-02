<?php

namespace Aeria\Router;

use Aeria\Router\Request;

abstract class Controller
{

    protected $request;

    public final function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function getPrefix(): string
    {
        return 'aeria';
    }

}
