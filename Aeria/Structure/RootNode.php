<?php

namespace Aeria\Structure;
use Aeria\Structure\Node;

class RootNode extends Node
{
    public function shouldBeChildOf(Node $possibleParent)
    {
       return false;
    }
}
