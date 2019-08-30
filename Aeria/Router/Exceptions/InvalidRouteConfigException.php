<?php

namespace Aeria\Router\Exceptions;

class InvalidRouteConfigException extends \Exception
{

    public function __construct(array $config = [])
    {
        $json = json_encode($config);
        parent::__construct(
            "The configuration used for the route called is invalid, check the documentation. \n You provide:\n {$json}"
        );
    }

}
