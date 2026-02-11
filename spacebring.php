<?php

use RSA\Spacebring\Plugin;

/**
 * Plugin Name: Spacebring
 * Plugin URI: https://github.com/ryansallen98/spacebring-wp-plugin
 * Description: A WordPress plugin for Spacebring API integration
 * Version: 1.0.0
 * Author: Ryan Allen
 * Author URI: https://github.com/ryansallen98
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: spacebring
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

define('SPACEBRING_PATH', plugin_dir_path(__FILE__));

add_action('plugins_loaded', function () {
    Plugin::init();
});