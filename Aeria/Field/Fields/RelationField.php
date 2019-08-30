<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Fields\SelectField;
use Aeria\Field\Interfaces\FieldInterface;

class RelationField extends SelectField
{

    protected $original_config;

    public function __construct($parentKey, $config, $sections, $index = null) {
        parent::__construct($parentKey, $config, $sections, $index);

        $this->original_config = json_decode(json_encode($config));

        $this->config['type'] = 'select';
        $this->config['ajax'] = $this->config['relation'];
        unset($this->config['relation']);
    }

    public function get(array $savedFields, bool $skipFilter = false) {
        $values = parent::get($savedFields, true);

        if ($skipFilter) {
            return $values;
        }

        if (!empty($values)) {
            if(isset($this->config['multiple']) && $this->config['multiple']){
                $post_type = isset($this->config['relation']['type'])
                    ? $this->config['relation']['type']
                    : 'any';
                $values = get_posts([
                    'post__in' => $values,
                    'post_type' => 'any'
                ]);
            } else {
                $values = get_post($values);
            }
        }

        $values = apply_filters("aeria_get_relation", $values, $this->original_config);

        return $values;
    }

}
