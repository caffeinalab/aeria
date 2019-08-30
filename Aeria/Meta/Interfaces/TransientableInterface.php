<?php

namespace Aeria\Meta\Interfaces;

interface TransientableInterface
{
    public function saveTransient(string $key);
    public static function decodeTransient(string $key);
}