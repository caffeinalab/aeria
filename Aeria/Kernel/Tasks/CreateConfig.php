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
    public $priority = 3;
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
        $args['config'] = $this->applyAddons($args['config'], $args['service']['render_engine']);
        if (isset($args['config']['aeria']) || isset($args['config']['aeria']['group'])) {
            $args['config'] = $this->applyGroups($args['config'], $args['config']['aeria']['group']);
        }
        $args['config'] = $this->manipulateConfig($args['config']);
        $args['config'] = $this->checkSectionIds($args['config']);
        $args['config'] = apply_filters('aeria_transform_config', $args['config']);
        // After all transformation we can remove sections
        // that have empty "accepts" (no section types defined)
        $args['config'] = $this->removeEmptySections($args['config']);
        $args['container']->make('config')->load($args['config']);

        return $args;
    }

    private function mergeSpec($spec, $extension)
    {
        if (isset($spec['fields']) && isset($extension['fields'])) {
            $spec['fields'] = $this->extendsFields($spec['fields'], $extension['fields']);
            unset($extension['fields']);
        }

        foreach ($extension as $key => $value) {
            if (!isset($spec[$key]) || gettype($value) !== gettype($spec[$key])) {
                $spec[$key] = $value;
                continue;
            }

            $is_associative_array = is_array($value) && array_keys($value) !== range(0, count($value) - 1);
            $spec[$key] = $is_associative_array ? $this->mergeSpec($spec[$key], $value) : $value;
        }

        return $spec;
    }

    private function extendsFields($fields, $extensions)
    {
        $newFields = [];
        foreach ($extensions as $extension) {
            $isNewField = true;
            foreach ($fields as $index => $spec) {
                if ($spec['id'] === $extension['id']) {
                    $fields[$index] = $this->mergeSpec($spec, $extension);
                    $isNewField = false;
                    break;
                }
            }
            if ($isNewField) {
                $newFields[] = $extension;
            }
        }

        return array_merge($fields, $newFields);
    }

    private function applyAddons($tree, $render_service)
    {
        if (!isset($tree['aeria']) || !isset($tree['aeria']['extension'])) {
            return $tree;
        }

        foreach ($tree['aeria']['extension'] as $id => $extensions) {
            foreach ($extensions as $kind => $extension) {
                $namespace = ($kind == 'controller' || $kind == 'route') ? 'global' : 'aeria';
                if (!isset($tree[$namespace][$kind]) || !isset($tree[$namespace][$kind][$id])) {
                    add_action(
                        'admin_notices',
                        function () use ($render_service, $kind, $id) {
                            $render_service->render(
                                'admin_notice_template',
                                [
                                    'type' => 'error',
                                    'dismissible' => false,
                                    'message' => "Impossible to extend \"$id\" ($kind) because of missing base configuration",
                                ]
                            );
                        }
                    );

                    continue;
                } else {
                    $tree[$namespace][$kind][$id] = $this->mergeSpec($tree[$namespace][$kind][$id], $extension);
                }
            }
        }

        return $tree;
    }

    private function checkSectionIds($tree)
    {
        foreach ($tree as $key => $value) {
            $tree[$key]['id'] = $key;
            $tree[$key] = apply_filters('aeria_transform_section_'.$tree[$key]['id'], $tree[$key]);
        }

        return $tree;
    }

    private function removeEmptySections($tree)
    {
        foreach ($tree as $key => $value) {
            if (isset($tree[$key]) && is_array($tree[$key])) {
                $tree[$key] = $this->removeEmptySections($tree[$key]);
            }

            if (isset($value['type'])
                && $value['type'] === 'sections'
                && empty($value['accepts'])
            ) {
                unset($tree[$key]);
                $tree = array_values($tree);
            }
        }

        return $tree;
    }

    private function applyGroups($tree, $groups)
    {
        foreach ($tree as $key => $value) {
            if ($key === 'fields') {
                $new_tree = [];
                foreach ($tree[$key] as $fieldKey => $field_config) {
                    if ($field_config['type'] === 'group'
                        && isset($groups[$field_config['id']])
                    ) {
                        foreach ($groups[$field_config['id']]['fields'] as $new_field) {
                            $filtered_config = $field_config;
                            unset($filtered_config['type']);
                            unset($filtered_config['id']);

                            if (isset($filtered_config['prefix'])) {
                                $new_field['id'] = $filtered_config['prefix'].$new_field['id'];
                                unset($filtered_config['prefix']);
                            }

                            $new_tree[] = array_merge($new_field, $filtered_config);
                        }
                    } else {
                        $new_tree[] = $field_config;
                    }
                }
                $tree[$key] = $new_tree;
            }
            if (is_array($tree[$key])) {
                $tree[$key] = $this->applyGroups($tree[$key], $groups);
            }
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
