<?php

namespace Aeria\Structure;
use Aeria\Structure\Node;

/**
 * The RootNode is a node that has no parents
 * 
 * @category Structure
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class RootNode extends Node
{
    public function shouldBeChildOf(Node $possible_parent)
    {
       return false;
    }
}
