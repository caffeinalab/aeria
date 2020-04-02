<?php

namespace Aeria\Field\Fields;

/**
 * TermsField is the class that represents a terms field.
 *
 * @category Field
 *
 * @author   Lorenzo Girardi <lorenzo.girardi@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 *
 * @see     https://github.com/caffeinalab/aeria
 */
class TermsField extends SelectField
{
    /**
     * Transform the config array; note that this does not operate on
     * `$this->config`: this way it can be called from outside.
     *
     * @param array $config the field's config
     *
     * @return array the transformed config
     */
    public static function transformConfig(array $config)
    {
        $config['type'] = 'select';
        $filter = (isset($config['filter'])) ? $config['filter'] : [];
        $config['ajax'] = array_merge($filter, ['endpoint' => '/wp-json/aeria/terms']);

        unset($config['filter']);

        return parent::transformConfig($config);
    }
}
