<?php
/**
 * @package  BanaagDiwaSubmissions
 */
/*
Plugin Name: Banaag Diwa Submissions
Plugin URI: https://github.com/sonroyaalmerol/atenews-banaag-diwa-wp
Description: A plugin developed for submission uploads for Atenews Banaag Diwa based on WP REST API.
Version: 1.0.0
Author: Son Roy Almerol
Author URI: https://github.com/sonroyaalmerol
License: MIT
Text Domain: banaag-diwa-submissions
*/

// If this file is called firectly, abort!!!
defined('ABSPATH') or die('Error: Unauthorized action.');

// Require once the Composer Autoload
if (file_exists( dirname( __FILE__ ) . '/vendor/autoload.php')) {
	require_once dirname(__FILE__) . '/vendor/autoload.php';
}

// Define CONSTANTS
define('PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PLUGIN_URL', plugin_dir_url(__FILE__));
define('PLUGIN', plugin_basename(__FILE__));


register_activation_hook(__FILE__, array('Inc\Base\Activate', 'activate'));
register_deactivation_hook(__FILE__, array('Inc\Base\Deactivate', 'deactivate'));

/**
 * Initialize all the core classes of the plugin
 */

if (class_exists('Inc\\Init')) {
	Inc\Init::register_services();
}