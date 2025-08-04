<?php
/**
 * Uninstall plugin.
 *
 * @package ultimate-markdown-pro
 */

// Exit if this file is called outside WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}

require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextulmap-shared.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextulmap-admin.php';

// Delete options and tables.
Daextulmap_Admin::un_delete();
