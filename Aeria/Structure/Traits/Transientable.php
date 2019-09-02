<?php

namespace Aeria\Structure\Traits;

trait Transientable
{

    public function saveTransient(string $key)
    {
        $serialized = serialize($this);

        set_transient($key, $serialized, 600);
    }

    public static function decodeTransient(string $key)
    {
        $serialized = get_transient($key);
        $instance = unserialize($serialized);
        delete_transient($key);
        return $instance;
    }
}
