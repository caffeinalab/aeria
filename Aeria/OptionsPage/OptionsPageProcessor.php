<?php

namespace Aeria\OptionsPage;

use Aeria\Field\FieldGroupProcessor;

/**
 * OptionsPageProcessor is a wrapper for FieldGroupProcessor
 * 
 * @category Options
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class OptionsPageProcessor extends FieldGroupProcessor
{
    /**
     * Returns the FieldGroupProcessor type
     *
     * @return string the type = "options"
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getType()
    {
        return "options";
    }
    /**
     * Gets the saved options from WP
     *
     * @return array the saved fields
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getSavedFields()
    {
        return wp_load_alloptions();
    }
}
