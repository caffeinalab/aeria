<?php

use Aeria\Aeria;
use Aeria\Meta\Meta;
use Aeria\Meta\MetaProcessor;
use Aeria\Config\Config;
use Aeria\OptionsPage\OptionsPageProcessor;


if (!function_exists('dump')) {
    /**
     * Dumps the provided arguments
     * 
     * @param mixed ...$args the dumpable args
     *
     * @return void
     *
     * @access public
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
        $is_cli = in_array(php_sapi_name(), [ 'cli', 'cli-server' ]);
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
                    . $message
                    . "\n/*begin*/?><!--end-->\n\n",
                    true
                )
            );
        }
        echo $message;
    }
}


if (!function_exists('dd')) {
    /**
     * Dumps the provided arguments than dies
     * 
     * @param mixed ...$args the dumpable args
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    function dd(...$args)
    {
        dump(...$args);
        die();
    }
}

if (!function_exists('toSnake')) {
    /**
     * Converts camelCase to snake_case
     * 
     * @param string $convertible_text the text to convert
     *
     * @return string the converted text
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    function toSnake($convertible_text)
    {
        $convertible_text = preg_replace('/\s+/u', '', ucwords($convertible_text));
        return strtolower(
            preg_replace(
                '/(.)(?=[A-Z])/u',
                '$1' . '_',
                $convertible_text
            )
        );
    }
}


if (!function_exists('aeria')) {
    /**
     * Returns Aeria's instance
     * 
     * @param string $abstract the requested service
     *
     * @return mixed the service or Aeria's instance
     *
     * @access public
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
     * Returns Aeria's fields
     * 
     * @param WP_Post $post the current post
     *
     * @return array the retrieved fields
     *
     * @access public
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
          if( !in_array($post->post_type, $metabox["post_type"])){
            continue;
          }
          $processor = new MetaProcessor($post->ID, $metabox, $sections, $render_service);
          $fields[$name] = $processor->get();
        }

        return $fields;
    }
    /**
     * Returns an Aeria field
     * 
     * @param WP_Post $post    the current post
     * @param string  $metabox the metabox ID
     * @param string  $id      the field's ID
     *
     * @return array the retrieved fields
     *
     * @access public
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
        return ($processor->get()[$id]);
    }
    /**
     * Returns an Aeria metabox's fields
     * 
     * @param WP_Post $post    the current post
     * @param string  $metabox the metabox ID
     *
     * @return array the retrieved fields
     *
     * @access public
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
     * Returns Aeria's options
     * 
     * @param string $option_page the page we're checking
     *
     * @return array the retrieved fields
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    function get_aeria_options($option_page)
    {
      $aeria = aeria();
      $options = $aeria->make('config')->get('aeria.options', []);
      $sections = $aeria->make('config')->get('aeria.section', []);
      $render_service = $aeria->make('render_engine');
      $optionPage = array_merge(
        ['id' => $optionPage],
        $options[$optionPage]
      );
      $processor = new OptionsPageProcessor($optionPage['id'], $optionPage, $sections, $render_service);
      return $processor->get();
    }
    /**
     * Saves Aeria's provided fields
     * 
     * @param array $saving_data the data we're saving
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    function save_aeria_fields(array $saving_data)
    {
        foreach ($saving_data['fields'] as $field => $value) {
            update_post_meta($saving_data['post_ID'], $saving_data['metabox'].'-'.$field, $value);
        }
    }



    /**
     * Flattens an array
     * 
     * @param array $to_be_normalized the array we want to normalize
     * @param int   $times            the times to normalize the array
     *
     * @return array the retrieved fields
     *
     * @access public
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
