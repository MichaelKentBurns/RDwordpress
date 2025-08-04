<?php
/**
 * The file used to display the "Tools" menu in the admin area.
 *
 * @package ultimate-markdown-pro
 */

$this->menu_elements->capability = get_option( $this->shared->get( 'slug' ) . '_tools_menu_required_capability' );
$this->menu_elements->context = null;
$this->menu_elements->display_menu_content();
