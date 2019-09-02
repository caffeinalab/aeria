<?php

namespace Aeria\Structure\Interfaces;

interface TransientableInterface
{
    public function saveTransient(string $key);
    public static function decodeTransient(string $key);
}
