<?php 

namespace Aeria\RenderEngine\Interfaces;

interface Renderable
{

    public function render(array $data);

    public function setPath(string $path);
    public function getPath():string;

    public function name():string;
}