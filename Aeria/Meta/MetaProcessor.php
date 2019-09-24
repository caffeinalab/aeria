<?php

namespace Aeria\Meta;

use Aeria\Field\FieldGroupProcessor;
/**
 * MetaProcessor is a wrapper for FieldGroupProcessor
 * 
 * @category Meta
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class MetaProcessor extends FieldGroupProcessor
{
    /**
     * Returns the FieldGroupProcessor type
     *
     * @return string the type = "meta"
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getType()
    {
        return "meta";
    }
    /**
     * Gets the saved fields from a post
     *
     * @return array the saved fields
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getSavedFields()
    {
        return get_post_meta($this->id);
    }
}
