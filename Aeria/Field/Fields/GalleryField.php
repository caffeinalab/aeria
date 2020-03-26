<?php

namespace Aeria\Field\Fields;

/**
 * Gallery is the class that represents a gallery field.
 *
 * @category Field
 *
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class GalleryField extends BaseField
{
    /**
     * Gets the field's value.
     *
     * @param array $saved_fields the FieldGroup's saved fields
     * @param bool  $skip_filter  whether to skip or not WP's filter
     *
     * @return array the field's values, an array containing the gallery's children
     *
     * @since  Method available since Release 3.0.0
     */
    public function get(array $saved_fields, bool $skip_filter = false)
    {
        $length = (int) parent::get($saved_fields, true);
        $children = [];

        for ($i = 0; $i < $length; ++$i) {
            $children[] = (new MediaField(
                $this->key, ['id' => 'picture'], $this->sections, $i
            ))->get($saved_fields);
        }
        if (!$skip_filter) {
            $children = apply_filters('aeria_get_gallery', $children, $this->config);
        }

        return $children;
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
        $stored_value = parent::get($saved_fields, true);
        $result = [];
        $result['value'] = (int) $stored_value;
        $result['children'] = [];

        for ($i = 0; $i < $result['value']; ++$i) {
            $result['children'][] = (new MediaField(
                $this->key, ['id' => 'picture'], $this->sections, $i
            ))->getAdmin($saved_fields, $errors);
        }

        return array_merge(
            $this->config,
            $result
        );
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
        $stored_values = (int) parent::set($context_ID, $context_type, $saved_fields, $new_values, $validator_service, $query_service)['value'];
        if (!$stored_values) {
            return;
        }
        for ($i = 0; $i < $stored_values; ++$i) {
            (new MediaField(
                $this->key, ['id' => 'picture'], $this->sections, $i
            ))->set($context_ID, $context_type, $saved_fields, $new_values, $validator_service, $query_service);
        }
    }
}
