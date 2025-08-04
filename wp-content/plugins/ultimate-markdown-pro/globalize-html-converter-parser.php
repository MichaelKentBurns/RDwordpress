<?php
/**
 * Globalize the HTMLToMarkdown parser.
 *
 * @package ultimate_markdown_pro
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

require __DIR__ . '/vendor/autoload.php';

use League\HTMLToMarkdown\HtmlConverter;

global $daextulmap_converter;
$daextulmap_converter = new HtmlConverter();
