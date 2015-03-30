<?php

// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class SesClientProxyForPHPMailer {
	private $phpmailer;

	public function __construct($phpmailer) {
		$this->phpmailer = $phpmailer;
	}

	public function Send() {
		// Build the raw email
		$this->phpmailer->preSend();

		try {

			return AeriaSES::$client->sendRawEmail([
				'RawMessage' => [
					// Get the builded RAW email (comprensive of headers)
					'Data' => base64_encode($this->phpmailer->getSentMIMEMessage())
				]
			]);

		} catch (Exception $e) {
			// wp_mail() catch only phpmailerException
			trigger_error($e->getMessage(), E_WARNING);
			throw new phpmailerException($e->getMessage(), $e->getCode());
		}
	}
}

class AeriaSES {

	public static $client = null;
	public static $config = [];

	public static function init($key, $secret, $region) {
		static::$config = array(
			'key'    => $key,
			'secret' => $secret,
			'region' => $region
		);
	}

	public static function enable() {
		add_action('phpmailer_init', function(&$phpmailer) {

			if (static::$client == null) {
				require __DIR__.'/../vendor/aws.phar';
				static::$client = Aws\Ses\SesClient::factory(static::$config);
			}

			$phpmailer = new SesClientProxyForPHPMailer($phpmailer);
		});
	}

}
