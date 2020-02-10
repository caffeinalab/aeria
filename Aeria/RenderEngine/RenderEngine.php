<?php

namespace Aeria\RenderEngine;

use Aeria\RenderEngine\Interfaces\Renderable;
use Aeria\Structure\Traits\DictionaryTrait;

/**
 * RenderEngine is in charge of rendering views.
 *
 * @category Render
 *
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class RenderEngine
{
    use DictionaryTrait {
        DictionaryTrait::__construct as instanciateDictionary;
    }

    private $root_paths = [];

    /**
     * Constructs the Render service.
     *
     *
     * @since  Method available since Release 3.0.0
     */
    public function __construct()
    {
        $this->instanciateDictionary();
        $this->addRootPath(dirname(__DIR__, 2).'/Resources/Templates');
        $custom_paths = [];
        $custom_paths = apply_filters('aeria_register_template', $custom_paths);
        foreach ($custom_paths as $path) {
            $this->addRootPath($path);
        }
    }

    /**
     * Renders the specified view.
     *
     * @param string $mode   is the view name
     * @param array  $extras are the required data for the view
     *
     * @throws \Exception when the view doesn't exist
     *
     * @since  Method available since Release 3.0.0
     */
    public function render($mode, $extras)
    {
        try {
            if ($this->exists($mode)) {
                $this->get($mode)->render($extras);
            } else {
                throw new \Exception('Trying to render unexisting view.');
            }
        } catch (\Exception $e) {
            echo 'Unable to render template: '.$mode.'-'.$e->getMessage();
        }
    }

    /**
     * Registers a new view to the RenderEngine.
     *
     * @param Renderable $view the view object
     *
     * @since  Method available since Release 3.0.0
     */
    public function register(Renderable $view)
    {
        $this->set($view->name(), $view);
    }

    /**
     * Returns the RenderEngine root paths, i.e. where it looks for views.
     *
     * @return array the root paths
     *
     * @since  Method available since Release 3.0.0
     */
    public function getRootPaths()
    {
        return $this->root_paths;
    }

    /**
     * Adds a new path to the root paths.
     *
     * @param string $root_path the path we want to add
     *
     * @since  Method available since Release 3.0.0
     */
    public function addRootPath(string $root_path)
    {
        $this->root_paths[] = $root_path;
    }
}
