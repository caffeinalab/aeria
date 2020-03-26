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
        $args['container']->make('config')->merge($args['config']);

        return $args;
    }

    private function checkSectionIds($tree)
    {
        foreach ($tree as $key => $value) {
            $tree[$key]['id'] = $key;
        }

        return $tree;
    }

    private function manipulateConfig($tree)
    {
        foreach ($tree as $key => $value) {
            if (is_array($tree[$key])) {
                $tree[$key] = $this->manipulateConfig($tree[$key]);
            }
            if ($key === 'section') {
                $tree[$key] = $this->checkSectionIds($tree[$key]);
            }
            if ($key === 'fields') {
                foreach ($tree[$key] as $fieldKey => $field_config) {
                    $tree[$key][$fieldKey] = $this->getRealFields($field_config);
                }
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

        return $new_field_config;
    }
}
