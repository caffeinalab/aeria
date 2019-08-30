<?php

namespace Aeria\PostType\Interfaces;

interface PostTypeModelInterface
{
    public function registerPostType(string $name, array $settings) : bool;
}