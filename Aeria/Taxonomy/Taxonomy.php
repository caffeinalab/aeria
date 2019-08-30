<?php

namespace Aeria\Taxonomy;

use Aeria\Config\Traits\ValidateConfTrait;
use Aeria\Container\Interfaces\ValidateConfInterface;
use Aeria\Taxonomy\Exceptions\AlreadyExistingTaxonomyException;
use Aeria\Taxonomy\Exceptions\WordpressTaxonomyException;

class Taxonomy implements ValidateConfInterface
{
    use ValidateConfTrait;

    public function getValidationStructure() : array
    {
        $namespace = Taxonomy::class;
        return [
            'taxonomy' => $this->makeRegExValidator(
                "/^[a-z0-9_-]{1,32}$/"
            ),
            'args' => $this->combineOrValidator(
                $this->makeIsArrayValidator(),
                $this->makeIsEqualToValidator('null')
            )
        ];
    }

    static function validateThatPostTypeExist(string $post_type_name)
    {
        if (!post_type_exists($post_type_name)) {
            throw new Exception("Post type '{$post_type_name}' do not exsist");
        }
    }

    public function create($taxonomy)
    {
        $this->validate($taxonomy);

        if ($this->exists($taxonomy)) {
            throw new AlreadyExistingTaxonomyException(
                "{$taxonomy['taxonomy']} already exist"
            );
        }

        $taxonomy_obj = register_taxonomy(
            $taxonomy['taxonomy'],
            $taxonomy['object_type'],
            $taxonomy['args']
        );

        if ($taxonomy_obj instanceof WP_Error) {
            throw new WordpressTaxonomyException(
                $taxonomy_obj,
                'Unable to register a post type during PostType->create()'
            );
        }

        return $taxonomy_obj;
    }

    public function exists(array $taxonomy) : bool
    {
        return taxonomy_exists($taxonomy['taxonomy']);
    }

    private function validate($conf)
    {
        $exeption = $this->isValid($conf);
        if (!is_null($exeption)) {
            throw $exeption;
        }
    }
}
