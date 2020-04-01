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
class CreateAdminScripts extends Task
{
    public $priority = 10;
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
