<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class AeriaPlugin {
	static $plugins = array();
	protected static $PARAMS = array();

	static public function params(){
		return is_array(static::$PARAMS)?array_values(static::$PARAMS):explode(',',static::$PARAMS);
	}

	static public function enable($plugin_name){
		foreach((array)$plugin_name as $plugin){
			static::$PARAMS = [];

			if(is_array($plugin)){
				$plugin_name = $plugin[0];
				static::$PARAMS = $plugin[1];
			} else {
				$plugin_name = $plugin;
			}

			if(is_file($plugin_path = AERIA_DIR.'plugins/' . $plugin_name . '.php')){
				static::$plugins[$plugin_name] = include($plugin_path);
			} else {
				static::$plugins[$plugin_name] = false;
			}
		}
		static::$PARAMS = [];
	}

}

function aeria_plugins_url($url='', $path='', $plugin=''){
	if(empty($plugin)) $plugin = empty($path)?'':basename(dirname($path));
    return AERIA_PLUGINS_URL.(empty($plugin)?'':'/'.$plugin).(empty($url)?'':'/'.$url);
}