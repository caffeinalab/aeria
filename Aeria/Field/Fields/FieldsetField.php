<?php

namespace Aeria\Field\Fields;

use Aeria\Field\FieldNodeFactory;

class FieldsetField extends BaseField
{
    private function getAccordionStatus()
    {
        return new SwitchField(
            $this->key,
            ['id' => 'accordion-state', 'validators' => ''],
            $this->sections
        );
    }

    public function get(array $metas, bool $skipFilter = false)
    {
        $fields = [];
        foreach ($this->config['fields'] as $field_index => $field_config) {
            $fields[$field_config['id']] = FieldNodeFactory::make(
                $this->key, $field_config, $this->sections
            )->get($metas);
        }
        if (!$skipFilter) {
            $fields = apply_filters('aeria_get_fieldset', $fields, $this->config);
        }

        return $fields;
    }

    public function getAdmin(array $metas, array $errors)
    {
        $config = $this->config;
        $config['accordionState'] = $this->getAccordionStatus()->get($metas);
        $fields = [];
        foreach ($this->config['fields'] as $field_index => $field_config) {
            $fields[] = FieldNodeFactory::make(
                $this->key, $field_config, $this->sections
            )->getAdmin($metas, $errors);
        }
        $config['fields'] = $fields;

        return $config;
    }

    public function set($context_ID, $context_type, array $savedFields, array $new_values, $validator_service, $query_service)
    {
        $this->getAccordionStatus()->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        foreach ($this->config['fields'] as $field_index => $field_config) {
            FieldNodeFactory::make(
                $this->key, $field_config, $this->sections
            )->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        }
    }
}
