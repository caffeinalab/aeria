<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

wp_enqueue_script('aeria.ajax', AERIA_URL.'resources/js/aeria.ajax.js');
wp_localize_script('aeria.ajax', 'AERIA_AJAX', [ 'URL' => AERIA_HOME_URL.'index.php' ]);

// Install Ajax Handler
add_filter('query_vars',function($vars) {
	$vars[] = 'ajax';
	return $vars;
});

add_action('template_redirect', function() {
	if ($action = get_query_var('ajax')) {
		ini_set('zlib.output_compression','On');

		if (!defined('DOING_AJAX')) {
			define('DOING_AJAX', true);
		}

		send_nosniff_header();

		$action = $action;
		$args = $_REQUEST;
		$user_logged = is_user_logged_in();

		$privateHook = 'AERIA_AJAX_HANDLER_private_'.$action;
		$publicHook = 'AERIA_AJAX_HANDLER_public_'.$action;

		unset($args['ajax']);
		ob_end_clean();

		if ($user_logged && AeriaAJAX::existsPrivate($action)) {
			do_action($privateHook,$args);
		} elseif (AeriaAJAX::exists($action)) {
			do_action($publicHook,$args);
		}

		exit;
	}
});

class AeriaAJAX {

	public static $registry = array();

	public static function exists($function_name){
		return isset(static::$registry['AERIA_AJAX_HANDLER_public_'.$function_name]);
	}

	public static function existsPrivate($function_name){
		return isset(static::$registry['AERIA_AJAX_HANDLER_private_'.$function_name]);
	}

	public static function register($function_name,$callback){
		$key='AERIA_AJAX_HANDLER_public_'.$function_name;
		static::$registry[$key] = $callback;
		add_action($key,$callback);
	}

	public static function registerPrivate($function_name,$callback){
		$key='AERIA_AJAX_HANDLER_private_'.$function_name;
		static::$registry[$key] = $callback;
		add_action($key,$callback);
	}

	public static function sendJSON($payload){
		ob_end_clean();
		header('Content-Type: application/json');
		die(json_encode($payload,JSON_NUMERIC_CHECK));
	}

}
