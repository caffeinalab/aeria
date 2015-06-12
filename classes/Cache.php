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


class Cache {
	static $cache_hashes = [];
	public static $driver = 'Aeria\\CacheBypass';

	public static function hash($key){
		return (is_object($key)||is_array($key))?sha1(serialize($key)):$key;
	}

	public static function set($data,$key,$group=false,$expire=0){
		$d = static::$driver;
		$hash = static::hash($key);
		$data = serialize($data);
		if(!isset(static::$cache_hashes[$group])) static::$cache_hashes[$group] = [];
		static::$cache_hashes[$group][$hash] = time();
		return $d::set($data,$hash,$group,$expire);
	}

	public static function get($key,$group=false,$default=null){
		$d = static::$driver;
		$hash = static::hash($key);
		$r = $d::get($hash,$group,$default);
		return is_serialized($r)?unserialize($r):$r;
	}

	public static function delete($key,$group=false){
		$d = static::$driver;
		$hash = static::hash($key);
		return $d::delete($hash,$group);
	}

	public static function deleteGroup($group){
		$d = static::$driver;
		$d::deleteGroup($group);
	}

	public static function clear(){
		$d = static::$driver;
		$d::clear();
	}

	public static function setDriver($new_driver){
		$c = 'Aeria\\Cache' . $new_driver;
		if (class_exists($c)) {
			static::$driver = $c;
		}
	}

}

class CacheWordPress {
	public static function get($key,$group='',$default=null) {
		if(null===($v=get_transient($key)) && $default){
			$v = is_callable($default)?call_user_func($default):$default;
			static::set($v,$key,$group);
		}
		return $v;
	}
	public static function set($data,$key,$group='',$expire=0) {
		return set_transient($key,$data,$expire);
	}
	public static function delete($key,$group='') {
		return delete_transient($key);
	}
	public static function clear() {}
	public static function deleteGroup($group) {}
}

function & redis(){
	if(!defined('PREDIS_LOADED')){
		require dirname(__DIR__).'/vendor/Predis/Autoloader.php';
		Predis\Autoloader::register();
		define('PREDIS_LOADED',1);
	}
	try {
		return new Predis\Client('tcp://127.0.0.1:6379');
	} catch(Exception $e){
		die('Aeria\\CacheRedis Error: '.$e->getMessage());
	}
}

class CacheRedis {

	public static function & redis() {
		return redis();
	}

	public static function get($key,$group=false,$default=null) {
		$r = redis();

		try {
			$v = $group ? $r->hget($group,$key) : $r->get($key);
		} catch(Exception $e) {
			$v = null;
		}

		if (null === $v && $default){
			$v = is_callable($default) ? call_user_func($default) : $default;
			static::set($v, $key, $group);
		}

		return $v;

	}

	public static function set($data,$key,$group=false,$expire=0) {
		$r = redis();
		try {
			if ($group) {
				return $r->hset($group, $key, $data);
			} else {
				if (!$expire) return $r->set($key, $data);
				else return $r->setex($key, $expire, $data);
			}
		} catch(Exception $e) {
			return null;
		}
	}

	public static function delete($key,$group=false) {
		$r = redis();
		try {
			return $group ? $r->hdel($group,$key) : $r->del($key);
		} catch(Exception $e) {
			return null;
		}
	}

	public static function deleteGroup($group) {
		$r = redis();
		try {
			return $r->delete($group);
		} catch(Exception $e) {
			return null;
		}
	}

	public static function clear() {
		$r = redis();
		return $r->flushall();
	}

}


class CacheBypass {

	public static function get($key,$group='') { return false; }
	public static function set($data,$key,$group='',$expire=0) {}
	public static function delete($key,$group='') {}
	public static function deleteGroup($group) {}
	public static function clear() {}

}
