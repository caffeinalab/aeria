<?php

namespace Aeria\Router\Exceptions;

/**
 * InvalidRouteConfigException gets thrown when the user provides
 * an invalid route configuration
 * 
 * @category Router
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class InvalidRouteConfigException extends \Exception
{
    /**
     * Constructs the exception with a message
     *
     * @param array $config the route configuration
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct(array $config = [])
    {
        $json = json_encode($config);
        parent::__construct(
            "The configuration used for the route called is invalid, check the documentation. \n You provide:\n {$json}"
        );
    }

}
