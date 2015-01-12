<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

wp_enqueue_script('aeria.social', AERIA_URL.'resources/js/aeria.social.js', ['jquery']);
wp_enqueue_style('aeria.social', AERIA_URL.'resources/css/aeria.social.css');

AeriaAJAX::register('aeriasocial.get', function(){
	if (!isset($_REQUEST['uri'])) {
		die(json_encode([ 'error' => 'Please provide a URI' ]));
	}

	die(json_encode(AeriaSocial::getCount($_REQUEST['uri'])));
});

class AeriaSocial {

	public static $services = [];
	public static $config = [
		'cache_ttl'=> 600,
		'sharedcount_url' => 'http://free.sharedcount.com',
		'sharedcount_apikey'=> '7720b008f401af653af5f3e2bf3141aa32240ee0',
		'services'=> [
			'facebook'	=> [ 'claim' => 'Condividi' ],
			'twitter'	=> [ 'claim' => 'Tweet' ],
			'linkedin'	=> [ 'claim' => 'Consiglia' ],
			'gplus'		=> [ 'claim' => 'Condividi' ]
		]
	];

	public static function init($config = null) {
		if (is_array($config)) static::$config = array_merge(static::$config, $config);
		static::$services = array_keys(static::$config['services']);

		if (isset(static::$config['apiurl'])) {
			wp_localize_script('aeria.social', 'AERIA_SOCIAL', [ 'URL' => static::$config['apiurl'] ]);
		}
	}

	public static function widget($uri, $info=[], $opt=[]) {
		$stats = null;
		if (empty($opt['onlyajax'])) {
			$stats = static::getViaCacheCount($uri);
		}

		$r = '<header data-hash="' . static::getHash($uri) . '" class="aeriasocial-btns" ' . (is_null($stats) && !isset($opt['nocount']) ? 'data-aeriasocial-needajax="true"' : '') . ' data-aeriasocial-uri="' . $uri . '" ' . '>';
		foreach (static::$services as $service) {
			$the_service = static::$config['services'][$service];

			$r .= '<div data-aeriasocial-service="' . $service . '" ';
			foreach ((array)$info as $k => $v) $r .= ' data-aeriasocial-' . $k . '="' . $v . '" ';
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

	public static function widgetOnlyShare($uri, $info=[]) {
		return static::widget($uri, $info, [ 'nocount' => true ]);
	}

	public static function widgetOnlyAjax($uri, $info=[]) {
		return static::widget($uri, $info, [ 'onlyajax'=> true ]);
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
		return 'aeriasocial_' . substr(md5($uri), 0, 12);
	}

	public static function getViaCacheCount($uri) {
		if (isset($_GET['__nocache'])) return null;
		$hash = static::getHash($uri);
		return AeriaCache::get($hash);
	}

	public static function setCacheCount($uri, $data) {
		$hash = static::getHash($uri);
		AeriaCache::set($data, $hash, false, static::$config['cache_ttl']);
	}

	public static function getViaAPICount($uri) {
		return AeriaNetwork::json(static::$config['sharedcount_url'], [
			'url' 	=> $uri,
			'apikey' => static::$config['sharedcount_apikey']
		]);
	}

	public static function getCount($uri) {
		if (is_array($uri)) {
			return array_map([ 'static', 'getCountSingle' ], $uri);
		} else {
			return $this->getCountSingle($uri);
		}
	}

	public static function getCountSingle($uri) {
		try {
			$data = static::getViaCacheCount($uri);

			if (is_null($data)) {
				$data = static::getViaAPICount($uri);
				if (empty($data)) throw new Exception('Empty data');
				if (isset($data->Error)) throw new Exception($data->Error);

				$data = static::parseCount($uri, $data);
				static::setCacheCount($uri, $data);
			}

			return $data;

		} catch (Exception $e) {
			return [
				'services'	=>	[],
				'error'		=>	$e->getMessage()
			];
		}
	}

	protected static function parseCount($uri, $info) {
		$r = [];
		$r['uri'] = $uri;
		$r['services'] = [
			'facebook' 		=> $info->Facebook->total_count,
			'twitter'  		=> $info->Twitter,
			'stumbleupon' 	=> $info->StumbleUpon,
			'reddit'			=> $info->Reddit,
			'gplus'			=> $info->GooglePlusOne,
			'pinterest'		=> $info->Pinterest,
			'linkedin'		=> $info->LinkedIn
		];
		$r['sum'] = array_reduce($r['services'], function($carry, $item) { return $carry + $item; }, 0);
		return $r;
	}

	public static function getMostSharedContents() {
		if (AeriaCache::$driver !== 'AeriaCacheRedis') {
			throw new Exception('You can obtain getMostSharedContents() only with Redis.');
		}

		$Redis = AeriaCacheRedis::redis();

		$keys = $Redis->keys('aeriasocial_*');
		$data = array_map(function($key) use ($Redis) {
			return unserialize($Redis->get($key));
		}, $Redis->keys('aeriasocial_*'));

		usort($data, function($a, $b) {
			return $a['sum'] - $b['sum'];
		});

		return $data;
	}

}