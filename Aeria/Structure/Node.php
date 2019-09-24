<?php

namespace Aeria\Structure;

/**
 * Node describes a Tree node
 * 
 * @category Structure
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
abstract class Node
{
    private $children = [];
    /**
     * Adds a child to the Node
     *
     * @param mixed $child a node that is a child of this one
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function addChild($child)
    {
        $this->children[] = $child;
    }
    /**
     * This method is needed when saving children
     *
     * @param Node $possible_parent the possible parent we're checking
     *
     * @return bool whether the node is a possible parent or not
     * @throws \Exception if the method wasn't implemented in subclasses
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function shouldBeChildOf(Node $possible_parent)
    {
        throw new Exception('You should implement this method!');
    }
    /**
     * Returns the node children
     *
     * @return array the node's children
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getChildren()
    {
        return $this->children;
    }
}
