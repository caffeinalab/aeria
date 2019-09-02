<?php

namespace Aeria\Structure;

abstract class Node
{
    private $children = [];

    public function addChild($child)
    {
        $this->children[] = $child;
    }

    public function shouldBeChildOf(Node $possibleParent)
    {
        throw new Exception('You should implement this method!');
    }

    public function getChildren ()
    {
        return $this->children;
    }
}
