<?php

namespace Aeria\Field\Interfaces;
/**
 * FieldInterface describes how a Field class should be
 * 
 * @category Field
 * @package  Aeria
 * @author   Andrea Longo <andrea.longo@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
interface FieldInterface
{
    /**
     * Constructs the field
     *
     * @param string $parent_key the field's parent key
     * @param array  $config      the field's config
     * @param array  $sections   Aeria's sections config
     * @param array  $index      index for multiple fields
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct($parent_key, $config, $sections, $index);
    /**
     * Gets the field's value
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return mixed the field's value
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter);
    /**
     * Gets the field's value and its errors
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param array $errors      the saving errors
     *
     * @return array the field's config, hydrated with values and errors
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getAdmin(array $saved_fields, array $errors);
    /**
     * Saves the new values to the fields.
     *
     * @param int       $context_ID        the context ID. For posts, post's ID
     * @param string    $context_type      the context type. Right now, options|meta
     * @param array     $saved_fields       the saved fields
     * @param array     $new_values        the values we're saving
     * @param Validator $validator_service Aeria's validator service
     * @param Query     $query_service     Aeria's query service
     * 
     * @return array the results of the saving
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function set($context_ID, $context_type, array $saved_fields, array $new_values, $validator_service, $query_service);

}
