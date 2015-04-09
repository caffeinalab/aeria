<?php

if( false === defined('AERIA') ) exit;

class Aeria {

	private static $eventHandlers = [];
	private static $holder = [];

	public static function get($key){
		return @static::$holder[$key]?:null;
	}

	public static function set($key,$value){
		return static::$holder[$key] = $value;
	}

	public static function delete($key){
		unset(static::$holder[$key]);
	}


	public static function on($name,$callback){
		static::$eventHandlers[$name][] = $callback;
	}

	public static function off($name){
		if(isset(static::$eventHandlers[$name]))
			unset(static::$eventHandlers[$name]);
	}

	public static function bindedEvents(){
		return static::$eventHandlers;
	}

	public static function trigger($name,array $params=[]){
		if(isset(static::$eventHandlers[$name])) {
			foreach ( static::$eventHandlers[$name] as $handler ) {
				return call_user_func_array($handler,$params);
			}
		}
		return null;
	}


	public static function get_posts($options,$force_refresh=false){
		global $wpdb;

		if ( !$results ){

			if(empty($options['posts_per_page'])&&empty($options['numberposts']))
				$options['posts_per_page'] = -1;

			if($called_fields = (empty($options['fields'])?false:(array)$options['fields'])){
				$options['fields'] = 'ids';
				$ids = [];
		    	$ids = get_posts($options);
		    	if($ids){
		    		$ids = array_map('intval',$ids);
		    		$results = $wpdb->get_results("SELECT ID,".implode(',',$called_fields)." FROM $wpdb->posts WHERE ID IN (".implode(',',$ids).');');
		    	} else {
		    		$results = [];
		    	}

		    } else {
		    	$results = get_posts($options);
		    }
		}

		return $results;
	}

	public static function get_tags($options,$force_refresh=false){
		global $wpdb;

		$hash = sha1(serialize($options));

		if (!$results){
    		$results = get_tags($options);
    	}
	    return $results;
	}
}
