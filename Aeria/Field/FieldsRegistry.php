<?php

namespace Aeria\Field;

use Aeria\Field\Interfaces\FieldInterface;
use Aeria\Structure\Traits\DictionaryTrait;
/**
 * FieldsRegistry is in charge of registering the different fields in Aeria
 * 
 * @category Field
 * @package  Aeria
 * @author   Andrea Longo <andrea.longo@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class FieldsRegistry
{

    use DictionaryTrait;

    protected $registry = [];

    const DEFAULT_FIELD_TYPE = 'base';
    /**
     * Registers a new field
     *
     * @param string $name      the wanted "slug" for the field
     * @param string $namespace the field's class namespace
     * @param bool   $override  whether to override or not the existance check
     *
     * @return FieldsRegistry the fields registry
     * @throws Exception if the field was already registered
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
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
    /**
     * Returns a field object
     *
     * @param string $parent_key the field's parent key
     * @param array  $config      the field's configuration
     * @param array  $sections   Aeria's sections configuration
     * @param int    $index      for multiple fields with children
     *
     * @return FieldInterface the field
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function make($parent_key, $config, $sections, $index = null) : FieldInterface
    {
        $field_type = $config['type'];
        $fieldClass = $this->exists($field_type)
            ? $this->get($field_type)
            : $this->get(self::DEFAULT_FIELD_TYPE);
        return new $fieldClass($parent_key, $config, $sections, $index);
    }

}
