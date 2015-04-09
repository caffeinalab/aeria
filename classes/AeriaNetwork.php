<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class AeriaNetwork {

	public static $errors = [];

	public static function send($uri, $data=[], $method='GET', $headers=[], $additional=[])
	{
		if (is_array($data)) $data = http_build_query($data);
		$method = strtoupper($method);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		if ($method=='POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			if (!empty($data)) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		} else {
			if (!empty($data)) $uri .= '?' . $data;
		}

		if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $uri);

		if (!empty($additional)) foreach ((array)$additional as $k => $v) curl_setopt($curl, $k, $v);

		try {
			$content = curl_exec($ch);
			if (curl_errno($ch)) throw new Exception(curl_error($ch));
			if (empty($content)) throw new Exception("Empty response");
		} catch (Exception $e) {
			curl_close($ch);
			AeriaDebug::exception($e);
			static::$errors[] = $e->getMessage();
			return null;
		}

		curl_close($ch);
		return $content;
	}

	public static function json()
	{
		$response = forward_static_call_array(['static','send'], func_get_args());
		$json = json_decode($response);
		if (json_last_error() || empty($json)) return null;
		return $json;
	}

	public static function jsonp()
	{
		$response = forward_static_call_array(['static','send'], func_get_args());
		$response = str_replace('cb({', '{', $response);
		$response = str_replace('})','}', $response);
		$json = json_decode($response);
		if (json_last_error() || empty($json)) return null;
		return $json;
	}

}
