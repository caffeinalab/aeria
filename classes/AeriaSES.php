<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class AeriaSES {
	public static function init($key, $secret, $from, $region = 'eu-west-1') {
		$region = strtolower($region);
		add_action('phpmailer_init',function($mailer) use ($key, $secret, $from, $region){
			$mailer->isSMTP(true);
			$mailer->SMTPAuth = true;
			$mailer->Mailer = "smtp";
			$mailer->Host = "tls://email-smtp.$region.amazonaws.com";
			$mailer->Port = 465;
			$mailer->Username = $key;
			$mailer->Password = $secret;
			$mailer->SetFrom($from);
		});
  }
}
