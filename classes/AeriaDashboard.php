<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */

class AeriaDashboard {
	public static $anonboxes=0;
	public static $boxes=[];

	public static function clear(){
		$box = static::$boxes;
		add_action('wp_dashboard_setup',function() use ($box){
			global $wp_meta_boxes;
			$dash = & $wp_meta_boxes['dashboard']['normal']['core'];
			foreach($dash as $slug => $value){
				if(false===isset($box[$slug])) {
					unset($wp_meta_boxes['dashboard']['side']['core'][$slug]);
					unset($wp_meta_boxes['dashboard']['normal']['core'][$slug]);
				}
			}
		});
	}

	public static function add($title,$callback,$slug=''){
		if (empty($slug)) $slug = 'aeria_dashboard_wdg_'.static::$anonboxes++;
		if(false==is_callable($callback)) $callback = function(){};
		static::$boxes[$slug] = $callback;
		add_action('wp_dashboard_setup',function() use ($slug,$title,$callback){
			wp_add_dashboard_widget($slug,$title,$callback);
		});
	}
}