<?php

namespace Aeria\Router;

use Aeria\Router\Request;

/**
 * Controller describes a controller and its methods
 * 
 * @category Query
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
abstract class Controller
{

    protected $request;
    /**
     * Contructs the controller
     *
     * @param Request $request the controller's request
     *
     * @return void
     *
     * @access public
     * @final
     * @since  Method available since Release 3.0.0
     */
    public final function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Returns the controller prefix
     *
     * @return string the prefix = "aeria"
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function getPrefix(): string
    {
        return 'aeria';
    }

}
