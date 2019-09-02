<?php

namespace Aeria\RenderEngine\AbstractClasses;

use Aeria\RenderEngine\Interfaces\Renderable;

abstract class ViewAbstract implements Renderable
{
    protected $view_path;

    public function render(array $data)
    {
        include $this->view_path;
    }

    public function setPath(string $path)
    {
        $this->view_path = $path;
    }
    public function getPath():string
    {
        return $this->view_path;
    }

    public function name():string
    {
        throw new Exception("Missing name implementation.");
    }
}