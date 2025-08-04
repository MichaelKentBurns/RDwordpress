<?php
/**
 * Globalize Front YAML parser.
 *
 * @package ultimate-markdown-pro
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

require __DIR__ . '/vendor/autoload.php';

global $front_yaml_parser;
$front_yaml_parser = new Mni\FrontYAML\Parser();
