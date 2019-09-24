<?php

namespace Aeria\Meta;

use Aeria\Config\Traits\ValidateConfTrait;
use Aeria\Container\Interfaces\ValidateConfInterface;
use Aeria\Field\FieldError;
use Aeria\Meta\MetaProcessor;
use Aeria\RenderEngine\RenderEngine;
/**
 * Meta is in charge of creating, saving and rendering metaboxes
 * 
 * @category Meta
 * @package  Aeria
 * @author   Jacopo Martinelli <jacopo.martinelli@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class Meta implements ValidateConfInterface
{
    use ValidateConfTrait;
    protected $sections;
    /**
     * Returns a validation structure for meta configs
     *
     * @return array the validation structure
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getValidationStructure() : array
    {
        return [
            'id' => $this->makeRegExValidator(
                "/^[a-z0-9_-]{1,20}$/"
            )
        ];
    }
    /**
     * Creates the requested metaboxes
     *
     * @param array        $config          the Meta configs
     * @param array        $sections       the sections configuration
     * @param RenderEngine $render_service the service in charge of rendering HTML 
     *
     * @return array the field's values, an array containing the gallery's children
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function create($config, $sections, $render_service)
    {
        $this->validate($config);
        $this->sections = $sections;
        add_meta_box(
            'aeria-' . $config['id'],
            $config['title'],
            Meta::class . '::renderHTML',
            isset($config['post_type']) ? $config['post_type'] : 'post',
            isset($config['context']) ? $config['context'] : 'advanced',
            isset($config['priority']) ? $config['priority'] : 'default',
            ["config" => $config,"sections"=> $sections, "render_service" => $render_service]
        );
    }
    /**
     * Validates the meta configuration
     *
     * @param array $conf the Meta configuration to validate
     *
     * @return void
     * @throws ConfigValidationException in case of an invalid config
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
     * Returns the required fields to get a WP nonce
     *
     * @param WP_Post $post current post object 
     *
     * @return array the nonce fields
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private static function nonceIDs($post)
    {
        return [
          'action' => 'update-'.basename(__FILE__).$post->ID,
          'field' => 'update_aeria_meta'
        ];
    }
    /**
     * Calls the render service to render HTML
     *
     * @param WP_Post $post the current post object
     * @param array   $extra other required data to render HTML
     *
     * @return void
     *
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    static function renderHTML($post, $extra)
    {
        $metabox = $extra["args"]["config"];
        $sections = $extra["args"]["sections"];
        $render_service = $extra["args"]["render_service"];
        $nonceIDs = static::nonceIDs($post);
        wp_nonce_field($nonceIDs['action'], $nonceIDs['field']);
        $processor = new MetaProcessor($post->ID, $metabox, $sections, $render_service);
        $metabox["fields"] = $processor->getAdmin();
        ?>
        <!-- <pre>
          <?php var_dump($processor->get()); ?>
        </pre> -->
        <?php
        $render_service->render(
            'meta_template',
            [
                "metabox" => $metabox
            ]
        );
    }
    /**
     * Saves the new metadata to WP
     *
     * @param array     $metabox           the metabox configuration
     * @param array     $new_values        the values fetched from $_POST
     * @param Validator $validator_service the validation service for fields
     * @param Query     $query_service     the required query service
     * @param array     $sections          the sections configuration
     *
     * @return void
     * @throws ConfigValidationException in case of an invalid config
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public static function save($metabox,  $new_values, $validator_service, $query_service, $sections, $render_service)
    {
        return function ($post_id,$post) use ($metabox, $new_values, $validator_service, $query_service, $sections, $render_service) {
            // Since this function is triggered when a post is created too, I'm gonna skip it in that case. I'm gonna skip it even if the post_Type is not supported.
            if ($new_values==[] || !in_array($post->post_type, $metabox["post_type"])) {
                return $post_id;
            }

            $nonceIDs = static::nonceIDs($post);
            $post_type_object = get_post_type_object($new_values['post_type']);

            if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                || (empty($new_values['post_ID']) ||  empty($new_values['post_type']))
                || (!isset($new_values['post_ID']) || $post_id != $new_values['post_ID'])
                || (!check_admin_referer($nonceIDs['action'], $nonceIDs['field']))
                || (!current_user_can($post_type_object->cap->edit_post, $post_id))
            ) return $post_id;

            $processor = new MetaProcessor($post_id, $metabox, $sections, $render_service, $new_values);
            $processor->set(
                $validator_service,
                $query_service
            );

            FieldError::make($post_id)
              ->saveTransient($post_id."-errors");
        };
    }
}
