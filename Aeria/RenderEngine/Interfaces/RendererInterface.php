<?php

namespace Aeria\RenderEngine\Interfaces;


interface Renderer{
    public function getName() : string;
    public function getClosure();
}