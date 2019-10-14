<?php

namespace Aeria\RenderEngine\Renderers;

use Aeria\RenderEngine\Interfaces\RendererInterface;

class TextRenderer implements RendererInterface
{
    protected $name='text';

    public function __construct ($name){
        $this->name=$name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}