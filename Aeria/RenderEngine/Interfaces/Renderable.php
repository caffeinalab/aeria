<?php 

namespace Aeria\RenderEngine\Interfaces;

/**
 * Renderable describes how a renderable class is made
 * 
 * @category Render
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface Renderable
{
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
    public function render(array $data);
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
    public function setPath(string $path);
    /**
     * Gets the view's path
     *
     * @return string the view path
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getPath():string;
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
    public function name():string;
}