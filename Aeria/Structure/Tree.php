<?php

namespace Aeria\Structure;

use Aeria\Structure\Node;
use Aeria\Structure\RootNode;


class Tree
{
    protected $root;


    public function __construct()
    {
        $this->root = new RootNode();
    }

    public function insert(Node $node)
    {

        if (!$this->recursiveInsert($this->root, $node)) {
            $this->root->addChild($node);
            return true;
        }
        return $this;
    }

    private function recursiveInsert($parent, $node)
    {
        foreach ($parent->getChildren() as $possibleParent) {
            if ($node->shouldBeChildOf($possibleParent)) {
                $possibleParent->addChild($node);
                return true;
            }
        }
        foreach ($parent->getChildren() as $possibleParent) {
            if($this->recursiveInsert($possibleParent, $node))
                return true;
        }
        return false;
    }

    public function executeOnNodes($method)
    {
        foreach ($this->recursiveReader($this->root) as $child) {
            $method($child);
        }
    }

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
