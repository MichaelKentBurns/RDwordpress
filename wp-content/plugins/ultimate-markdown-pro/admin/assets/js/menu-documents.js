/**
 * This file is used to initialize Select2 and render the Markdown content in the editor render section.
 *
 * @package ultimate-markdown-pro
 */

jQuery( document ).ready(
	function ($) {

		'use strict';

		// Init select2.
		$( '#category_id' ).select2();

		// ---- VARIABLES ----
		const parserNiceName = get_parser_nice_name();
		let usePhpParser = 'marked' === window.DAEXTULMAP_PARAMETERS.livePreviewMarkdownParser ? false : true;
		let autoRefreshEnabled = window.DAEXTULMAP_PARAMETERS.livePreviewPhpAutoRefresh;
		let debounceTimer = null;
		// 2 seconds after last keystroke.
		const debounceDelay = window.DAEXTULMAP_PARAMETERS.livePreviewPhpDebounceDelay;

		// Show refresh button only if PHP parser is active.
		if (usePhpParser) {
			$( '#refresh-php-preview' ).show();
			$( '#parser-indicator' ).text( parserNiceName );
		} else {
			$( '#refresh-php-preview' ).hide();
			$( '#parser-indicator' ).text( parserNiceName );
		}

		// Initial render on page load.
		const content = $( '#content' );
		if (content.length > 0) {
			const textareaValue = content.val();
			renderPreview( textareaValue );
		}

		// Handle textarea input.
		$( '#content' ).on( 'input', function () {
			const textareaValue = $( this ).val();

			if ( ! usePhpParser) {
				renderMarkdown( textareaValue );
			} else {
				if (autoRefreshEnabled) {
					handleDebouncedPhpPreview( textareaValue );
				}
			}
		} );

		// Handle manual refresh button click.
		$( '#refresh-php-preview' ).on( 'click', function () {

			// Prevet the default action of the button.
			event.preventDefault();

			const textareaValue = $( '#content' ).val();
			renderPhpMarkdown( textareaValue );
		} );

		// Render the Markdown text in the editor render.
		function renderMarkdown(textareaValue) {

			'use strict';

			/**
			 * Remove the YAML data available at the beginning of the document (Front
			 * Matter)
			 */
			const textWithoutFrontMatter = textareaValue.replace( /^\s*-{3}\r?\n.*?\r?\n-{3}/ms, '');

			// Generate the HTML from the Markdown content.
			const content = marked( textWithoutFrontMatter );

			// Sanitize the generated HTML.
			let cleanContent = DOMPurify.sanitize(
				content,
				{USE_PROFILES: {html: true}}
			);

			// Add the HTML in the DOM.
			$( '#editor-render' ).html( cleanContent );

		}

		// ---- CALL PHP PARSER VIA REST API ----

		function renderPhpMarkdown(textareaValue) {
			$( '#editor-render' ).html( '<div class="daextulmap-markdown-editor__loading-screen"' +
				'<div class="daextulmap-markdown-editor__loading-screen-wrapper">' +
				'              <div class="daextulmap-markdown-editor__loading-screen-content">' +
				'                  <div class="daextulmap-markdown-editor__loading-screen-spinner-container">' +
				'                      <div class="daextulmap-markdown-editor__loading-screen-spinner"></div>' +
				'                  </div>' +
				'                  <div class="daextulmap-markdown-editor__loading-screen-text">Loading preview...</div>' +
				'              </div>' +
				'          </div>' +
				'</div>' );

			wp.apiFetch( {
				path: '/ultimate-markdown-pro/v1/parse-markdown',
				method: 'POST',
				data: {
					markdown_content: textareaValue
				},
			} ).then( response => {

				let cleanContent = DOMPurify.sanitize(
					response,
					{USE_PROFILES: {html: true}}
				);
				$( '#editor-render' ).html( cleanContent );
				$( '#parser-indicator' ).text( parserNiceName );

			} );

		}

		// ---- DEBOUNCED HANDLER ----

		function handleDebouncedPhpPreview(textareaValue) {
			if (debounceTimer) {
				clearTimeout( debounceTimer );
			}

			debounceTimer = setTimeout( function () {
				renderPhpMarkdown( textareaValue );
			}, debounceDelay );
		}

		// ---- GET PARSER NICE NAME ----

		function get_parser_nice_name() {
			'use strict';

			const parser = window.DAEXTULMAP_PARAMETERS.livePreviewMarkdownParser;

			switch( parser ) {
				case 'commonmark':
					return 'CommonMark (PHP)';
				case 'commonmark_github':
					return 'CommonMark GitHub (PHP)';
				case 'parsedown':
					return 'Parsedown (PHP)';
				case 'parsedown_extra':
					return 'Parsedown Extra (PHP)';
				case 'cebe_markdown':
					return 'Cebe Markdown (PHP)';
				case 'cebe_markdown_github_flavored':
					return 'Cebe Markdown Github (PHP)';
				case 'cebe_markdown_extra':
					return 'Cebe Markdown Extra (PHP)';
				case 'marked':
					return 'Marked (JavaScript)';
			}
		}

		// --- MAIN RENDER LOGIC ----

		function renderPreview(textareaValue) {
			if ( ! usePhpParser) {
				renderMarkdown( textareaValue );
			} else {
				renderPhpMarkdown( textareaValue );
			}
		}

	}
);