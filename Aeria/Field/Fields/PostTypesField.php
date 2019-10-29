<?php

namespace Aeria\Field\Fields;

use Aeria\Field\Fields\SelectField;
use Aeria\Field\Interfaces\FieldInterface;
/**
 * PostTypesField is the class that represents a select with all post-types as options
 *
 * @category Field
 * @package  Aeria
 * @author   Alberto Parziale <alberto.parziale@caffeina.com>
 * @license  https://github.com/caffeinalab/aeria/blob/master/LICENSE  MIT license
 * @link     https://github.com/caffeinalab/aeria
 */
class PostTypesField extends SelectField
{

    protected $original_config;
    /**
     * Transform the config array; note that this does not operate on
     * `$this->config`: this way it can be called from outside
     *
     * @param array $config    the field's config
     *
     * @return array        the transformed config
     */
    public static function transformConfig(array $config) {
        $config['type'] = 'select';

        $config['options'] = [
            array( 'label' => 'page', 'value' => 'page'),
            array( 'label' => 'post', 'value' => 'post'),
        ];

        $post_types = get_post_types(array(
            'public' => true,
            '_builtin' => false
        ));

        $config['options'] = array_merge(
            $config['options'],
            array_map( function ($post_type) {
                return array( 'label' => $post_type, 'value' => $post_type);
            }, array_values($post_types) )
        );
        return parent::transformConfig($config);
    }
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
     * @since  Method available since Release 3.0.7
     */
    public function __construct($parent_key, $config, $sections, $index = null) {
        $this->original_config = json_decode(json_encode($config));
        parent::__construct($parent_key, $config, $sections, $index);
    }
}
