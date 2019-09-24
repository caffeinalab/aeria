<?php

namespace Aeria\Field;

use Aeria\Field\FieldNodeFactory;
use Aeria\Field\Exceptions\NonExistentConfigException;
use Aeria\Structure\Tree;

/**
 * FieldGroupProcessor is in charge of managing field groups
 * 
 * @category Field
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class FieldGroupProcessor
{
    protected $saved_fields;
    protected $sections;
    protected $new_values;
    protected $render_service;
    private $_tree;
    /**
     * Constructs the processor
     *
     * @param string $id             the field group ID
     * @param array  $field_group     the field group's config
     * @param array  $sections       Aeria's sections config
     * @param array  $render_service Aeria's render service
     * @param array  $new_values     values sent by $_POST
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct($id, $fieldGroup, $sections, $render_service, $new_values = [])
    {
        $this->id = $id;
        $this->sections = $sections;
        $this->render_service = $render_service;
        $this->createTree($fieldGroup);
        $this->new_values = $new_values;
    }
    /**
     * Creates the field tree
     *
     * @param array $field_group the field group's config
     *
     * @return void
     *
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function createTree($field_group)
    {
        $this->_tree = new Tree();
        foreach ($field_group['fields'] as $index=>$config) {
            $parent_key = $field_group['id'];
            $this->_tree->insert(FieldNodeFactory::make($parent_key, $config, $this->sections));
        }
    }
    /**
     * Class to be extended in subclasses: returns the field type
     *
     * @return void
     * @throws Exception if the method is not implemented
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getType()
    {
        throw new Exception('Need to implement getType');
    }
    /**
     * Class to be extended in subclasses: returns the saved fields
     *
     * @return void
     * @throws Exception if the method is not implemented
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getSavedFields()
    {
        throw new Exception('Need to implement getSavedFields');
    }
    /**
     * Returns the saved fields
     *
     * @return array the saved values
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get()
    {
        $result = [];
        try {
            $this->_tree->executeOnNodes(
                function ($node) use (&$result) {
                    $result[$node->id] = $node->get(
                        $this->getSavedFields()
                    );
                }
            );
        } catch (NonExistentConfigException $nece) {
            error_log("Aeria: there's a missing configuration for the metabox ".$nece->getMessage());
        }
        return $result;
    }
    /**
     * Returns the saved fields and their configuration
     *
     * @return array the configuration, hydrated with values
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getAdmin()
    {
        $result = [];
        $savedErrors = FieldError::make($this->id)->getList();
        try {
            $this->_tree->executeOnNodes(
                function ($node) use (&$result, $savedErrors) {
                    $result[] = $node->getAdmin(
                        $this->getSavedFields(),
                        $savedErrors
                    );
                }
            );
        } catch (NonExistentConfigException $nece) {
            error_log("Aeria: there's a missing configuration for the metabox ".$nece->getMessage());
        }
        return  $result;
    }
    /**
     * Saves the new values to the fields.
     *
     * @param Validator $validator_service the validation service
     * @param Query     $query_service     the DB query service
     * 
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function set($validator_service, $query_service)
    {
        try{
            $this->_tree->executeOnNodes(
                function ($node) use ($validator_service, $query_service) {
                    $fieldError = $node->set(
                        $this->id,
                        $this->getType(),
                        $this->getSavedFields(),
                        $this->new_values,
                        $validator_service,
                        $query_service
                    );
                }
            );
        } catch (NonExistentConfigException $nece) {
            error_log("Aeria: there's a missing configuration for the metabox ".$nece->getMessage());
        }
    }
}
