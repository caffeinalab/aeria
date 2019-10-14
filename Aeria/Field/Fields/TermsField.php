<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Fields\SelectField;
use Aeria\Field\Interfaces\FieldInterface;

/**
 * TermsField is the class that represents a terms field
 *
 * @category Field
 * @package  Aeria
 * @author   Lorenzo Girardi <lorenzo.girardi@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class TermsField extends SelectField
{

    protected $original_config;
    /**
     * Constructs the field
     *
     * @param string $parent_key the field's parent key
     * @param array  $config      the field's config
     * @param array  $sections   Aeria's sections config
     * @param array  $index      index for of the subfield
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.8
     */
    public function __construct($parent_key, $config, $sections, $index = null) {
        parent::__construct($parent_key, $config, $sections, $index);

        $this->original_config = json_decode(json_encode($config));

        $this->config['type'] = 'select';

        $taxonomy = (isset($config['taxonomy'])) ? $config['taxonomy'] : 'category';
        $hide_empty = (isset($config['hide_empty'])) ? $config['hide_emty'] : true;

        $terms = get_terms(array(
          'taxonomy' => $taxonomy,
          'hide_empty' => $hide_empty,
        ));

        $this->config['options'] = array_map( function ($term) {
          return array(
            'label' => $term->name,
            'value' => $term->term_id,
          );
        }, array_values($terms) );

        unset($this->config['taxonomy']);
    }
}
