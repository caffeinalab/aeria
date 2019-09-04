<?php

use Aeria\Aeria;
use Aeria\Meta\Meta;
use Aeria\Meta\MetaProcessor;
use Aeria\Config\Config;
use Aeria\OptionsPage\OptionsPageProcessor;


if (!function_exists('dump')) {
  function dump(...$args)
  {
      $message = implode(
          "\n\n",
          array_map(
              function($value) {
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
  function dd(...$args)
  {
      dump(...$args);
      die();
  }
}

if (!function_exists('toSnake')) {
    function toSnake($convertibleText)
    {
        $convertibleText = preg_replace('/\s+/u', '', ucwords($convertibleText));
        return strtolower(
            preg_replace(
                '/(.)(?=[A-Z])/u',
                '$1' . '_',
                $convertibleText
            )
        );
    }
}


if (!function_exists('aeria')) {
    function aeria(/* ?string */ $abstract = null)
    {
        if (is_null($abstract)) {
            return Aeria::getInstance();
        }
        return Aeria::getInstance()->make($abstract);
    }

    function get_aeria_fields($post)
    {
        $aeria = aeria();
        $meta_service = $aeria->make('meta');
        $metaboxes = $aeria->make('config')->get('aeria.meta', []);
        $sections = $aeria->make('config')->get('aeria.section', []);
        $fields = [];
        foreach ($metaboxes as $name => $data) {
          $metabox = array_merge(
              ['id' => $name],
              $data
          );
          if( !in_array($post->post_type, $metabox["post_type"])){
            continue;
          }
          $processor = new MetaProcessor($post->ID, $metabox, $sections);
          $fields[$name] = $processor->get();
        }

        return $fields;
    }

    function get_aeria_field($post, $metabox, $id)
    {
        $aeria = aeria();
        $metaboxes = $aeria->make('config')->get('aeria.meta', []);
        $sections = $aeria->make('config')->get('aeria.section', []);
        $metaboxes[$metabox]['id'] = $metabox;
        $processor = new MetaProcessor($post->ID, $metaboxes[$metabox], $sections);
        return ($processor->get()[$id]);
    }

    function get_aeria_metabox($post, $metabox)
    {
      $aeria = aeria();
      $meta_service = $aeria->make('meta');
      $metaboxes = $aeria->make('config')->get('aeria.meta', []);
      $sections = $aeria->make('config')->get('aeria.section', []);
      $fields = [];
      $metabox = array_merge(
          ['id' => $metabox],
          $metaboxes[$metabox]
      );
      $processor = new MetaProcessor($post->ID, $metabox, $sections);
      $fields = $processor->get();
      return $fields;
    }

    function get_aeria_options($optionPage)
    {
      $aeria = aeria();
      $options = $aeria->make('config')->get('aeria.options', []);
      $sections = $aeria->make('config')->get('aeria.section', []);
      $optionPage = array_merge(
        ['id' => $optionPage],
        $options[$optionPage]
      );
      $processor = new OptionsPageProcessor($optionPage['id'], $optionPage, $sections);
      return $processor->get();
    }

    function save_aeria_fields(array $saving_data)
    {
        foreach ($saving_data['fields'] as $field => $value) {
            update_post_meta($saving_data['post_ID'], $saving_data['metabox'].'-'.$field, $value);
        }
    }




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
