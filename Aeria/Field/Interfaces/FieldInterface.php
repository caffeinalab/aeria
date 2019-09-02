<?php

namespace Aeria\Field\Interfaces;

interface FieldInterface
{

    public function __construct($parentKey, $config, $sections, $index);

    public function get(array $savedFields, bool $skipFilter);

    public function getAdmin(array $savedFields, array $errors);

    public function set($context_ID, $context_type, array $savedFields, array $newValues, $validator_service, $query_service);

}
