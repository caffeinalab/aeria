<?php

namespace Aeria\RenderEngine;

use Aeria\RenderEngine\Interfaces\Renderable;

use Aeria\RenderEngine\Exceptions\RendererNotAvailableException;
use Aeria\Config\Config;
use Aeria\Structure\Traits\DictionaryTrait;

class RenderEngine 
{
    use DictionaryTrait {
        DictionaryTrait::__construct as instanciateDictionary;
    }

    private $root_paths = [];

    public function __construct()
    {
        $this->instanciateDictionary();
        $this->addRootPath(dirname(__DIR__, 2)."/Resources/Templates");
    }

    public function render($mode, $extras)
    {
        try{
            if ($this->exists($mode))
                $this->get($mode)->render($extras);
            else
                throw new \Exception("Trying to render unexisting view.");
        }catch (\Exception $e){
            echo "Unable to render template: ".$mode."-".$e->getMessage();
        }
    }

    public function register(Renderable $view)
    {
        $this->set($view->name(), $view);
    }

    public function getRootPaths()
    {
        return $this->root_paths;
    }

    public function addRootPath(string $root_path)
    {
        $this->root_paths[]=$root_path;
    }
}

