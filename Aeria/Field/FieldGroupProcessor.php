<?php

namespace Aeria\Field;

use Aeria\Field\FieldNodeFactory;
use Aeria\Structure\Tree;


class FieldGroupProcessor
{
    protected $savedFields;
    protected $sections;
    protected $newValues;
    private $tree;

    public function __construct($id, $fieldGroup, $sections, $newValues = [])
    {
        $this->id = $id;
        $this->sections = $sections;
        $this->createTree($fieldGroup);
        $this->newValues = $newValues;
    }

    private function createTree($fieldGroup)
    {
        $this->tree = new Tree();
        foreach ($fieldGroup['fields'] as $index=>$config) {
            $parentKey = $fieldGroup['id'];
            $this->tree->insert(FieldNodeFactory::make($parentKey, $config, $this->sections));
        }
    }

    public function getType(){
      throw new Exception('Need to implement getType');
    }

    public function getSavedFields(){
      throw new Exception('Need to implement getSavedFields');
    }

    public function get()
    {
        $result = [];
        $this->tree->executeOnNodes(
            function ($node) use (&$result) {
                $result[$node->id] = $node->get(
                  $this->getSavedFields()
                );
            }
        );
        return $result;
    }

    public function getAdmin()
    {
        $result = [];
        $savedErrors = FieldError::make($this->id)->getList();

        $this->tree->executeOnNodes(
            function ($node) use (&$result, $savedErrors) {
                $result[] = $node->getAdmin(
                  $this->getSavedFields(),
                  $savedErrors
                );
            }
        );
        return  $result;
    }

    public function set($validator_service, $query_service)
    {
        $this->tree->executeOnNodes(
            function ($node) use ($validator_service, $query_service) {
              $fieldError = $node->set(
                $this->id,
                $this->getType(),
                $this->getSavedFields(),
                $this->newValues,
                $validator_service,
                $query_service
              );
            }
        );
    }
}
