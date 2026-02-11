<?php

use RSA\Spacebring\Plugin;

/**
 * Plugin Name: Spacebring
 * Plugin URI: https://github.com/ryansallen98/spacebring-wordpress
 * Description: A WordPress plugin for Spacebring API integration
 * Version: 1.1.1
 * Author: Ryan Allen
 * Author URI: https://github.com/ryansallen98
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
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