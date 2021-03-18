<?php
/**
 * The main file of the <%= pkg.title %> plugin
 *
 * @package sdjpcrm
 * @version <%= pkg.version %>
 *
 * Plugin Name: <%= pkg.title %>
 * Plugin URI: <%= pkg.pluginUrl %>
 * Description: <%= pkg.description %>
 * Author: <%= pkg.author %>
 * Author URI: <%= pkg.authorUrl %>
 * Version: <%= pkg.version %>
 * Text Domain: sdjpcrm
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'SDJPCRM_SLUG' ) ) {
	define( 'SDJPCRM_SLUG', '<%= pkg.slug %>' );
}

if ( ! defined( 'SDJPCRM_VERSION' ) ) {
	define( 'SDJPCRM_VERSION', '<%= pkg.version %>' );
}

if ( ! defined( 'SDJPCRM_URL' ) ) {
	define( 'SDJPCRM_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'SDJPCRM_PATH' ) ) {
	define( 'SDJPCRM_PATH', plugin_dir_path( __FILE__ ) );
}

// Load the autoloader.
require SDJPCRM_PATH . 'vendor/autoload.php';

// Load the `wp_sdjpcrm()` entry point function.
require SDJPCRM_PATH . 'inc/functions.php';

if ( wp_get_environment_type() === 'development' ) {
	require SDJPCRM_PATH . 'inc/test.php';
}

// Initialize the plugin.
call_user_func( 'WpMunich\sdjpcrm\wp_sdjpcrm' );
