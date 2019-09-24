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
/**
 * PostType is the service in charge of registering post types
 * 
 * @category PostType
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class PostType implements ValidateConfInterface
{
    use ValidateConfTrait;
    /**
     * Returns a validation structure for post type configs
     *
     * @return array the validation structure
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getValidationStructure() : array
    {
        return [
            'post_type' => $this->makeRegExValidator(
                "/(?!^(page|post|attachment|nav_menu_item|revision|custom_css|customize_changeset)$)^[a-z0-9_-]{1,20}$/"
            )
        ];
    }
    /**
     * Creates the requested post types
     *
     * @param array $post_type the post type's configuration
     *
     * @return WP_Post_Type the registered post type object
     * @throws AlreadyExistingPostTypeException if the post type already exists
     * @throws WordpressPostTypeException if WP fails
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Gets the requested post type object
     *
     * @param string $post_type the post type ID
     *
     * @return WP_Post_Type the requested post type
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Checks if a post type exists
     *
     * @param string $post_type the post type ID
     *
     * @return bool whether it exists or not
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function exists(string $post_type)
    {
        return post_type_exists($post_type);
    }
    /**
     * Validates the post type configuration
     *
     * @param array $conf the post type's configuration
     *
     * @return void
     * @throws ConfigValidationException if the configuration is invalid
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
    /**
     * Helper function. Removes an element from an array
     *
     * @param string $key   the requested key to delete
     * @param array  $array the array to work on
     *
     * @return array the filtered array
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
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
