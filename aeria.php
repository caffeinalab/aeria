<?php
/**
 * Aeria.
 *
 * @author      Caffeina
 * @copyright   2019 Caffeina
 * @license     MIT
 *
 * @wordpress-plugin
 * Plugin Name: Aeria
 * Plugin URI:  https://github.com/caffeinalab/aeria
 * Description: Aeria is a modular, lightweight, fast WordPress Application development kit.
 * Version:     3.2.8
 * Author:      Caffeina
 * Author URI:  https://caffeina.com
 * Text Domain: aeria
 * License:     MIT
 */
defined('ABSPATH') or die('No script kiddies please!');

require_once __DIR__.'/vendor/autoload.php';

add_action(
    'init',
    function () {
        $aeria = aeria();
        do_action('aeria_init');
        $aeria->bootstrap();
    }
);
