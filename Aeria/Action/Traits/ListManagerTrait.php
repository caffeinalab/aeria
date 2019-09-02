<?php

namespace Aeria\Action\Traits;

trait ListManagerTrait
{

    protected $list;

    public function __construct()
    {
        $this->list = [];
    }

    public function push($elem)
    {
        $this->list[] = $elem;
    }

    public function list(): array
    {
        return $this->list;
    }
}
