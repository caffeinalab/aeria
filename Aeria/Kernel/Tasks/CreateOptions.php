<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;

/**
 * This task is in charge of creating options pages.
 *
 * @category Kernel
 *
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class CreateOptions extends Task
{
    public $priority = 4;
    public $admin_only = true;

    /**
     * The main task method. It registers the option pages.
     *
     * @param array $args the arguments to be passed to the Task
     *
     * @since  Method available since Release 3.0.0
     */
    public function do(array $args)
    {
        $default_icon_data = file_get_contents(dirname(__DIR__, 2).'/aeria.svg');
        $default_icon = 'data:image/svg+xml;base64,'.base64_encode($default_icon_data);
        if (isset($args['config']['aeria']['options'])) {
            $section_config = isset($args['config']['aeria']['section']) ? $args['config']['aeria']['section'] : [];

            foreach ($args['config']['aeria']['options'] as $name => $data) {
                $config = [];
                $config = array_merge(
                    ['id' => $name],
                    $data
                );

                $theOptionPage = [
                    'title' => $config['title'],
                    'menu_title' => $config['title'],
                    'capability' => isset($config['capability']) ? $config['capability'] : 'manage_options',
                    'menu_slug' => $config['menu_slug'],
                    'parent' => isset($config['parent']) ? $config['parent'] : 'options-general.php',
                    'parent_title' => isset($config['parent_title']) ? $config['parent_title'] : '',
                    'parent_icon' => isset($config['parent_icon']) ? $config['parent_icon'] : $default_icon,
                    'config' => $config,
                    'sections' => $section_config,
                    'validator_service' => $args['service']['validator'],
                    'query_service' => $args['service']['query'],
                ];
                $args['service']['options']->register($theOptionPage);
            }
        }
        add_action(
            'admin_menu',
            function () use ($args) {
                $args['service']['options']->boot($args['config'], $args['service']['render_engine']);
            }
        );
    }
}
