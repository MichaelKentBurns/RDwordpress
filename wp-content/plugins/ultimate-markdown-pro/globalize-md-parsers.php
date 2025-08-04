<?php
/**
 * Globalize Markdown parsers.
 *
 * @package ultimate-markdown-pro
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\GithubFlavoredMarkdownConverter;

if ( DAEXTULMAP_PHP_VERSION > 50300 ) {

	require __DIR__ . '/vendor/autoload.php';

	// Parsedown.
	global $daextulmap_parsedown;
	global $daextulmap_parsedown_extra;
	$daextulmap_parsedown       = new Parsedown();
	$daextulmap_parsedown_extra = new ParsedownExtra();

}

if ( DAEXTULMAP_PHP_VERSION > 50400 ) {

	// Cebe markdown.
	global $daextulmap_cebe_markdown;
	global $daextulmap_cebe_markdown_github_flavored;
	global $daextulmap_cebe_markdown_extra;
	$daextulmap_cebe_markdown                 = new \cebe\markdown\Markdown();
	$daextulmap_cebe_markdown_github_flavored = new \cebe\markdown\GithubMarkdown();
	$daextulmap_cebe_markdown_extra           = new \cebe\markdown\MarkdownExtra();

}

// Check PHP version and mbstring extension.
if ( version_compare( PHP_VERSION, '7.4', '>=' ) && extension_loaded( 'mbstring' ) ) {
	// Autoload dependencies.
	require __DIR__ . '/vendor/autoload.php';

	// Instantiate the Markdown parsers and store them in global variables.
	global $commonmark_converter, $github_flavored_converter;

	$commonmark_converter      = new CommonMarkConverter();
	$github_flavored_converter = new GithubFlavoredMarkdownConverter();
}
