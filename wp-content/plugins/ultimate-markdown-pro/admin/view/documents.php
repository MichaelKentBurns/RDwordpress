<?php
/**
 * The file used to display the "Documents" menu in the admin area.
 *
 * @package ultimate-markdown-pro
 */

$this->menu_elements->capability = get_option( $this->shared->get( 'slug' ) . '_documents_menu_required_capability' );
$this->menu_elements->context    = 'crud';
$this->menu_elements->display_menu_content();