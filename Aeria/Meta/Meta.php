<?php

namespace Aeria\Meta;

use Aeria\Config\Traits\ValidateConfTrait;
use Aeria\Container\Interfaces\ValidateConfInterface;
use Aeria\Field\FieldError;
use Aeria\Meta\MetaProcessor;
use Aeria\RenderEngine\RenderEngine;

class Meta implements ValidateConfInterface
{
    use ValidateConfTrait;
    protected $sections;

    public function getValidationStructure() : array
    {
        return [
            'id' => $this->makeRegExValidator(
                "/^[a-z0-9_-]{1,20}$/"
            )
        ];
    }

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

    private function validate($conf)
    {
        $exception = $this->isValid($conf);
        if (!is_null($exception)) {
            throw $exception;
        }
    }

    private static function nonceIDs($post)
    {
        return [
          'action' => 'update-'.basename(__FILE__).$post->ID,
          'field' => 'update_aeria_meta'
        ];
    }

    static function renderHTML($post, $extra)
    {
        $metabox = $extra["args"]["config"];
        $sections = $extra["args"]["sections"];
        $render_service = $extra["args"]["render_service"];
        $nonceIDs = static::nonceIDs($post);
        wp_nonce_field($nonceIDs['action'], $nonceIDs['field']);
        $processor = new MetaProcessor($post->ID, $metabox, $sections);
        $metabox["fields"] = $processor->getAdmin();
        ?>
        <pre>
          <?php var_dump($processor->get()); ?>
        </pre>
        <?php
        $render_service->render('meta_template',
        [
          "metabox" => $metabox
        ]);
    }

    public static function save($metabox,  $newValues, $validator_service, $query_service, $sections)
    {
        return function ($post_id,$post) use ($metabox, $newValues, $validator_service, $query_service, $sections) {
            // Since this function is triggered when a post is created too, I'm gonna skip it in that case. I'm gonna skip it even if the post_Type is not supported.
            if ($newValues==[] || !in_array($post->post_type, $metabox["post_type"])) {
                return $post_id;
            }

            $nonceIDs = static::nonceIDs($post);
            $post_type_object = get_post_type_object($newValues['post_type']);

            // TODO: Check if current post type is supported: in the 1.0, the supported fields were hardcoded to ['post']
            if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || (empty($newValues['post_ID']) ||  empty($newValues['post_type']))
            || (!isset($newValues['post_ID']) || $post_id != $newValues['post_ID'])
            || (!check_admin_referer($nonceIDs['action'], $nonceIDs['field']))
            || (!current_user_can($post_type_object->cap->edit_post, $post_id))
            )return $post_id;

            $processor = new MetaProcessor($post_id, $metabox, $sections, $newValues);
            $processor->set(
              $validator_service,
              $query_service
            );

            FieldError::make($post_id)
              ->saveTransient($post_id."-errors");
        };
    }
}
