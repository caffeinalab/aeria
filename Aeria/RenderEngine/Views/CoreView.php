<?php

namespace Aeria\RenderEngine\Views;

use Aeria\RenderEngine\AbstractClasses\ViewAbstract;

class CoreView extends ViewAbstract
{
    public function name():string
    {
        if (preg_match_all(
            '/^([\/][a-zA-Z\/0-9\ \-]+)\/(([a-zA-Z\ -_%$£]+).php)+$/',
            $this->view_path,
            $matches
        )
        ) {
            return toSnake($matches[3][0]);
        } else {
            throw new Exception("Unable to get filename for file: ".$this->view_path);
        }


    }
}