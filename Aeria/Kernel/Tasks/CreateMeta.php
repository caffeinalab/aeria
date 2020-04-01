<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;
use Aeria\Meta\Meta;

/**
 * This task is in charge of creating Metaboxes.
 *
 * @category Kernel
 *
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class CreateMeta extends Task
{
    public $priority = 7;
    public $admin_only = true;

    /**
     * The main task method. It registers the metaboxes and sections.
     *
     * @param array $args the arguments to be passed to the Task
     *
     * @since  Method available since Release 3.0.0
     */
    public function do(array $args)
    {
        if (isset($args['config']['aeria']['meta'])) {
            $section_config = isset($args['config']['aeria']['section']) ? $args['config']['aeria']['section'] : null;
            foreach ($args['config']['aeria']['meta'] as $name => $data) {
                $meta_config = array_merge(
                    ['id' => $name],
                    $data
                );

                add_action(
                    'add_meta_boxes',
                    function () use ($meta_config, $args, $section_config) {
                        $meta = $args['service']['meta']->create(
                            $meta_config,
                            $section_config,
                            $args['service']['render_engine']
                        );
                    }
                );
                add_action('save_post', Meta::save($meta_config, $_POST, $args['service']['validator'], $args['service']['query'], $section_config, $args['service']['render_engine']), 10, 2);
            }

            add_action(
                'admin_print_scripts', function () use ($args, $section_config) {
                    $args['service']['render_engine']->render('section_encoder_template', ['section_config' => $section_config]);
                }
            );

            add_action(
                'admin_head',
                function () use ($args) {
                    global $_wp_admin_css_colors;
                    $admin_colors = $_wp_admin_css_colors;
                    $aeria_colors = $admin_colors[get_user_option('admin_color')]->colors;
                    $args['service']['render_engine']->render('color_encoder_template', ['colors' => $aeria_colors]);
                }
            );
        }
    }
}
