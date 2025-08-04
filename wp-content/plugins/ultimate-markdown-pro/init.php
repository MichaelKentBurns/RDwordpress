<?php
/**
 * Plugin Name: Ultimate Markdown Pro
 * Description: A set of tools that helps you work with the Markdown language.
 * Version: 1.22
 * Author: DAEXT
 * Author URI: https://daext.com
 * Text Domain: ultimate-markdown-pro
 * License: GPLv3
 *
 * @package ultimate-markdown-pro
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// Set constants.
define( 'DAEXTULMAP_EDITION', 'PRO' );

const DAEXTULMAP_PLUGIN_UPDATE_CHECKER_SETTINGS = array(
	'slug'                          => 'ultimate-markdown-pro',
	'prefix'                        => 'daextulmap',
	'wp_plugin_update_info_api_url' => 'https://daext.com/wp-json/daext-commerce/v1/wp-plugin-update-info/',
);

// Save the PHP version in a format that allows a numeric comparison.
if ( ! defined( 'DAEXTULMAP_PHP_VERSION' ) ) {
	$version = explode( '.', PHP_VERSION );
	define( 'DAEXTULMAP_PHP_VERSION', ( $version[0] * 10000 + $version[1] * 100 + $version[2] ) );
}

// Rest API.
require_once plugin_dir_path( __FILE__ ) . 'rest/class-daextulmap-rest.php';
add_action( 'plugins_loaded', array( 'Daextulmap_Rest', 'get_instance' ) );

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Register the update checker callbacks on filters.
 *
 * @return void
 */
function daextulmap_register_update_checker_callbacks_on_filters() {

	$plugin_update_checker = new PluginUpdateChecker( DAEXTULMAP_PLUGIN_UPDATE_CHECKER_SETTINGS );
	$plugin_update_checker->register_callbacks_on_filters();

}

add_action( 'plugins_loaded', 'daextulmap_register_update_checker_callbacks_on_filters' );

// Class shared across public and admin.
require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextulmap-shared.php';

// Perform the Gutenberg related activities only if Gutenberg is present.
if ( function_exists( 'register_block_type' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'blocks/src/init.php';
}

// Admin.
if ( is_admin() ) {

	require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextulmap-admin.php';

	// If this is not an AJAX request, create a new singleton instance of the admin class.
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		add_action( 'plugins_loaded', array( 'Daextulmap_Admin', 'get_instance' ) );
	}

	// Activate the plugin using only the class static methods.
	register_activation_hook( __FILE__, array( 'Daextulmap_Admin', 'ac_activate' ) );

	// Update the plugin db tables and options if they are not up-to-date.
	Daextulmap_Admin::ac_create_database_tables();
	Daextulmap_Admin::ac_initialize_options();

}

// Ajax.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	// Admin.
	require_once plugin_dir_path( __FILE__ ) . 'class-daextulmap-ajax.php';
	add_action( 'plugins_loaded', array( 'Daextulmap_Ajax', 'get_instance' ) );

}

/**
 * Load the plugin text domain for translation.
 *
 * @return void
 */
function daextulmap_load_plugin_textdomain() {
	load_plugin_textdomain( 'ultimate-markdown-pro', false, 'ultimate-markdown-pro/lang/' );
}

add_action( 'init', 'daextulmap_load_plugin_textdomain' );
