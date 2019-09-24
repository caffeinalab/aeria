<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;
/**
 * This task is in charge of creating fields.
 * 
 * @category Kernel
 * @package  Aeria
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class CreateField extends Task
{
    public $priority = 2;
    public $admin_only = false;
    /**
     * The main task method. It registers the fields to the field service.
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
        $args['service']['field']->register('base', \Aeria\Field\Fields\BaseField::class);
        $args['service']['field']->register('repeater', \Aeria\Field\Fields\RepeaterField::class);
        $args['service']['field']->register('gallery', \Aeria\Field\Fields\GalleryField::class);
        $args['service']['field']->register('picture', \Aeria\Field\Fields\PictureField::class);
        $args['service']['field']->register('sections', \Aeria\Field\Fields\SectionsField::class);
        $args['service']['field']->register('select', \Aeria\Field\Fields\SelectField::class);
        $args['service']['field']->register('switch', \Aeria\Field\Fields\SwitchField::class);
        $args['service']['field']->register('relation', \Aeria\Field\Fields\RelationField::class);
        $args['service']['field']->register('maps', \Aeria\Field\Fields\MapField::class);
        $args['service']['field']->register('daterange', \Aeria\Field\Fields\DateRangeField::class);
        // example of multiple registered fields with the same handler; not
        // really needed in this case, as they use the default BaseField, but
        // colud be useful; `register` accepts a third value: `override`.
        // So, having the list of overridable fields here it's not a bad idea.
        $args['service']['field']->register('text', \Aeria\Field\Fields\BaseField::class);
        $args['service']['field']->register('textarea', \Aeria\Field\Fields\BaseField::class);
        $args['service']['field']->register('wysiwyg', \Aeria\Field\Fields\BaseField::class);
        $args['service']['field']->register('number', \Aeria\Field\Fields\BaseField::class);
        $args['service']['field']->register('email', \Aeria\Field\Fields\BaseField::class);
        $args['service']['field']->register('url', \Aeria\Field\Fields\BaseField::class);
        $args['service']['field']->register('date', \Aeria\Field\Fields\BaseField::class);


        do_action('aeria_register_field', $args['service']['field'], $args['container']);
    }

}
