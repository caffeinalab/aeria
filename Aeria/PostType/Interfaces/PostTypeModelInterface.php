<?php

namespace Aeria\PostType\Interfaces;
/**
 * PostTypeModelInterface describes a post type model
 * 
 * @category PostType
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface PostTypeModelInterface
{
    /**
     * Registers a post type
     *
     * @param string $name     the post type's name
     * @param array  $settings the configuration 
     * 
     * @return bool succesful or not
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function registerPostType(string $name, array $settings) : bool;
}