<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class AeriaShortcode {

  static public function register($name,$callback,$default_params=array()){
    add_shortcode($name, function($atts,$content='') use (&$callback,&$default_params){
        $pars = wp_parse_args($atts,(array)$default_params);
        ob_start();
        call_user_func_array($callback, [$pars,empty($content)?'':($content)]);
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    });
  }

  static public function run($name,$atts,$content=null){
    if(is_array($atts)){
      $attstring = '';
      foreach($atts as $att_name => $att_value){
        $attstring .= ' '.$att_name.'="'.trim($att_value).'"';
      }
    } else {
      $attstring = ' '.$atts;
    }

    if($content){
      $code = '['.$name.$attstring.']'.trim(str_replace('<br />','',$content)).'[/'.$name.']';
    } else {
      $code = '['.$name.$attstring.' /]';
    }
    return ($code);
  }
}
