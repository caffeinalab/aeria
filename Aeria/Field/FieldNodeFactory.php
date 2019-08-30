<?php

namespace Aeria\Field;

class FieldNodeFactory
{

    public static function make($parentKey, $config, $sections, $index = null)
    {
        return aeria('field')->make($parentKey, $config, $sections, $index);
    }

}
