<?php

namespace Aeria\Router;

use \WP_REST_Request;

class Request
{

    public $wp_request;

    public function __construct(WP_REST_Request $wp_request)
    {
        $this->wp_request = $wp_request;
    }

}
