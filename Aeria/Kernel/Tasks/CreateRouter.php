<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;
use Aeria\Router\Factory\RouteFactory;

/**
 * This task is in charge of creating the router and its routes.
 * 
 * @category Kernel
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class CreateRouter extends Task
{
    public $priority = 9;
    public $admin_only = false;
    /**
     * The main task method. It registers various routes, the default ones
     * and the custom ones.
     *
     * @param array $args the arguments to be passed to the Task
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function do(array $args)
    {
        $args['service']['router']->get(
            "/validate", function ($request) use ($args) {
                $wp_req = $request->wp_request;
                return $args['service']['validator']->validate($wp_req["field"], $wp_req["validators"]);
            }
        );
        $args['service']['router']->get(
            "/search", function ($request) use ($args) {
                $wp_req = $request->wp_request;
                return $args['service']['query']->getPosts($wp_req->get_params());
            }
        );
        $args['service']['router']->get(
            "/post-types", function ($request) use ($args) {
                $wp_req = $request->wp_request;
                return $args['service']['query']->getPostTypes($wp_req->get_params());
            }
        );
        $args['service']['router']->get(
            "/taxonomies", function ($request) use ($args) {
                $wp_req = $request->wp_request;
                return $args['service']['query']->getTaxonomies($wp_req->get_params());
            }
        );
        $args['service']['router']->get(
            "/validate-by-id", function ($request) use ($args) {
                $wp_req = $request->wp_request;
                $meta = isset($args['config']['aeria']['meta']) ? $args['config']['aeria']['meta'] : null;
                return $args['service']['validator']->validateByID($wp_req["field_id"], $wp_req["value"], $meta);
            }
        );

        if (isset($args['config']['global']['route'])) {
            if (is_array($args['config']['global']['route'])) {
                $routes_config = array_flat($args['config']['global']['route'], 1);
                foreach ($routes_config as $config) {
                    $route = RouteFactory::make($config);
                    $args['service']['router']->register($route);
                }
            }
        }
        $args['service']['router']->boot();
    }
}