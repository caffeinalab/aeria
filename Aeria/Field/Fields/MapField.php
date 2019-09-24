<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Interfaces\FieldInterface;

class MapField extends BaseField
{
    public $isMultipleField = false;

    public function get(array $metas, bool $skipFilter = false)
    {
        $address = (new BaseField($this->key, ['id' => 'address', 'validators' => ''], $this->sections))->get($metas);
        $autocomplete = (new BaseField($this->key, ['id' => 'autocomplete', 'validators' => ''], $this->sections))->get($metas);
        $country = (new BaseField($this->key, ['id' => 'country', 'validators' => ''], $this->sections))->get($metas);
        $lat = floatval((new BaseField($this->key, ['id' => 'lat', 'validators' => ''], $this->sections))->get($metas));
        $lng = floatval((new BaseField($this->key, ['id' => 'lng', 'validators' => ''], $this->sections))->get($metas));
        $locality = (new BaseField($this->key, ['id' => 'locality', 'validators' => ''], $this->sections))->get($metas);
        $postal_code = (new BaseField($this->key, ['id' => 'postal_code', 'validators' => ''], $this->sections))->get($metas);
        $region = (new BaseField($this->key, ['id' => 'region', 'validators' => ''], $this->sections))->get($metas);
        $route = (new BaseField($this->key, ['id' => 'route', 'validators' => ''], $this->sections))->get($metas);
        $street_number = (new BaseField($this->key, ['id' => 'street_number', 'validators' => ''], $this->sections))->get($metas);
        $api_key = get_option('map_options-api_key');
        $infos = [
            'api_key' => $api_key,
            'autocomplete' => $autocomplete,
            'address' => $address,
            'country' => $country,
            'lat' => $lat,
            'lng' => $lng,
            'locality' => $locality,
            'postal_code' => $postal_code,
            'region' => $region,
            'route' => $route,
            'street_number' => $street_number
        ];
        if(!$skipFilter)
            $infos = apply_filters("aeria_get_maps", $infos, $this->config);
        return $infos;
    }

    public function getAdmin(array $metas, array $errors)
    {
        return parent::getAdmin($metas, $errors, true);
    }

    public function set($context_ID, $context_type, array $savedFields, array $new_values, $validator_service, $query_service)
    {
        (new BaseField($this->key, ['id' => 'autocomplete', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'address', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'country', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'lat', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'lng', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'locality', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'postal_code', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'region', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'route', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'street_number', 'validators' => ''], $this->sections))->set($context_ID, $context_type, $savedFields, $new_values, $validator_service, $query_service);
    }
}