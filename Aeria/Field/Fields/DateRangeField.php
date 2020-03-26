<?php

namespace Aeria\Field\Fields;

/**
 * DateRange is the class that represents a Date Range field.
 *
 * @category Field
 *
 * @author   Simone Montali <simone.montali@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class DateRangeField extends BaseField
{
    /**
     * Gets the field's value.
     *
     * @param array $metas       the FieldGroup's saved fields
     * @param bool  $skip_filter whether to skip or not WP's filter
     *
     * @return mixed the field's values, an array containing the start at [0], end at [1]
     *
     * @since  Method available since Release 3.0.0
     */
    public function get(array $metas, bool $skip_filter = false)
    {
        $from = (new BaseField($this->key, ['id' => 'from'], $this->sections))->get($metas);
        $to = (new BaseField($this->key, ['id' => 'to'], $this->sections))->get($metas);
        $values = [$from, $to];
        if (!$skip_filter) {
            $values = apply_filters('aeria_get_daterange', $values, $this->config);
        }

        return $values;
    }

    /**
     * Gets the field's value and its errors.
     *
     * @param array $metas  the FieldGroup's saved fields
     * @param array $errors the saving errors
     *
     * @return array the field's config, hydrated with values and errors
     *
     * @since  Method available since Release 3.0.0
     */
    public function getAdmin(array $metas, array $errors)
    {
        return parent::getAdmin($metas, $errors, true);
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
        (new BaseField($this->key, ['id' => 'from'], $this->sections))->set($context_ID, $context_type, $saved_fields, $new_values, $validator_service, $query_service);
        (new BaseField($this->key, ['id' => 'to'], $this->sections))->set($context_ID, $context_type, $saved_fields, $new_values, $validator_service, $query_service);
    }
}
