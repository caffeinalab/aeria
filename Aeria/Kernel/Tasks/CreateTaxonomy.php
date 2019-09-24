<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;

/**
 * This task is in charge of creating the taxonomies.
 * 
 * @category Kernel
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class CreateTaxonomy extends Task
{
    public $priority = 3;
    public $admin_only = false;
    /**
     * The main task method. It registers the needed taxonomies.
     *
     * @param array $args the arguments to be passed to the Task
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function do(array $args)
    {
        if (isset($args['config']['aeria']['taxonomy'])) {
            foreach ($args['config']['aeria']['taxonomy'] as $name => $data) {
                $taxonomy = $args['service']['taxonomy']->create(
                    array_merge(
                        ['taxonomy' => $name],
                        $data
                    )
                );
            }
        }
    }

}