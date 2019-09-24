<?php

namespace Aeria\RenderEngine;

use Aeria\RenderEngine\Views\CoreView;

/**
 * ViewFactory makes CoreView objects from a view path
 * 
 * @category Render
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class ViewFactory
{
    /**
     * Creates a CoreView object from a view file
     *
     * @param string $view_path the file's path
     * 
     * @return CoreView the view object
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public static function make($view_path)
    {
        $core_view = new CoreView();
        $core_view->setPath($view_path);
        return $core_view;
    }
}