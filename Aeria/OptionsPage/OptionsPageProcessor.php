<?php

namespace Aeria\OptionsPage;

use Aeria\Field\FieldGroupProcessor;

/**
 * OptionsPageProcessor is a wrapper for FieldGroupProcessor.
 *
 * @category Options
 *
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class OptionsPageProcessor extends FieldGroupProcessor
{
    /**
     * Returns the FieldGroupProcessor type.
     *
     * @return string the type = "options"
     *
     * @since  Method available since Release 3.0.0
     */
    public function getType()
    {
        return 'options';
    }

    /**
     * Gets the saved options from WP.
     *
     * @return array the saved fields
     *
     * @since  Method available since Release 3.0.0
     */
    public function getSavedFields()
    {
        $saved_values = wp_load_alloptions();
        $sanitized_values = [];
        foreach ($saved_values as $key => $value) {
            $sanitized_values[$key] = maybe_unserialize($value);
        }

        return $sanitized_values;
    }
}
