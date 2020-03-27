<?php

namespace Aeria\Kernel\Tasks;

use Aeria\Kernel\AbstractClasses\Task;

/**
 * This task is in charge of creating fields.
 *
 * @category Kernel
 *
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class CreateConfig extends Task
{
    public $priority = 2;
    public $admin_only = false;

    /**
     * The main task method. It registers the fields to the field service.
     *
     * @param array $args the arguments to be passed to the Task
     *
     * @since  Method available since Release 3.0.17
     */
    public function do(array $args)
    {
        $args['config'] = $this->manipulateConfig($args['config']);
        $args['config'] = $this->checkSectionIds($args['config']);
        $args['config'] = apply_filters('aeria_transform_config', $args['config']);
        $args['container']->make('config')->load($args['config']);

        return $args;
    }

    private function checkSectionIds($tree)
    {
        foreach ($tree as $key => $value) {
            $tree[$key]['id'] = $key;
            $tree[$key] = apply_filters('aeria_transform_section_'.$tree[$key]['id'], $tree[$key]);
        }

        return $tree;
    }

    private function manipulateConfig($tree)
    {
        foreach ($tree as $key => $value) {
            if ($key === 'section') {
                $tree[$key] = $this->checkSectionIds($tree[$key]);
            }
            if ($key === 'fields') {
                foreach ($tree[$key] as $fieldKey => $field_config) {
                    $tree[$key][$fieldKey] = $this->getRealFields($field_config);
                }
            }
            if (is_array($tree[$key])) {
                $tree[$key] = $this->manipulateConfig($tree[$key]);
            }
        }

        return $tree;
    }

    private function getRealFields($field_config)
    {
        $fields_registry = aeria('field');

        if (isset($field_config['fields'])) {
            $field_config['fields'] = $this->getRealFields($field_config['fields']);
        }

        if (isset($field_config['id'])) {
            $field_config = apply_filters('aeria_before_transform_field_base', $field_config);
            $field_config = apply_filters('aeria_before_transform_field_'.$field_config['id'], $field_config);
        }

        if (!isset($field_config['type'])) {
            return $field_config;
        }

        $field_config['original_config'] = $field_config;
        $type = $field_config['type'];

        if (!$fields_registry->exists($type)) {
            return $field_config;
        }

        $field_type_class = $fields_registry->get($type);
        $new_field_config = is_array($field_config)
            ? $field_type_class::transformConfig($field_config)
            : $field_config;

        $new_field_config = apply_filters('aeria_after_transform_field_base', $new_field_config);
        $new_field_config = apply_filters('aeria_after_transform_field_'.$new_field_config['id'], $new_field_config);

        return $new_field_config;
    }
}
