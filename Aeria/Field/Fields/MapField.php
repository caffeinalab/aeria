<?php

namespace Aeria\Field\Fields;

class MapField extends BaseField
{
    public static function transformConfig(array $config)
    {
        $config['apiKey'] = get_option('map_options-api_key');

        return parent::transformConfig($config);
    }

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
        $address = (new BaseField($this->key, ['id' => 'address', 'validators' => ''], $this->sections))->get($metas);
        $country = (new BaseField($this->key, ['id' => 'country', 'validators' => ''], $this->sections))->get($metas);
        $lat = floatval((new BaseField($this->key, ['id' => 'lat', 'validators' => ''], $this->sections))->get($metas));
        $lng = floatval((new BaseField($this->key, ['id' => 'lng', 'validators' => ''], $this->sections))->get($metas));
        $locality = (new BaseField($this->key, ['id' => 'locality', 'validators' => ''], $this->sections))->get($metas);
        $postal_code = (new BaseField($this->key, ['id' => 'postalCode', 'validators' => ''], $this->sections))->get($metas);
        $region = (new BaseField($this->key, ['id' => 'region', 'validators' => ''], $this->sections))->get($metas);
        $route = (new BaseField($this->key, ['id' => 'route', 'validators' => ''], $this->sections))->get($metas);
        $street_number = (new BaseField($this->key, ['id' => 'streetNumber', 'validators' => ''], $this->sections))->get($metas);

        $infos = [
            'address' => $address,
            'country' => $country,
            'lat' => $lat,
            'lng' => $lng,
            'locality' => $locality,
            'postalCode' => $postal_code,
            'region' => $region,
            'route' => $route,
            'streetNumber' => $street_number,
        ];
        if (!$skipFilter) {
            $infos = apply_filters('aeria_get_maps', $infos, $this->config);
        }

        return $infos;
    }

    public function getAdmin(array $metas, array $errors)
    {
        $config = parent::getAdmin($metas, $errors, true);
        $config['accordionState'] = $this->getAccordionStatus()->get($metas);

        return $config;
    }

    public function set($context_ID, $context_type, array $savedFields, array $new_values, $validator_service, $query_service)
    {
        (new BaseField($this->key, ['id' => 'accordion-state', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'address', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'country', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'lat', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'lng', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'locality', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'postalCode', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'region', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'route', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'streetNumber', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
    }
}
