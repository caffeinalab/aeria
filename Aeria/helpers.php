<?php

use Aeria\Aeria;
use Aeria\Meta\MetaProcessor;
use Aeria\OptionsPage\OptionsPageProcessor;

if (!function_exists('dump')) {
    /**
     * Dumps the provided arguments.
     *
     * @param mixed ...$args the dumpable args
     *
     * @since  Method available since Release 3.0.0
     */
    function dump(...$args)
    {
        $message = implode(
            "\n\n",
            array_map(
                function ($value) {
                    return var_export($value, true);
                },
                $args
            )
        );
        $is_cli = in_array(php_sapi_name(), ['cli', 'cli-server']);
        if (!$is_cli) {
            $message = preg_replace(
                [
                    '/\&lt\;\!\-\-begin\-\-\&gt\;.+?\/\*end\*\//',
                    '/\/\*begin\*\/.+?\&lt\;\!\-\-end\-\-\&gt\;/',
                    '/array\&nbsp\;\(\<br\s\/\>\)/',
                ],
                [
                    '',
                    '',
                    'array ( )',
                ],
                highlight_string(
                    "<!--begin--><?php/*end*/\n"
                    .$message
                    ."\n/*begin*/?><!--end-->\n\n",
                    true
                )
            );
        }
        echo $message;
    }
}

if (!function_exists('dd')) {
    /**
     * Dumps the provided arguments than dies.
     *
     * @param mixed ...$args the dumpable args
     *
     * @since  Method available since Release 3.0.0
     */
    function dd(...$args)
    {
        var_dump(...$args);
        die();
    }
}

if (!function_exists('toSnake')) {
    /**
     * Converts camelCase to snake_case.
     *
     * @param string $convertible_text the text to convert
     *
     * @return string the converted text
     *
     * @since  Method available since Release 3.0.0
     */
    function toSnake($convertible_text)
    {
        $convertible_text = preg_replace('/\s+/u', '', ucwords($convertible_text));

        return strtolower(
            preg_replace(
                '/(.)(?=[A-Z])/u',
                '$1'.'_',
                $convertible_text
            )
        );
    }
}

if (!function_exists('aeria')) {
    /**
     * Returns Aeria's instance.
     *
     * @param string $abstract the requested service
     *
     * @return mixed the service or Aeria's instance
     *
     * @since  Method available since Release 3.0.0
     */
    function aeria(/* ?string */ $abstract = null)
    {
        if (is_null($abstract)) {
            return Aeria::getInstance();
        }

        return Aeria::getInstance()->make($abstract);
    }
    /**
     * Returns Aeria's fields.
     *
     * @param WP_Post $post the current post
     *
     * @return array the retrieved fields
     *
     * @since  Method available since Release 3.0.0
     */
    function get_aeria_fields($post)
    {
        $aeria = aeria();
        $meta_service = $aeria->make('meta');
        $metaboxes = $aeria->make('config')->get('aeria.meta', []);
        $sections = $aeria->make('config')->get('aeria.section', []);
        $render_service = $aeria->make('render_engine');
        $fields = [];
        foreach ($metaboxes as $name => $data) {
            $metabox = array_merge(
              ['id' => $name],
              $data
            );
            $postTypes = isset($metabox['post_type']) ? $metabox['post_type'] : [];
            $templates = isset($metabox['templates']) ? $metabox['templates'] : [];

            if (!in_array($post->post_type, $postTypes) && !in_array(get_page_template_slug($post), $templates)) {
                continue;
            }
            $processor = new MetaProcessor($post->ID, $metabox, $sections, $render_service);
            $fields[$name] = $processor->get();
        }

        return $fields;
    }
    /**
     * Returns an Aeria field.
     *
     * @param WP_Post $post    the current post
     * @param string  $metabox the metabox ID
     * @param string  $id      the field's ID
     *
     * @return array the retrieved fields
     *
     * @since  Method available since Release 3.0.0
     */
    function get_aeria_field($post, $metabox, $id)
    {
        $aeria = aeria();
        $metaboxes = $aeria->make('config')->get('aeria.meta', []);
        $sections = $aeria->make('config')->get('aeria.section', []);
        $render_service = $aeria->make('render_engine');
        $metaboxes[$metabox]['id'] = $metabox;
        $processor = new MetaProcessor($post->ID, $metaboxes[$metabox], $sections, $render_service);

        return $processor->get()[$id];
    }
    /**
     * Returns an Aeria metabox's fields.
     *
     * @param WP_Post $post    the current post
     * @param string  $metabox the metabox ID
     *
     * @return array the retrieved fields
     *
     * @since  Method available since Release 3.0.0
     */
    function get_aeria_metabox($post, $metabox)
    {
        $aeria = aeria();
        $meta_service = $aeria->make('meta');
        $metaboxes = $aeria->make('config')->get('aeria.meta', []);
        $sections = $aeria->make('config')->get('aeria.section', []);
        $render_service = $aeria->make('render_engine');
        $fields = [];
        $metabox = array_merge(
          ['id' => $metabox],
          $metaboxes[$metabox]
      );
        $processor = new MetaProcessor($post->ID, $metabox, $sections, $render_service);
        $fields = $processor->get();

        return $fields;
    }
    /**
     * Returns Aeria's options.
     *
     * @param string $option_page the page we're checking
     *
     * @return array the retrieved fields
     *
     * @since  Method available since Release 3.0.0
     */
    function get_aeria_options($optionPage = null)
    {
        $aeria = aeria();
        $options = $aeria->make('config')->get('aeria.options', []);
        $sections = $aeria->make('config')->get('aeria.section', []);
        $render_service = $aeria->make('render_engine');
        if (is_null($optionPage)) {
            $result = array();
            foreach (array_keys($options) as $key) {
                $result[$key] = get_aeria_options_by_page($key);
            }

            return $result;
        } elseif (!array_key_exists($optionPage, $options)) {
            return [];
        } else {
            return get_aeria_options_by_page($optionPage);
        }
    }
    /**
     * Returns Aeria's options for a specified option page.
     *
     * @param string $option_page the page we're checking
     *
     * @return array the retrieved fields
     *
     * @since  Method available since Release 3.0.8
     */
    function get_aeria_options_by_page($optionPage)
    {
        $aeria = aeria();
        $options = $aeria->make('config')->get('aeria.options', []);
        $sections = $aeria->make('config')->get('aeria.section', []);
        $render_service = $aeria->make('render_engine');

        if (!isset($options[$optionPage])) {
            return [];
        }

        $optionPage = array_merge(
          ['id' => $optionPage],
          $options[$optionPage]
        );
        $processor = new OptionsPageProcessor($optionPage['id'], $optionPage, $sections, $render_service);

        return $processor->get();
    }

    /**
     * Saves Aeria's provided fields.
     *
     * @param array $saving_data the data we're saving
     *
     * @since  Method available since Release 3.0.0
     */
    function save_aeria_fields(array $saving_data)
    {
        foreach ($saving_data['fields'] as $field => $value) {
            update_post_meta($saving_data['post_ID'], $saving_data['metabox'].'-'.$field, $value);
        }
    }

    /**
     * Flattens an array.
     *
     * @param array $to_be_normalized the array we want to normalize
     * @param int   $times            the times to normalize the array
     *
     * @return array the retrieved fields
     *
     * @since  Method available since Release 3.0.0
     */
    function array_flat(array $to_be_normalized, $times = -1)
    {
        $pivot = [];
        foreach ($to_be_normalized as $key => $value) {
            if (is_array($value)) {
                $res_val = $times > 1 || $times == -1
                    ? array_flat($value, $times == -1 ? $times : $times - 1)
                    : $value;
                $pivot = array_merge($pivot, $res_val);
            } else {
                $pivot[$key] = $value;
            }
        }

        return $pivot;
    }
}

if (!function_exists('aeria_array_filter')) {
    /**
     * Filters a data array.
     *
     * @param array $data   the data to be filtered
     * @param array $config the filter configuration
     *
     * @return array the filtered data
     *
     * @since  Method available since Release 3.2.1
     */
    function aeria_array_filter($data = [], $config = [])
    {
        if (empty($data) || (!isset($config['exclude']) && !isset($config['include']))) {
            return $data;
        }

        return array_filter(
            $data,
            function ($value) use ($config) {
                return isset($config['exclude'])
                    ? !in_array($value, $config['exclude'])
                    : in_array($value, $config['include']);
            }
        );
    }
}

if (!function_exists('aeria_object_filter')) {
    /**
     * Filters a select options array.
     *
     * @param array  $objects the select options
     * @param array  $config  the filter configuration
     * @param string $key     the filter configuration
     *
     * @return array the filtered select options
     *
     * @since  Method available since Release 3.2.1
     */
    function aeria_objects_filter($objects = [], $config = [], $key = 'value')
    {
        if (empty($objects) || (!isset($config['exclude']) && !isset($config['include']))) {
            return $objects;
        }

        return array_values(array_filter(
            $objects,
            function ($object) use ($config, $key) {
                return isset($config['exclude'])
                    ? !in_array($object[$key], $config['exclude'])
                    : in_array($object[$key], $config['include']);
            }
        ));
    }
}
