<?php

namespace Aeria\Router;

use \WP_REST_Request;
/**
 * Request is a wrapper for WP_REST_Request
 * 
 * @category Router
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class Request
{

    public $wp_request;
    /**
     * Constructs a new Request
     *
     * @param WP_REST_Request $wp_request the request
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct(WP_REST_Request $wp_request)
    {
        $this->wp_request = $wp_request;
    }

}
