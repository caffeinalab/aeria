<?php

namespace Aeria\Structure;

use Aeria\Structure\Node;
use Aeria\Structure\RootNode;

/**
 * The Tree is a structure composed of nodes
 * 
 * @category Structure
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class Tree
{
    protected $root;

    /**
     * Constructs a new Tree
     * 
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct()
    {
        $this->root = new RootNode();
    }
    /**
     * Inserts a new node to the Tree
     *
     * @param Node $node the inserted node
     *
     * @return Tree this tree
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function insert(Node $node)
    {

        if (!$this->recursiveInsert($this->root, $node)) {
            $this->root->addChild($node);
            return true;
        }
        return $this;
    }
    /**
     * Checks if a node has to be children of another one
     *
     * @param Node $parent the parent node
     * @param Node $node the node we're inserting
     *
     * @return bool whether the adding was done correctly
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function recursiveInsert($parent, $node)
    {
        foreach ($parent->getChildren() as $possible_parent) {
            if ($node->shouldBeChildOf($possible_parent)) {
                $possible_parent->addChild($node);
                return true;
            }
        }
        foreach ($parent->getChildren() as $possible_parent) {
            if($this->recursiveInsert($possible_parent, $node))
                return true;
        }
        return false;
    }
    /**
     * Executes a method on every node
     *
     * @param callable $method the function we want to execute
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function executeOnNodes($method)
    {
        foreach ($this->recursiveReader($this->root) as $child) {
            $method($child);
        }
    }
    /**
     * Recursively reads the children of a node
     *
     * @param Node $parent the requested parent
     *
     * @return array the found nodes
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function recursiveReader($parent)
    {
        $children = [];
        foreach ($parent->getChildren() as $child){
            $children[] = $child;
            array_merge($children, $this->recursiveReader($child));
        }
        return $children;
    }

}
