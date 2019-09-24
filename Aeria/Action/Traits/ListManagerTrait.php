<?php

namespace Aeria\Action\Traits;

/**
 * ListManagerTrait makes a class able to manage a list
 * 
 * @category Action
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
trait ListManagerTrait
{

    protected $list;
    /**
     * Constructs the list
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct()
    {
        $this->list = [];
    }
    /**
     * Pushes an element to the list
     * 
     * @param mixed $elem the element to add
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function push($elem)
    {
        $this->list[] = $elem;
    }
    /**
     * Returns the full list
     *
     * @return array the list
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function list(): array
    {
        return $this->list;
    }
}
