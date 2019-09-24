<?php

namespace Aeria\Taxonomy;

use Aeria\Config\Traits\ValidateConfTrait;
use Aeria\Container\Interfaces\ValidateConfInterface;
use Aeria\Taxonomy\Exceptions\AlreadyExistingTaxonomyException;
use Aeria\Taxonomy\Exceptions\WordpressTaxonomyException;

/**
 * Taxonomy is the service in charge of registering post types
 * 
 * @category Taxonomy
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class Taxonomy implements ValidateConfInterface
{
    use ValidateConfTrait;
    /**
     * Returns a validation structure for taxonomy configs
     *
     * @return array the validation structure
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Checks if a post type exists
     *
     * @return void
     * @throws \Exception if the post type doesn't exist
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    static function validateThatPostTypeExist(string $post_type_name)
    {
        if (!post_type_exists($post_type_name)) {
            throw new \Exception("Post type '{$post_type_name}' do not exsist");
        }
    }
    /**
     * Creates a new taxonomy
     *
     * @return WP_Taxonomy the taxonomy
     * @throws AlreadyExistingTaxonomyException if the taxonomy already exists
     * @throws WordpressTaxonomyException if WP throws an exception
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Checks if a taxonomy exists
     *
     * @return bool whether the taxonomy exists or not
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function exists(array $taxonomy) : bool
    {
        return taxonomy_exists($taxonomy['taxonomy']);
    }
    /**
     * Validates a taxonomy configuration
     *
     * @param array $conf the taxonomy configuration
     * 
     * @return void
     * @throws ConfigValidationException if the config is invalid
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function validate($conf)
    {
        $exception = $this->isValid($conf);
        if (!is_null($exception)) {
            throw $exception;
        }
    }
}
