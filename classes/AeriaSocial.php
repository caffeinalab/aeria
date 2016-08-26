<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class AeriaSocial {

	public static $hash_prefix = 'AERIAS_';
	public static $config = [
		'cache_ttl'		=> 600, // in seconds
		'services'		=> [
			'facebook'	=> [ 'claim' => 'Condividi' ],
			'twitter'	=> [ 'claim' => 'Tweet' ],
			'gplus'		=> [ 'claim' => 'Condividi' ],
			'linkedin'	=> [ 'claim' => 'Consiglia' ],
		]
	];

	public static function init($config = []) {
		static::$config = array_merge(static::$config, $config);

		// Added possibility to use outside of Wordpress
		if (defined('ABSPATH')) {

			// Install CSS + JS
			wp_enqueue_script('aeria.social', AERIA_URL . 'resources/js/aeria.social.js', ['jquery']);
			wp_enqueue_style('aeria.social', AERIA_URL . 'resources/css/aeria.social.css');
			if (isset(static::$config['apiurl'])) {
				wp_localize_script('aeria.social', 'AERIA_SOCIAL', [ 'URL' => static::$config['apiurl'] ]);
			}

			// Install AJAX handler
			AeriaAJAX::register('aeriasocial.get', function(){
				if (!isset($_REQUEST['uri'])) {
					echo json_encode([ 'error' => 'Please provide a URI' ]);
					exit(1);
				}

				echo json_encode(AeriaSocial::getCount($_REQUEST['uri']));
				exit;
			});

		}
	}

	public static function widget($uri, $info = [], $opt = []) {
		$stats = null;

		if (empty($opt['onlyajax'])) {
			$stats = static::getCountForURI($uri, true);
		}

		$r = '<header class="aeriasocial-btns" ' . (is_null($stats) && !isset($opt['nocount']) ? 'data-aeriasocial-needajax="true"' : '') . ' data-aeriasocial-uri="' . $uri . '" ' . '>';
		foreach (array_keys(static::$config['services']) as $service) {
			$the_service = static::$config['services'][$service];

			$r .= '<div data-aeriasocial-service="' . $service . '" ';
			foreach ((array)$info as $k => $v) {
				$r .= ' data-aeriasocial-' . $k . '="' . $v . '" ';
			}
			$r .= 'class="aeriasocial-btn aeriasocial-btn-' . $service . '">';

			$r .= '<a class="aeriasocial-claim">';
			$r .= '<i class="aeriasocial-icon">&nbsp;</i>';
			$r .= '<span class="aeriasocial-text">' . $the_service['claim'] . '</span>';
			$r .= '</a>';

			if ( ! isset($opt['nocount'])) {
				$r .= '<span class="aeriasocial-count"><i></i><u></u>';
				$r .= '<span data-aeriasocial-count>';
				if (!is_null($stats)) $r .= (string)$stats['services'][$service];
				$r .= '</span>';
				$r .= '</span>';
			}

			$r .= '</div>';
		}
		$r .= '</header>';

		return $r;
	}

	public static function widgetOnlyShare($uri, $info = []) {
		return static::widget($uri, $info, [ 'nocount' => true ]);
	}

	public static function widgetOnlyAjax($uri, $info=[]) {
		return static::widget($uri, $info, [ 'onlyajax' => true ]);
	}

	public static function widgetSum($uri, $info=[], $opt=[]) {
		$r = '<section class="aeriasocial-container">';
			$r .= '<div class="aeriasocial-sum" data-aeriasocial-count-sum></div>';
			$r .= static::widget($uri, $info, $opt);
		$r .= '</section>';
		return $r;
	}

	public static function widgetSumOnlyAjax($uri, $info) {
		return static::widgetSum($uri, $info, [ 'onlyajax'=> true ]);;
	}

	protected static function getHash($uri) {
		return md5($uri);
	}

	public static function getCount($uris, $only_cache = false) {
		if (!is_array($uris)) $uris = [ $uris ];

		$result = [];
		$uris_to_fetch = [];
		$hashes = [];

		foreach (array_keys(static::$config['services']) as $service_key) {
			$uris_to_fetch[ $service_key ] = [];
		}

		foreach ($uris as $uri) {
			$hashes[ $uri ] = static::getHash($uri);

			$data = [
			'uri' 				=> $uri,
			'hash' 				=> $hashes[ $uri ],
			'services' 			=> [],
			'services_errors' => [],
			'sum' 				=> 0
			];

			foreach (array_keys(static::$config['services']) as $service_key) {
				$count = AeriaCache::get( static::$hash_prefix . $hashes[ $uri ] . '_'. $service_key );

				if ($count == null || (time() > $count['expire'])) {				
					if ($only_cache === false) {
						$uris_to_fetch[ $service_key ][] = $uri;
					}
				}

				$data['services'][ $service_key ] = !is_null($count) ? intval($count['count']) : 0;
				$result[ $uri ] = $data;
			}
		}

		foreach ($uris_to_fetch as $service_key => $uris_to_fetch_per_service) {
			if (empty($uris_to_fetch_per_service)) continue;

			try {

				$api_data = forward_static_call([ 'static', 'getCountForService' . ucfirst($service_key)], $uris_to_fetch_per_service);
				
				foreach ($uris_to_fetch_per_service as $uri) {
					$count = $api_data[ $uri ];
					$result[ $uri ]['services'][ $service_key ] = $count;

					AeriaCache::set([
					'expire' => time() + static::$config['cache_ttl'],
					'count' 	=> $count
					], static::$hash_prefix . $hashes[ $uri ] . '_'. $service_key);

				}

			} catch (Exception $e) {

				foreach ($uris_to_fetch_per_service as $uri) {
					$result[ $uri ]['services'][$service_key] = 0;
					$result[ $uri ]['services_errors'] = $e->getMessage();
				}

			}
		}			

		foreach ($result as $uri => $data) {
			
			$result[ $uri ]['sum'] = array_reduce($data['services'], function($carry, $item) { 
				return $carry + intval($item); 
			}, 0);

			AeriaCache::set([
			'uri' 	=> $uri,
			'sum' 	=> $data['sum']
			], static::$hash_prefix . 'SUMS_' . $hashes[ $uri ]);
	
		}
			
		return $result;
	}

	public static function getCountForServiceEmpty($uris) {
		$result = [];
		foreach ($uris as $uri) $result[ $uri ] = 0;
		return $result;
	}

	public static function getCountForServiceFacebook($uris) {
		$result = [];

		$uri_encoded = implode(',', $uris);		
		$url = "https://graph.facebook.com/?ids=" . $uri_encoded;

		if (!empty(static::$config['facebook_app_token'])) {
			$url .= "&access_token=" . static::$config['facebook_app_token'];
		}

		$data = AeriaNetwork::json($url);

		foreach ($uris as $uri) {
			$result[ $uri ] = 0;
			if (!empty($data->{$uri}->share->share_count)) {
				$result[ $uri ] = intval($data->{$uri}->share->share_count);
			}
		}

		return $result;
	}

	public static function getCountForServiceLinkedin($uris) {
		$result = [];
		
		foreach ($uris as $uri) {
			$result[ $uri ] = 0;
			$data = AeriaNetwork::json("http://www.linkedin.com/countserv/count/share?format=json&url=" . urlencode($uri));
			if (!empty($data->count)) {
				$result[ $uri ] = intval($data->count);
			}
		}

		return $result;
	}

	public static function getCountForServiceTwitter($uris) {
		return static::getCountForServiceEmpty($uris);
	}

	public static function getCountForServiceGplus($uris) {
		$result = [];
		
		foreach ($uris as $uri) {
			$result[ $uri ] = 0;
			$data = AeriaNetwork::send("https://plusone.google.com/_/+1/fastbutton?count=true&url=" . urlencode($uri));
			if (preg_match("/{c: (\d+)/", $data, $matches)) {
				$result[ $uri ] = intval($matches[1]);
			}
		}

		return $result;
	}

	public static function getMostSharedContents() {
		$Redis = AeriaCacheRedis::redis();

		$data = array_map(function($key) use ($Redis) {
			return unserialize($Redis->get($key));
		}, $Redis->keys( static::$hash_prefix . 'SUMS_*' ));

		usort($data, function($a, $b) {
			return $a['sum'] - $b['sum'];
		});

		return $data;
	}

}
