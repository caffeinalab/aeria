<?php

namespace Aeria\RenderEngine\AbstractClasses;

use Aeria\RenderEngine\Interfaces\Renderable;

/**
 * ViewAbstract describes a View class and its methods
 * 
 * @category Render
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
abstract class ViewAbstract implements Renderable
{
    protected $view_path;
    /**
     * Renders the php to the current page
     *
     * @param array $data provides the needed data to the page
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function render(array $data)
    {
        include $this->view_path;
    }
    /**
     * Sets the view's path
     *
     * @param string $path the new path for the view
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function setPath(string $path)
    {
        $this->view_path = $path;
    }
    /**
     * Gets the view's path
     *
     * @return string the view path
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getPath():string
    {
        return $this->view_path;
    }
    /**
     * This class needs to be implemented in subclasses
     * It provides the view's name
     *
     * @return void
     * @throws Exception if the method is not implemented in the subclass
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function name():string
    {
        throw new Exception("Missing name implementation.");
    }
}