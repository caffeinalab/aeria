<?php

namespace Aeria\PostType;

use Aeria\Config\Config;
use Aeria\PostType\Interfaces\PostTypeModelInterface;
use Aeria\PostType\Exceptions\{
    MissingModelException,
    AlreadyExistingPostTypeException,
    NoPostTypeException,
    WordpressPostTypeException
};
use Aeria\Config\Traits\ValidateConfTrait;
use Aeria\Container\Interfaces\ValidateConfInterface;

class PostType implements ValidateConfInterface
{
    use ValidateConfTrait;

    public function getValidationStructure() : array
    {
        return [
            'post_type' => $this->makeRegExValidator(
                "/(?!^(page|post|attachment|nav_menu_item|revision|custom_css|customize_changeset)$)^[a-z0-9_-]{1,20}$/"
            )
        ];
    }

    public function create($post_type)
    {
        $this->validate($post_type);

        if ($this->exists($post_type['post_type'])) {
            throw new AlreadyExistingPostTypeException(
                "{$post_type['post_type']} already exist"
            );
        }

        $post_type_obj = register_post_type(
            $post_type['post_type'],
            $this->removeKeyFrom('post_type', $post_type)
        );

        if ($post_type instanceof WP_Error) {
            throw new WordpressPostTypeException(
                $post_type,
                'Unable to register a post type during PostType->create()'
            );
        }

        return $post_type;
    }

    public function get(string $post_type)
    {
        $post_type_obj = get_post_type_object($post_type);
        if (is_null($post_type_obj)) {
            throw new NoPostTypeException(
                "{$post_type['post_type']} has not been register"
            );
        }

        return $post_type_obj;
    }

    public function exists(string $post_type)
    {
        return post_type_exists($post_type);
    }

    private function validate($conf)
    {
        $exeption = $this->isValid($conf);
        if (!is_null($exeption)) {
            throw $exeption;
        }
    }

    private function removeKeyFrom(string $key, array $array)
    {
        return array_filter(
            $array,
            function ($k) use ($key) {
                return $k !== $key;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

}
