<?php

define('AERIA', 'vTest');
define('AERIA_URL', '');
define('WPINC', __DIR__);

require_once(__DIR__.'/wp-includes-functions.php');

require_once(__DIR__.'/../classes/AeriaNetwork.php');
require_once(__DIR__.'/../classes/AeriaCache.php');
require_once(__DIR__.'/../classes/AeriaSocial.php');

AeriaCache::setDriver('Redis');
AeriaSocial::init();

print_r( AeriaSocial::getCount(['http://google.it','http://google.com','http://facebook.com']) );