<?php

namespace Aeria\Meta;

use Aeria\Meta\Interfaces\TransientableInterface;
use Aeria\Meta\Traits\Transientable;

class FieldError implements TransientableInterface
{
    use Transientable;

    protected $listErrors = [];
    private static $_singleton=null;

    private function __construct($list=null)
    {
        $this->listErrors = $list;
    }

    public static function getSingleton()
    {
        if (self::$_singleton == null) {
            self::$_singleton=new FieldError;
        }
        return self::$_singleton;
    }

    public function getList()
    {
        return $this->listErrors;
    }

    public function addError($key, $error)
    {
        $this->listErrors[$key]=$error;
    }

    public function serializeError($inputId)
    {
        if (isset($this->listErrors[$inputId])) {
            return json_encode($this->listErrors[$inputId]);
        }
        return null;
    }

}
