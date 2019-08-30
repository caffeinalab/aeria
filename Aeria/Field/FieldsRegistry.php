<?php

namespace Aeria\Field;

use Aeria\Field\Interfaces\FieldInterface;
use Aeria\Structure\Traits\DictionaryTrait;

class FieldsRegistry
{

    use DictionaryTrait;

    protected $registry = [];

    const DEFAULT_FIELD_TYPE = 'base';

    public function register(string $name, string $namespace = null, bool $override = false)
    {
        if (empty($namespace)) {
            $namespace = $name;
            $name = strtolower(array_pop(explode('\\', $name)));
        }
        if ($this->exists($name) && !$override) {
          throw new \Exception("The field named {$name} has been already registered");
        }
        $this->set($name, $namespace);
        return $this;
    }

    public function make($parentKey, $config, $sections, $index = null) : FieldInterface
    {
        $field_type = $config['type'];
        $fieldClass = $this->exists($field_type)
            ? $this->get($field_type)
            : $this->get(self::DEFAULT_FIELD_TYPE);
        return new $fieldClass($parentKey, $config, $sections, $index);
    }

}
