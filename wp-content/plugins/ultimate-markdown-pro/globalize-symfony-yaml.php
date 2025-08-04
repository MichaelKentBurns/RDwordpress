<?php
/**
 * Globalize Symfony YAML.
 *
 * @package ultimate-markdown-pro
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

global $symfony_yaml;
$symfony_yaml = new Yaml();