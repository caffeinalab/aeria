<?php

namespace Aeria\RenderEngine;

use Aeria\RenderEngine\Views\CoreView;

class ViewFactory
{
    public static function make($view_path)
    {
        $core_view = new CoreView();
        $core_view->setPath($view_path);
        return $core_view;
    }
}