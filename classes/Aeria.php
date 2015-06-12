<?php

/**
 * Aeria
 *
 * https://github.com/CaffeinaLab/aeria
 *
 * Caffeina srl (http://caffeina.it)
 * Copyright 2015 - MIT License
 */

namespace Aeria;

if( false === defined('AERIA') ) exit;

class Aeria {

	private static 	$eventHandlers = [],
									$dataHolder		 = [];

	/**
	 * Repository : Get value
	 * @param  string $key    The key of value
	 * @param  mixed $default The default value (if a callable is passed it will be executed and return value used)
	 * @return mixed          The value associated with $key
	 */
	public static function get($key, $default=null){
		if ( isset(static::$dataHolder[$key]) ) {
			return static::$dataHolder[$key];
		} else {
			return static::$dataHolder[$key] = (is_callable($default) ? call_user_func($default) : $default);
		}
	}

	/**
	 * Repository : Set value
	 * @param  string $key    The key of value
	 * @param  mixed $value 	The value associated with $key
	 * @return mixed          The value setted
	 */
	public static function set($key, $value){
		return static::$dataHolder[$key] = $value;
	}

	/**
	 * Repository : Delete a value
	 * @param  string $key    The key of value
	 * @return void
	 */
	public static function delete($key){
		unset(static::$dataHolder[$key]);
	}

	/**
	 * Events : bind
	 * @param  string $name The event name
	 * @param  callable $callback The event listener
	 * @return void
	 */
	public static function on($name, $callback){
		static::$eventHandlers[$name][] = $callback;
	}

	/**
	 * Events : unbind
	 * @param  string $name The event name
	 * @return void
	 */
	public static function off($name){
		if (isset(static::$eventHandlers[$name])){
			unset(static::$eventHandlers[$name]);
		}
	}

	/**
	 * Events : get all binded events
	 * @return array
	 */
	public static function bindedEvents(){
		return static::$eventHandlers;
	}

	/**
	 * Events : Trigger
	 * @param  string $name   The event name
	 * @param  array  $params Optional parameters passed to event listeners
	 * @return mixed
	 */
	public static function trigger($name,array $params=[]){
		if (isset(static::$eventHandlers[$name])) {
			foreach ( static::$eventHandlers[$name] as $handler ) {
				return call_user_func_array($handler,$params);
			}
		}
		return null;
	}

}
