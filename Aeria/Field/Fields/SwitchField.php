<?php

namespace Aeria\Field\Fields;

/**
 * Switch is the class that represents an ON/OFF switch.
 *
 * @category Field
 *
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class SwitchField extends BaseField
{
    /**
     * Gets the field's value.
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return mixed the field's values, containing true or false
     *
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter = false)
    {
        $result = parent::get($saved_fields, true);

        if (!$skip_filter) {
            $result = filter_var($result, FILTER_VALIDATE_BOOLEAN);
            $result = apply_filters('aeria_get_switch', $result, $this->config);
        }

        return $result;
    }

    /**
     * Gets the field's value and its errors.
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param array $errors       the saving errors
     *
     * @return array the field's config, hydrated with values and errors
     *
     * @since  Method available since Release 3.0.0
     */
    public function getAdmin(array $saved_fields, array $errors)
    {
        $savedValues = parent::getAdmin($saved_fields, $errors, true);
        $savedValues['value'] = is_null($savedValues['value']) ? null : filter_var($savedValues['value'], FILTER_VALIDATE_BOOLEAN);

        return $savedValues;
    }

    /**
     * Saves the new values to the fields.
     *
     * @param int       $context_ID        the context ID. For posts, post's ID
     * @param string    $context_type      the context type. Right now, options|meta
     * @param array     $saved_fields      the saved fields
     * @param array     $new_values        the values we're saving
     * @param Validator $validator_service Aeria's validator service
     * @param Query     $query_service     Aeria's query service
     *
     * @since  Method available since Release 3.0.0
     */
    public function set($context_ID, $context_type, array $saved_fields, array $new_values, $validator_service, $query_service)
    {
        if (!isset($new_values[$this->key]) || !filter_var($new_values[$this->key], FILTER_VALIDATE_BOOLEAN)) {
            $new_values[$this->key] = 'false';
        } else {
            $new_values[$this->key] = 'true';
        }
        parent::set($context_ID, $context_type, $saved_fields, $new_values, $validator_service, $query_service);
    }
}
