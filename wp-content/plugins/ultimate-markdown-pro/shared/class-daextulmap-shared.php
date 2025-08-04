<?php
/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 *
 * @package ultimate-markdown-pro
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 */
class Daextulmap_Shared {

	/**
	 * The singleton instance of the class.
	 *
	 * @var Daextulmap_Shared
	 */
	protected static $instance = null;

	/**
	 * The data of the plugin.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 */
	private function __construct() {

		$this->data['slug'] = 'daextulmap';
		$this->data['ver']  = '1.22';
		$this->data['dir']  = substr( plugin_dir_path( __FILE__ ), 0, - 7 );
		$this->data['url']  = substr( plugin_dir_url( __FILE__ ), 0, - 7 );

		// Here are stored the plugin option with the related default values.
		$this->data['options'] = array(

			// Database version. (not available in the options UI).
			$this->get( 'slug' ) . '_database_version'     => '0',

			// Options version. (not available in the options UI).
			$this->get( 'slug' ) . '_options_version'      => '0',

			// General ------------------------------------------------------------------------------------------------.
			$this->get( 'slug' ) . '_documents_menu_required_capability' => 'edit_posts',
			$this->get( 'slug' ) . '_categories_menu_required_capability' => 'edit_posts',
			$this->get( 'slug' ) . '_tools_menu_required_capability' => 'edit_posts',

			$this->get( 'slug' ) . '_import_post_type'     => 'post',
			$this->get( 'slug' ) . '_rest_api_post_type'     => 'post',
			$this->get( 'slug' ) . '_markdown_parser'      => 'parsedown',
			$this->get( 'slug' ) . '_rest_api_markdown_parser'     => 'parsedown',
			$this->get( 'slug' ) . '_editor_markdown_parser'      => 'marked',
			$this->get( 'slug' ) . '_live_preview_markdown_parser'      => 'marked',
			$this->get( 'slug' ) . '_live_preview_php_auto_refresh'      => '1',
			$this->get( 'slug' ) . '_live_preview_php_debounce_delay'      => '1000',
			$this->get( 'slug' ) . '_cebe_markdown_html5'  => '0',
			$this->get( 'slug' ) . '_cebe_markdown_keep_list_start_number' => '0',
			$this->get( 'slug' ) . '_cebe_markdown_enable_new_lines' => '0',

			$this->get( 'slug' ) . '_rest_api_authentication_read' => '0',
			$this->get( 'slug' ) . '_rest_api_authentication_create' => '0',
			$this->get( 'slug' ) . '_rest_api_key'         => bin2hex( openssl_random_pseudo_bytes( 128 ) ),
			$this->get( 'slug' ) . '_rest_api_import_type' => '0',
			$this->get( 'slug' ) . '_rest_api_required_capability' => 'edit_posts',

			$this->get( 'slug' ) . '_default_category'     => '0',
			$this->get( 'slug' ) . '_exported_files_extension' => 'md',
			$this->get( 'slug' ) . '_exported_files_include_front_matter' => '0',
			$this->get( 'slug' ) . '_exported_files_include_empty_front_matter_fields' => '0',

			$this->get( 'slug' ) . '_upload_referenced_images_import_menu' => '0',
			$this->get( 'slug' ) . '_upload_referenced_images_import_classic_menu' => '0',
			$this->get( 'slug' ) . '_upload_referenced_images_import_markdown_ess' => '0',
			$this->get( 'slug' ) . '_upload_referenced_images_load_markdown_ess' => '0',
			$this->get( 'slug' ) . '_upload_referenced_images_submit_markdown_ess' => '0',
			$this->get( 'slug' ) . '_upload_referenced_images_import_classic_rest_api' => '0',
			$this->get( 'slug' ) . '_upload_referenced_images_import_documents_rest_api' => '0',

			$this->get( 'slug' ) . '_license_provider' => 'daext_com',
			$this->get( 'slug' ) . '_license_key' => '',

		);
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return Daextulmap_Shared|self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieve data.
	 *
	 * @param string $index The index of the data to retrieve.
	 *
	 * @return mixed
	 */
	public function get( $index ) {
		return $this->data[ $index ];
	}

	/**
	 * Given an array with the information related to the uploaded Markdown file
	 *  (markdown|mdown|mkdn|md|mkd|mdwn|mdtxt|mdtext|text|txt) the content of the markdown file is returned.
	 *
	 * @param array $file_info An array with the information related to the uploaded file.
	 *
	 * @return false|string
	 */
	public function get_markdown_file_content( $file_info ) {

		$file_name = $file_info['name'];

		// Verify the extension.
		if ( preg_match( '/^.+\.(markdown|mdown|mkdn|md|mkd|mdwn|mdtxt|mdtext|text|txt)$/', $file_name, $matches ) === 1 ) {

			global $wp_filesystem;
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();

			// Get the file content.
			$file_content = $wp_filesystem->get_contents( $file_info['tmp_name'] );

			return $file_content;

		} else {

			return false;

		}
	}

	/**
	 * Sanitize the data of an uploaded file.
	 *
	 * @param array $file The array with the data of the uploaded file.
	 *
	 * @return array
	 */
	public function sanitize_uploaded_file( $file ) {

		return array(
			'name'     => sanitize_text_field( $file['name'] ),
			'type'     => $file['tmp_name'],
			'tmp_name' => $file['tmp_name'],
			'error'    => intval( $file['error'], 10 ),
			'size'     => intval( $file['size'], 10 ),
		);
	}

	/**
	 * If multiple documents have the same name rename the documents with the duplicate name. This is
	 * necessary to ovoid overwriting files with the same names.
	 *
	 * @param array $document_a The array with the documents.
	 *
	 * @return mixed The array with the documents with the duplicate names renamed.
	 */
	public function rename_duplicate_names_in_document( $document_a ) {

		foreach ( $document_a as $key1 => $document ) {

			// This is the suffix added at the end of the file name when duplicates are present.
			$counter = 1;

			do {

				$document_copy_a = $document_a;
				$matches         = 0;
				$copy_key        = null;

				/**
				 * Check if $document['title'] exists more than one time in the array. If it existing more than
				 * one time get the key of last occurence where it exists.
				 */
				foreach ( $document_copy_a as $key2 => $document_copy ) {
					if ( $document['title'] === $document_copy['title'] ) {
						$copy_key = $key2;
						++$matches;
					}
				}

				/**
				 * Use the key of the duplicate name found in the last step to modify the document title of the
				 * duplicate. This step is performed only if more that one match is found. If there is only one
				 * match this means that there are no duplicates.
				 */
				if ( $matches > 1 ) {
					$document_a[ $copy_key ]['title'] .= $counter;
					++$counter;
				}
			} while ( $matches > 1 );

		}

		return $document_a;
	}

	/**
	 * Get the document/post title from the file name.
	 *
	 * @param string $filename The name of the file.
	 *
	 * @return string The title of the document/post.
	 */
	public function get_title_from_file_name( $filename ) {

		// Remove the markdown extension from the filename.
		preg_match(
			'/.*\.(markdown|mdown|mkdn|md|mkd|mdwn|mdtxt|mdtext|text|txt)$/',
			$filename,
			$matches,
			PREG_OFFSET_CAPTURE
		);

		if(isset($matches[1][1])){
			$title = substr( $filename, 0, $matches[1][1] - 1 );
		}else{
			$title = $filename;
		}

		return $title;

	}

	/**
	 * Converts the provided Markdown content to HTML. Note that the type of conversion depends on the options defined
	 * in the back-end.
	 *
	 * @param string $markdown_content The Markdown content to convert to HTML.
	 * @param string $context The context in which the conversion is performed. It can be "bulk_import", "editor", or
	 * "live_preview". Note that the context is used to determine which Markdown parser to use for the conversion.
	 *
	 * @return mixed The HTML generated from the provided Markdown text.
	 */
	public function convert_markdown_to_html( $markdown_content, $context ) {

		switch ( $context ) {

			case 'bulk_import':
				$markdown_parser = get_option( $this->get( 'slug' ) . '_markdown_parser' );
				break;

			case 'rest_api':
				$markdown_parser = get_option( $this->get( 'slug' ) . '_rest_api_markdown_parser' );
				break;

			case 'editor':
				$markdown_parser = get_option( $this->get( 'slug' ) . '_editor_markdown_parser' );
				break;

			case 'live_preview':
				$markdown_parser = get_option( $this->get( 'slug' ) . '_live_preview_markdown_parser' );
				break;

		}

		/**
		 * If the parser is "marked" return an empty string. This is because the Marked.js parser is JavaScript-based
		 * and, as a consequence, used on the front-end.
		 */
		if ( 'marked' === $markdown_parser ) {
			return '';
		}

		// Instances of the Markdown parsers are created and saved as global variables.
		require_once $this->get( 'dir' ) . 'globalize-md-parsers.php';

		// Convert HTML to Markdown using the selected parser and options.
		switch ( $markdown_parser ) {

			case 'commonmark':
			case 'commonmark_github':

				// Get the result object.
				if ( 'commonmark' === $markdown_parser ) {
					global $commonmark_converter;
					$result_obj = $commonmark_converter->convert( $markdown_content );
				} else {
					global $github_flavored_converter;
					$result_obj = $github_flavored_converter->convert( $markdown_content );
				}

				// Get the HTML content.
				$html_content = $result_obj->getContent();

				break;

			case 'parsedown':
				global $daextulmap_parsedown;
				$html_content = $daextulmap_parsedown->text( $markdown_content );

				break;

			case 'parsedown_extra':
				global $daextulmap_parsedown_extra;
				$html_content = $daextulmap_parsedown_extra->text( $markdown_content );

				break;

			case 'cebe_markdown':
				global $daextulmap_cebe_markdown;

				if ( intval( get_option( $this->get( 'slug' ) . '_cebe_markdown_html5' ), 10 ) === true ) {
					$daextulmap_cebe_markdown->html5 = true;
				}

				if ( intval(
					get_option( $this->get( 'slug' ) . '_cebe_markdown_keep_list_start_number' ),
					10
				) === true ) {
					$daextulmap_cebe_markdown->keepListStartNumber = true;
				}

				$html_content = $daextulmap_cebe_markdown->parse( $markdown_content );

				break;

			case 'cebe_markdown_github_flavored':
				global $daextulmap_cebe_markdown_github_flavored;

				if ( intval( get_option( $this->get( 'slug' ) . '_cebe_markdown_html5' ), 10 ) === true ) {
					$daextulmap_cebe_markdown_github_flavored->html5 = true;
				}

				if ( intval(
					get_option( $this->get( 'slug' ) . '_cebe_markdown_keep_list_start_number' ),
					10
				) === true ) {
					$daextulmap_cebe_markdown_github_flavored->keepListStartNumber = true;
				}

				if ( intval(
					get_option( $this->get( 'slug' ) . '_cebe_markdown_enable_new_lines' ),
					10
				) === true ) {
					$daextulmap_cebe_markdown_github_flavored->enableNewlines = true;
				}

				$html_content = $daextulmap_cebe_markdown_github_flavored->parse( $markdown_content );

				break;

			case 'cebe_markdown_extra':
				global $daextulmap_cebe_markdown_extra;

				if ( true === intval( get_option( $this->get( 'slug' ) . '_cebe_markdown_html5' ), 10 ) ) {
					$daextulmap_cebe_markdown_extra->html5 = true;
				}

				if ( intval(
					get_option( $this->get( 'slug' ) . '_cebe_markdown_keep_list_start_number' ),
					10
				) === true ) {
					$daextulmap_cebe_markdown_extra->keepListStartNumber = true;
				}

				$html_content = $daextulmap_cebe_markdown_extra->parse( $markdown_content );

				break;

		}

		return $html_content;
	}

	/**
	 * Given the category ID the category name is returned.
	 *
	 * @param int $category_id The category ID.
	 *
	 * @return string|null
	 */
	public function get_category_name( $category_id ) {

		if ( intval( $category_id, 10 ) === 0 ) {
			return __( 'None', 'ultimate-markdown-pro' );
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$category_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextulmap_category WHERE category_id = %d ", $category_id )
		);

		return $category_obj->name;
	}

	/**
	 * Returns true if one or more documents are using the specified category.
	 *
	 * @param int $category_id The category ID.
	 *
	 * @return bool
	 */
	public function category_is_used( $category_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daextulmap_document WHERE category_id = %d", $category_id )
		);

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Removes the YAML content from the provided strings. Note that only YAML content placed at the beginning of the
	 * string is removed. This is where Frontmatter is added.
	 *
	 * @param string $str The string from which the YAML content is removed.
	 *
	 * @return array|string|string[]|null
	 */
	public function remove_yaml( $str ) {

		return preg_replace( '/^\s*-{3}\R.+?\R-{3}/ms', '', $str );
	}

	/**
	 * Whether a user with this user ID exists.
	 *
	 * @param int $user The user ID.
	 *
	 * @return bool
	 */
	public function user_id_exists( $user ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user ) );

		if ( 1 === $count ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Create a post from the provided post title and post content provided in the Markdown syntax.
	 *
	 * @param string $title The post title.
	 * @param string $markdown_content The post content provided in the Markdown syntax.
	 * @param array  $front_matter The front matter of the document.
	 * @param string $context The context in which the post is created. It can be "bulk_import" (the operation performed
	 * using the Tools menu and selecting "Posts" as the destination), or "rest_api".
	 *
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 * See: https://developer.wordpress.org/reference/functions/wp_insert_post/
	 */
	public function create_classic_post( $title, $markdown_content, $front_matter, $context ) {

		// Remove the YAML part from the document.
		$markdown_content = $this->remove_yaml( $markdown_content );

		// Generate HTML from text with the Markdown syntax.
		$html_content = $this->convert_markdown_to_html( $markdown_content, $context );

		// Create the array with the parameters.
		$params = array();

		$params['post_type'] = 'bulk_import' === $context
			? get_option( $this->get( 'slug' ) . '_import_post_type' )
			: get_option( $this->get( 'slug' ) . '_rest_api_post_type' );

		$params['post_content'] = $html_content;
		if ( null !== $title ) {
			$params['post_title'] = $title;
		}
		if ( null !== $front_matter['excerpt'] ) {
			$params['post_excerpt'] = $front_matter['excerpt'];
		}
		if ( null !== $front_matter['categories'] ) {
			$params['post_category'] = $front_matter['categories'];
		}
		if ( null !== $front_matter['tags'] ) {
			$params['tags_input'] = $front_matter['tags'];
		}
		if ( null !== $front_matter['author'] ) {
			$params['post_author'] = $front_matter['author'];
		}
		if ( null !== $front_matter['date'] ) {
			$params['post_date'] = $front_matter['date'];
		}
		if ( null !== $front_matter['status'] ) {
			$params['post_status'] = $front_matter['status'];
		}
		if ( $front_matter['slug'] !== null ) {
			$params['post_name'] = $front_matter['slug'];
		}
		if ( null !== $front_matter['thumbnail'] ) {
			$params['_thumbnail_id'] = $front_matter['thumbnail'];
		}

		// Insert the post into the database.
		$new_post_id = wp_insert_post( $params );

		return $new_post_id;
	}

	/**
	 * Generates the JSON data associated with a Markdown document.
	 *
	 * @param string $title The title of the document.
	 * @param string $document The content of the document in Markdown syntax.
	 * @param array  $front_matter The front matter of the document.
	 *
	 * Note that wp_send_json_error() and wp_send_json_success() call wp_die() internally, so there is no need to
	 * call wp_die() after these functions.
	 */
	public function generate_markdown_document_json( $title, $document, $front_matter ) {

		// Remove the YAML part from the document.
		$markdown_data = $this->remove_yaml( $document );

		// Converts the provided Markdown content to HTML.
		$html_content = $this->convert_markdown_to_html( $markdown_data, 'editor' );

		// If $front_matter includes an error, return a JSON error response.
		if ( isset( $front_matter['error'] ) && ! empty( $front_matter['error'] ) ) {
			wp_send_json_error(
				array(
					'error' => $front_matter['error'],
				)
			);
		}

		// Otherwise, return the data as a success response.
		wp_send_json_success(
			array(
				'content'         => $markdown_data,
				'html_content'    => $html_content,
				'title'           => $title,
				'excerpt'         => $front_matter['excerpt'],
				'categories'      => $front_matter['categories'],
				'tags'            => $front_matter['tags'],
				'author'          => $front_matter['author'],
				'date'            => $front_matter['date'],
				'status'          => $front_matter['status'],
				'slug'            => $front_matter['slug'],
				'thumbnail'       => $front_matter['thumbnail'],
				'thumbnail_alt'   => $front_matter['thumbnail_alt'],
				'thumbnail_title' => $front_matter['thumbnail_title'],
			)
		);
	}


	/**
	 * Upload the referenced images to the media library and update the URLs.
	 *
	 * The updated image URLs will be the ones of the new media library images.
	 *
	 * @param string $markdown The Markdown content.
	 *
	 * @return array|false|mixed|string|string[]
	 */
	public function replace_referenced_images_with_local_images( $markdown ) {

		// Find all the images URLs with a regex.
		$markdown = preg_replace_callback(
			'{!\[([^\]]*)\]\((.*?)\s*(?:"(.*[^"])")?\s*\)}m',
			array( $this, 'preg_replace_callback_1' ),
			$markdown
		);

		return $markdown;
	}

	/**
	 * Callback used in the preg_replace_callback() function available in the
	 * replace_referenced_images_with_local_images() method.
	 *
	 * @param string $prc_match The match found by the preg_replace_callback() function.
	 *
	 * @return string
	 */
	public function preg_replace_callback_1( $prc_match ) {

		/**
		 * $match[0] - The whole string
		 * $match[1] - The image alt text
		 * $match[2] - The image URL
		 * $match[3] - The image title (check if it is set - this value is not always captured)
		 */
		$string      = $prc_match[0];
		$image_alt   = $prc_match[1];
		$image_url   = $prc_match[2];
		$image_title = isset( $prc_match[3] ) ? $prc_match[3] : null;

		// Create an image attachment from an image URL and get the resulting Attachment ID.
		$attachment_id = $this->create_image_attachment_from_image_url( $image_url, $image_alt, $image_title );

		// If the Attachment ID exists get the attachment image URL.
		if ( false !== $attachment_id ) {

			// If everything goes well get the URL associated with the Attachment ID.
			$new_image_url = wp_get_attachment_url( $attachment_id );

		} else {

			// If the Attachment ID is false leave the provided URL.
			$new_image_url = $image_url;

		}

		// Create the title part.
		if ( null !== $image_title ) {
			$image_title_complete = ' "' . $image_title . '"';
		} else {
			$image_title_complete = '';
		}

		return '![' . $image_alt . '](' . $new_image_url . $image_title_complete . ')';
	}


	/**
	 * Create an image attachment from an image URL.
	 *
	 * If the operation is successful the Attachment ID is returned, otherwise false is returned.
	 *
	 * Ref: https://rudrastyh.com/wordpress/how-to-add-images-to-media-library-from-uploaded-files-programmatically.html
	 *
	 * @param string $image_url The URL of the image.
	 * @param string $image_alt The alt text of the image.
	 * @param string $image_title The title of the image.
	 *
	 * @return false|int|WP_Error
	 */
	public function create_image_attachment_from_image_url( $image_url, $image_alt, $image_title ) {

		// It allows us to use download_url() and wp_handle_sideload() functions.
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Download the file to the temp dir.
		$temp_file = download_url( $image_url );

		// Returns false if the file can't be downloaded to the temp dir.
		if ( is_wp_error( $temp_file ) ) {
			return false;
		}

		// Move the temp file into the uploads directory.
		$file     = array(
			'name'     => basename( $image_url ),
			'type'     => mime_content_type( $temp_file ),
			'tmp_name' => $temp_file,
			'size'     => filesize( $temp_file ),
		);
		$sideload = wp_handle_sideload(
			$file,
			array(
				'test_form' => false, // No needs to check 'action' parameter.
			)
		);

		// Returns false if the file can't be uploaded to the uploads directory.
		if ( ! empty( $sideload['error'] ) ) {
			return false;
		}

		// Add the uploaded image into WordPress media library.
		$attachment_id = wp_insert_attachment(
			array(
				'guid'           => $sideload['url'],
				'post_mime_type' => $sideload['type'],
				'post_title'     => isset( $image_title ) ? $image_title : basename( $sideload['file'] ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$sideload['file']
		);

		// If the image alt is set update the attachment with this value.
		if ( isset( $image_alt ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $image_alt );
		}

		// Returns false if the file can't be added to the media library.
		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return false;
		}

		// Update the metadata and regenerate the image sizes.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $sideload['file'] )
		);

		// If everything goes well returns the Attachment ID.
		return $attachment_id;
	}

	/**
	 * Returns an array with the data used by React to initialize the options.
	 *
	 * @return array[]
	 */
	public function menu_options_configuration() {

		// Get the public post types that have a UI.
		$args               = array(
			'public'  => true,
			'show_ui' => true,
		);
		$post_types_with_ui = get_post_types( $args );
		unset( $post_types_with_ui['attachment'] );
		$post_types_select_options = array();
		foreach ( $post_types_with_ui as $post_type ) {
			$post_type_obj               = get_post_type_object( $post_type );
			$post_types_select_options[] = array(
				'value' => $post_type,
				'text'  => $post_type_obj->label,
			);
		}

		// Get the file extension options.
		$file_extension_options = array();
		$file_extensions        = array( 'md', 'markdown', 'mdown', 'mkdn', 'mkd', 'mdwn', 'mdtxt', 'mdtext', 'text', 'txt' );

		foreach ( $file_extensions as $file_extension ) {
			$file_extension_options[] = array(
				'value' => $file_extension,
				'text'  => '.' . $file_extension,
			);
		}

		// Categories. ------------------------------------------------------------------------------------------------.
		$default_category_id_select_options = array(
			array(
				'value' => '0',
				'text'  => __( 'None', 'ultimate-markdown-pro' ),
			),
		);

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$category_a = $wpdb->get_results(
			"SELECT category_id, name FROM {$wpdb->prefix}daextulmap_category ORDER BY category_id DESC",
			ARRAY_A
		);

		foreach ( $category_a as $key => $category ) {
			$default_category_id_select_options[] = array(
				'value' => $category['category_id'],
				'text'  => $category['name'],
			);
		}

		$configuration = array(
			array(
				'title'       => __( 'Conversion', 'ultimate-markdown-pro' ),
				'description' => __( 'Customize the settings for parsing Markdown.', 'ultimate-markdown-pro' ),
				'cards'       => array(
					array(
						'title'   => __( 'Markdown Parser', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'          => 'daextulmap_markdown_parser',
								'label'         => __( 'Bulk Import', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The parser used to perform the Markdown-to-HTML conversion during the import process executed from the "Tools" menu. Note that this conversion applies only when the selected destination is "Posts".',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the Markdown parser used for converting Markdown files into posts during bulk imports.', 'ultimate-markdown-pro' ),
								'selectOptions' => array(
									array(
										'value' => 'cebe_markdown',
										'text'  => __( 'Cebe Markdown (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'cebe_markdown_github_flavored',
										'text'  => __( 'Cebe Markdown Github (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'cebe_markdown_extra',
										'text'  => __( 'Cebe Markdown Extra (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'commonmark',
										'text'  => __( 'CommonMark (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'commonmark_github',
										'text'  => __( 'CommonMark GitHub (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'parsedown',
										'text'  => __( 'Parsedown (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'parsedown_extra',
										'text'  => __( 'Parsedown Extra (PHP)', 'ultimate-markdown-pro' ),
									),
								),
							),
							array(
								'name'          => 'daextulmap_rest_api_markdown_parser',
								'label'         => __( 'REST API', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The Markdown parser used to convert Markdown content to HTML when handling requests sent to the dedicated REST API endpoint.',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the Markdown parser used for processing Markdown documents submitted via the REST API.', 'ultimate-markdown-pro' ),
								'selectOptions' => array(
									array(
										'value' => 'cebe_markdown',
										'text'  => __( 'Cebe Markdown (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'cebe_markdown_github_flavored',
										'text'  => __( 'Cebe Markdown Github (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'cebe_markdown_extra',
										'text'  => __( 'Cebe Markdown Extra (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'commonmark',
										'text'  => __( 'CommonMark (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'commonmark_github',
										'text'  => __( 'CommonMark GitHub (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'parsedown',
										'text'  => __( 'Parsedown (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'parsedown_extra',
										'text'  => __( 'Parsedown Extra (PHP)', 'ultimate-markdown-pro' ),
									),
								),
							),
							array(
								'name'          => 'daextulmap_editor_markdown_parser',
								'label'         => __( 'Editor', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The Markdown parser used for Markdown-to-HTML conversions in the WordPress editor. More specifically, this parser is used by the following Block Editor sidebar features: Import Markdown, Load Markdown, and Submit Markdown.',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the Markdown parser used for conversions in the WordPress editor.', 'ultimate-markdown-pro' ),
								'selectOptions' => array(
									array(
										'value' => 'cebe_markdown',
										'text'  => __( 'Cebe Markdown (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'cebe_markdown_github_flavored',
										'text'  => __( 'Cebe Markdown Github (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'cebe_markdown_extra',
										'text'  => __( 'Cebe Markdown Extra (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'commonmark',
										'text'  => __( 'CommonMark (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'commonmark_github',
										'text'  => __( 'CommonMark GitHub (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'parsedown',
										'text'  => __( 'Parsedown (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'parsedown_extra',
										'text'  => __( 'Parsedown Extra (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'marked',
										'text'  => __( 'Marked (JavaScript)', 'ultimate-markdown-pro' ),
									),
								),
							),
							array(
								'name'          => 'daextulmap_live_preview_markdown_parser',
								'label'         => __( 'Live Preview', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The Markdown parser used to generate the live preview while editing Markdown content in the editor found in the "Documents" menu. For the fastest preview experience and low resource usage (especially important on shared servers), the "Marked" parser is recommended, as other parsers run on the server and require server communication on each update or manual refresh.',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the Markdown parser used for rendering the live preview in the Markdown editor.', 'ultimate-markdown-pro' ),
								'selectOptions' => array(
									array(
										'value' => 'cebe_markdown',
										'text'  => __( 'Cebe Markdown (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'cebe_markdown_github_flavored',
										'text'  => __( 'Cebe Markdown Github (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'cebe_markdown_extra',
										'text'  => __( 'Cebe Markdown Extra (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'commonmark',
										'text'  => __( 'CommonMark (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'commonmark_github',
										'text'  => __( 'CommonMark GitHub (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'parsedown',
										'text'  => __( 'Parsedown (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'parsedown_extra',
										'text'  => __( 'Parsedown Extra (PHP)', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'marked',
										'text'  => __( 'Marked (JavaScript - Recommended for Live Preview)', 'ultimate-markdown-pro' ),
									),
								),
							),
						),
					),
					array(
						'title'   => __( 'Cebe Markdown', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'    => 'daextulmap_cebe_markdown_html5',
								'label'   => __( 'HTML5', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Activate this option to enable HTML5 output instead of HTML4. This feature is applied only if one of the three "Cebe Markdown" parsers is used.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Enable HTML5 output instead of HTML4.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_cebe_markdown_keep_list_start_number',
								'label'   => __( 'List Start', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Activate this option to keep the number in the ordered lists as specified in the Markdown. The default behavior is to always start from 1 and increment by one regardless of the number specified in the Markdown. This feature is applied only if one of the three "Cebe Markdown" parsers is used.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Keep the number in the ordered lists as specified in the Markdown.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_cebe_markdown_enable_new_lines',
								'label'   => __( 'New Lines', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Activate this option to convert all the newlines to "br" tags. By default only the newlines with two preceding spaces are converted to <br/> tags. This feature is applied only if the "Cebe Markdown GitHub" parser is used.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Convert all the newlines to "br" tags.', 'ultimate-markdown-pro' ),
							),
						),
					),
					array(
						'title'   => __( 'Image Upload', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'    => 'daextulmap_upload_referenced_images_import_menu',
								'label'   => __( 'Tools Menu (Documents)', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With this option enabled, the images referenced in the Markdown document will be uploaded to the media library. This change will affect the imports executed with the "Tools" menu and "Documents" as the destination.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Upload the images to the media library when imports with "Documents" as a destination are performed.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_upload_referenced_images_import_classic_menu',
								'label'   => __( 'Tools Menu (Posts)', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With this option enabled, the images referenced in the Markdown document will be uploaded to the media library. This change will affect the imports executed with the "Tools" menu and "Posts" as the destination.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Upload the images to the media library when imports with "Posts" as a destination are performed.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_upload_referenced_images_import_markdown_ess',
								'label'   => __( 'Import Markdown (Editor Sidebar Section)', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With this option enabled, the images referenced in the Markdown document will be uploaded to the media library. This change will affect the imports executed with the "Import Markdown" block editor sidebar section.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Upload the images to the media library when imports are performed from the "Import Markdown" block editor sidebar section.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_upload_referenced_images_load_markdown_ess',
								'label'   => __( 'Load Markdown (Editor Sidebar Section)', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With this option enabled, the images referenced in the Markdown document will be uploaded to the media library. This change will affect the imports executed with the "Load Markdown" block editor sidebar section.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Upload the images to the media library when imports are performed from the "Load Markdown" block editor sidebar section.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_upload_referenced_images_submit_markdown_ess',
								'label'   => __( 'Submit Markdown (Editor Sidebar Section)', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With this option enabled, the images referenced in the Markdown document will be uploaded to the media library. This change will affect the imports executed with the "Submit Markdown" block editor sidebar.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Upload the images to the media library when imports are performed from the "Submit Markdown" block editor sidebar section.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_upload_referenced_images_import_classic_rest_api',
								'label'   => __( 'REST API (Posts)', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With this option enabled, the images referenced in the Markdown document will be uploaded to the media library. This change will affect the imports executed with the "Import Documents" REST API endpoint when the "Destination" option is set to "Posts".',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Upload the images to the media library when documents are sent using the REST API and the REST API destination is set to "Posts".', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_upload_referenced_images_import_documents_rest_api',
								'label'   => __( 'REST API (Documents)', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With this option enabled, the images referenced in the Markdown document will be uploaded to the media library. This change will affect the imports executed with the "Import Documents" REST API endpoint when the "Destination" option is set to "Documents".',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Upload the images to the media library when documents are sent using the REST API and the REST API destination is set to "Documents".', 'ultimate-markdown-pro' ),
							),
						),
					),
				),
			),
			array(
				'title'       => __( 'REST API', 'ultimate-markdown-pro' ),
				'description' => __( 'Configure the behavior of the REST API endpoints.', 'ultimate-markdown-pro' ),
				'cards'       => array(
					array(
						'title'   => __( 'Authentication', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'          => 'daextulmap_rest_api_authentication_read',
								'label'         => __( 'Authentication (Read)', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option determines the type of authentication required to get access on the REST API endpoints of type "Read".',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the type of authentication required to get access on the REST API endpoints of type "Create".', 'ultimate-markdown-pro' ),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Cookies', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'REST API Key', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => '2',
										'text'  => __( 'None', 'ultimate-markdown-pro' ),
									),
								),
							),
							array(
								'name'          => 'daextulmap_rest_api_authentication_create',
								'label'         => __( 'Authentication (Create)', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option determines the type of authentication required to get access on the REST API endpoints of type "Create".',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the type of authentication required to get access on the REST API endpoints of type "Create".', 'ultimate-markdown-pro' ),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Cookies', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'REST API Key', 'ultimate-markdown-pro' ),
									),
								),
							),
							array(
								'name'    => 'daextulmap_rest_api_key',
								'label'   => __( 'Key', 'ultimate-markdown-pro' ),
								'type'    => 'text',
								'tooltip' => __(
									'The REST API key. This option is used only if the "Authentication (Read)" option is set to "REST API Key".',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Set the REST API key.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_rest_api_required_capability',
								'label'   => __( 'Required Capability', 'ultimate-markdown-pro' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the REST API.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'The capability required to get access on the REST API.', 'ultimate-markdown-pro' ),
							),
						),
					),
					array(
						'title'   => __( 'Processing', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'          => 'daextulmap_rest_api_import_type',
								'label'         => __( 'Destination', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The destination of the items created by the import process performed with the REST API import endpoint.',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the destination for posts created via the REST API.', 'ultimate-markdown-pro' ),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Documents', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'Posts', 'ultimate-markdown-pro' ),
									),
								),
							),
							array(
								'name'          => 'daextulmap_rest_api_post_type',
								'label'         => __( 'Post Type', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The post type used for posts created via the dedicated REST API endpoint. This option is only used if the destination of the import process is "Posts".',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the post type for posts created via the REST API.', 'ultimate-markdown-pro' ),
								'selectOptions' => $post_types_select_options,
							),
						),
					),
				),
			),
			array(
				'title'       => __( 'Advanced', 'ultimate-markdown-pro' ),
				'description' => __( 'Manage advanced plugin settings.', 'ultimate-markdown-pro' ),
				'cards'       => array(
					array(
						'title'   => __( 'Import', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'          => 'daextulmap_import_post_type',
								'label'         => __( 'Post Type', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The post type to which the plugin should assign the posts generated with the "Tools" menu. This option is only used if the destination of the import process is "Posts."',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the post type of the imported documents.', 'ultimate-markdown-pro' ),
								'selectOptions' => $post_types_select_options,
							),
							array(
								'name'          => 'daextulmap_default_category',
								'label'         => __( 'Category', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option determines the category of the imported documents, the category of the documents submitted with the plugin REST API, and the default value of the "Category" field available in the "Documents" menu.',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the category of the imported documents.', 'ultimate-markdown-pro' ),
								'selectOptions' => $default_category_id_select_options,
							),
						),
					),
					array(
						'title'   => __( 'Export', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'          => 'daextulmap_exported_files_extension',
								'label'         => __( 'File Extension', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option determines the file extension of the files exported using the "Export Markdown" editor sidebar section and the file extension of the files included in the compressed archives generated with the "Tools" menu.',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select the extension of the exported files.', 'ultimate-markdown-pro' ),
								'selectOptions' => $file_extension_options,
							),
							array(
								'name'          => 'daextulmap_exported_files_include_front_matter',
								'label'         => __( 'Add Front Matter', 'ultimate-markdown-pro' ),
								'type'          => 'toggle',
								'tooltip'       => __(
									'Adds a YAML front matter section to each Markdown file generated via the "Export Markdown" post editor sidebar or in bulk via the Export section of the Tools menu (when the "Posts" source is selected). This section is automatically filled with post metadata such as the title, excerpt, slug, and publication date.',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Include a front matter block at the beginning of exported files.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'          => 'daextulmap_exported_files_include_empty_front_matter_fields',
								'label'         => __( 'Include Empty Front Matter Fields', 'ultimate-markdown-pro' ),
								'type'          => 'toggle',
								'tooltip'       => __(
									'When enabled, front matter fields with empty values will be included in the YAML front matter as empty strings or empty arrays. When disabled, these empty fields will be omitted entirely. Use this option to match the requirements of your content processing workflow.',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Choose whether to include empty fields in the front matter section.', 'ultimate-markdown-pro' ),
							),
						),
					),
					array(
						'title'   => __( 'Capabilities', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'    => 'daextulmap_documents_menu_required_capability',
								'label'   => __( 'Documents Menu', 'ultimate-markdown-pro' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Documents" menu.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'The capability required to access the "Documents" menu.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_categories_menu_required_capability',
								'label'   => __( 'Categories Menu', 'ultimate-markdown-pro' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Categories" menu.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'The capability required to access the "Categories" menu.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_tools_menu_required_capability',
								'label'   => __( 'Tools Menu', 'ultimate-markdown-pro' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Tools" menu.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'The capability required to access the "Tools" menu.', 'ultimate-markdown-pro' ),
							),
						),
					),
					array(
						'title'   => __( 'Live Preview', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'    => 'daextulmap_live_preview_php_auto_refresh',
								'label'   => __( 'Auto Refresh for PHP Live Preview', 'ultimate-markdown-pro' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'When enabled, the plugin automatically updates the live preview while typing. If disabled, you must manually refresh the preview using the dedicated button. Disabling this option can reduce server load on shared or slow hosting environments. This setting applies only when a PHP-based Markdown parser is selected.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Enables the automatic refresh of the PHP-rendered live preview when editing Markdown in the Documents editor.', 'ultimate-markdown-pro' ),
							),
							array(
								'name'    => 'daextulmap_live_preview_php_debounce_delay',
								'label'   => __( 'Debounce Delay for PHP Live Preview', 'ultimate-markdown-pro' ),
								'type'    => 'range',
								'tooltip' => __(
									'This value controls how long the plugin waits after you stop typing before sending the Markdown content to the PHP parser via REST API to update the live preview. Higher values reduce the number of server requests but may make the preview feel less responsive. This setting applies only when a PHP-based Markdown parser is selected. Example: A value of 1000 means the preview refreshes one second after typing stops.
',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Defines the delay, in milliseconds, between typing changes and the automatic refresh of the PHP-rendered live preview in the Documents editor.', 'ultimate-markdown-pro' ),
								'rangeMin'  => 500,
								'rangeMax'  => 3000,
								'rangeStep' => 100,
							),
						),
					),
				),
			),
			array(
				'title'       => __( 'License', 'ultimate-markdown-pro' ),
				'description' => __( 'Configure your license for seamless updates and support.', 'ultimate-markdown-pro' ),
				'cards'       => array(
					array(
						'title'   => __( 'License Management', 'ultimate-markdown-pro' ),
						'options' => array(
							array(
								'name'          => 'daextulmap_license_provider',
								'label'         => __( 'Provider', 'ultimate-markdown-pro' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Use this option to indicate where you purchased your license. Choosing the correct license provider ensures that your license is properly verified on our system.',
									'ultimate-markdown-pro'
								),
								'help'          => __( 'Select your license provider.', 'ultimate-markdown-pro' ),
								'selectOptions' => array(
									array(
										'value' => 'daext_com',
										'text'  => __( 'DAEXT.COM', 'ultimate-markdown-pro' ),
									),
									array(
										'value' => 'envato_market',
										'text'  => __( 'Envato Market', 'ultimate-markdown-pro' ),
									),
								),
							),
							array(
								'name'    => 'daextulmap_license_key',
								'label'   => __( 'Key', 'ultimate-markdown-pro' ),
								'type'    => 'text',
								'tooltip' => __(
									'For licenses purchased on daext.com, find and manage your keys at https://daext.com/account/. For Envato Market licenses, use the item purchase code available in your downloads area.',
									'ultimate-markdown-pro'
								),
								'help'    => __( 'Enter your license key.', 'ultimate-markdown-pro' ),
							),
						),
					),
				),
			),
		);

		return $configuration;
	}

	/**
	 * Echo the SVG icon specified by the $icon_name parameter.
	 *
	 * @param string $icon_name The name of the icon to echo.
	 *
	 * @return void
	 */
	public function echo_icon_svg( $icon_name ) {

		switch ( $icon_name ) {

			case 'check-done-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M16 8V5.2C16 4.0799 16 3.51984 15.782 3.09202C15.5903 2.71569 15.2843 2.40973 14.908 2.21799C14.4802 2 13.9201 2 12.8 2H5.2C4.0799 2 3.51984 2 3.09202 2.21799C2.71569 2.40973 2.40973 2.71569 2.21799 3.09202C2 3.51984 2 4.0799 2 5.2V12.8C2 13.9201 2 14.4802 2.21799 14.908C2.40973 15.2843 2.71569 15.5903 3.09202 15.782C3.51984 16 4.0799 16 5.2 16H8M12 15L14 17L18.5 12.5M11.2 22H18.8C19.9201 22 20.4802 22 20.908 21.782C21.2843 21.5903 21.5903 21.2843 21.782 20.908C22 20.4802 22 19.9201 22 18.8V11.2C22 10.0799 22 9.51984 21.782 9.09202C21.5903 8.71569 21.2843 8.40973 20.908 8.21799C20.4802 8 19.9201 8 18.8 8H11.2C10.0799 8 9.51984 8 9.09202 8.21799C8.71569 8.40973 8.40973 8.71569 8.21799 9.09202C8 9.51984 8 10.0799 8 11.2V18.8C8 19.9201 8 20.4802 8.21799 20.908C8.40973 21.2843 8.71569 21.5903 9.09202 21.782C9.51984 22 10.0799 22 11.2 22Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'edit-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M11 3.99998H6.8C5.11984 3.99998 4.27976 3.99998 3.63803 4.32696C3.07354 4.61458 2.6146 5.07353 2.32698 5.63801C2 6.27975 2 7.11983 2 8.79998V17.2C2 18.8801 2 19.7202 2.32698 20.362C2.6146 20.9264 3.07354 21.3854 3.63803 21.673C4.27976 22 5.11984 22 6.8 22H15.2C16.8802 22 17.7202 22 18.362 21.673C18.9265 21.3854 19.3854 20.9264 19.673 20.362C20 19.7202 20 18.8801 20 17.2V13M7.99997 16H9.67452C10.1637 16 10.4083 16 10.6385 15.9447C10.8425 15.8957 11.0376 15.8149 11.2166 15.7053C11.4184 15.5816 11.5914 15.4086 11.9373 15.0627L21.5 5.49998C22.3284 4.67156 22.3284 3.32841 21.5 2.49998C20.6716 1.67156 19.3284 1.67155 18.5 2.49998L8.93723 12.0627C8.59133 12.4086 8.41838 12.5816 8.29469 12.7834C8.18504 12.9624 8.10423 13.1574 8.05523 13.3615C7.99997 13.5917 7.99997 13.8363 7.99997 14.3255V16Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'brackets-ellipses':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M18.5708 20C19.8328 20 20.8568 18.977 20.8568 17.714V13.143L21.9998 12L20.8568 10.857V6.286C20.8568 5.023 19.8338 4 18.5708 4M5.429 4C4.166 4 3.143 5.023 3.143 6.286V10.857L2 12L3.143 13.143V17.714C3.143 18.977 4.166 20 5.429 20M7.5 12H7.51M12 12H12.01M16.5 12H16.51M8 12C8 12.2761 7.77614 12.5 7.5 12.5C7.22386 12.5 7 12.2761 7 12C7 11.7239 7.22386 11.5 7.5 11.5C7.77614 11.5 8 11.7239 8 12ZM12.5 12C12.5 12.2761 12.2761 12.5 12 12.5C11.7239 12.5 11.5 12.2761 11.5 12C11.5 11.7239 11.7239 11.5 12 11.5C12.2761 11.5 12.5 11.7239 12.5 12ZM17 12C17 12.2761 16.7761 12.5 16.5 12.5C16.2239 12.5 16 12.2761 16 12C16 11.7239 16.2239 11.5 16.5 11.5C16.7761 11.5 17 11.7239 17 12Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'file-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M14 11H8M10 15H8M16 7H8M20 6.8V17.2C20 18.8802 20 19.7202 19.673 20.362C19.3854 20.9265 18.9265 21.3854 18.362 21.673C17.7202 22 16.8802 22 15.2 22H8.8C7.11984 22 6.27976 22 5.63803 21.673C5.07354 21.3854 4.6146 20.9265 4.32698 20.362C4 19.7202 4 18.8802 4 17.2V6.8C4 5.11984 4 4.27976 4.32698 3.63803C4.6146 3.07354 5.07354 2.6146 5.63803 2.32698C6.27976 2 7.11984 2 8.8 2H15.2C16.8802 2 17.7202 2 18.362 2.32698C18.9265 2.6146 19.3854 3.07354 19.673 3.63803C20 4.27976 20 5.11984 20 6.8Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'file-06':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M14 2.26953V6.40007C14 6.96012 14 7.24015 14.109 7.45406C14.2049 7.64222 14.3578 7.7952 14.546 7.89108C14.7599 8.00007 15.0399 8.00007 15.6 8.00007H19.7305M16 13H8M16 17H8M10 9H8M14 2H8.8C7.11984 2 6.27976 2 5.63803 2.32698C5.07354 2.6146 4.6146 3.07354 4.32698 3.63803C4 4.27976 4 5.11984 4 6.8V17.2C4 18.8802 4 19.7202 4.32698 20.362C4.6146 20.9265 5.07354 21.3854 5.63803 21.673C6.27976 22 7.11984 22 8.8 22H15.2C16.8802 22 17.7202 22 18.362 21.673C18.9265 21.3854 19.3854 20.9265 19.673 20.362C20 19.7202 20 18.8802 20 17.2V8L14 2Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'link-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
	                    <path d="M9.99999 13C10.4294 13.5741 10.9773 14.0491 11.6065 14.3929C12.2357 14.7367 12.9315 14.9411 13.6466 14.9923C14.3618 15.0435 15.0796 14.9403 15.7513 14.6897C16.4231 14.4392 17.0331 14.047 17.54 13.54L20.54 10.54C21.4508 9.59695 21.9547 8.33394 21.9434 7.02296C21.932 5.71198 21.4061 4.45791 20.4791 3.53087C19.552 2.60383 18.298 2.07799 16.987 2.0666C15.676 2.0552 14.413 2.55918 13.47 3.46997L11.75 5.17997M14 11C13.5705 10.4258 13.0226 9.95078 12.3934 9.60703C11.7642 9.26327 11.0685 9.05885 10.3533 9.00763C9.63819 8.95641 8.9204 9.0596 8.24864 9.31018C7.57688 9.56077 6.96687 9.9529 6.45999 10.46L3.45999 13.46C2.5492 14.403 2.04522 15.666 2.05662 16.977C2.06801 18.288 2.59385 19.542 3.52089 20.4691C4.44793 21.3961 5.702 21.9219 7.01298 21.9333C8.32396 21.9447 9.58697 21.4408 10.53 20.53L12.24 18.82" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	                    </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'bar-chart-07':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 21H6.2C5.07989 21 4.51984 21 4.09202 20.782C3.71569 20.5903 3.40973 20.2843 3.21799 19.908C3 19.4802 3 18.9201 3 17.8V3M7 10.5V17.5M11.5 5.5V17.5M16 10.5V17.5M20.5 5.5V17.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'intersect-square':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 5.2C2 4.07989 2 3.51984 2.21799 3.09202C2.40973 2.71569 2.71569 2.40973 3.09202 2.21799C3.51984 2 4.0799 2 5.2 2H12.8C13.9201 2 14.4802 2 14.908 2.21799C15.2843 2.40973 15.5903 2.71569 15.782 3.09202C16 3.51984 16 4.0799 16 5.2V12.8C16 13.9201 16 14.4802 15.782 14.908C15.5903 15.2843 15.2843 15.5903 14.908 15.782C14.4802 16 13.9201 16 12.8 16H5.2C4.07989 16 3.51984 16 3.09202 15.782C2.71569 15.5903 2.40973 15.2843 2.21799 14.908C2 14.4802 2 13.9201 2 12.8V5.2Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 11.2C8 10.0799 8 9.51984 8.21799 9.09202C8.40973 8.71569 8.71569 8.40973 9.09202 8.21799C9.51984 8 10.0799 8 11.2 8H18.8C19.9201 8 20.4802 8 20.908 8.21799C21.2843 8.40973 21.5903 8.71569 21.782 9.09202C22 9.51984 22 10.0799 22 11.2V18.8C22 19.9201 22 20.4802 21.782 20.908C21.5903 21.2843 21.2843 21.5903 20.908 21.782C20.4802 22 19.9201 22 18.8 22H11.2C10.0799 22 9.51984 22 9.09202 21.782C8.71569 21.5903 8.40973 21.2843 8.21799 20.908C8 20.4802 8 19.9201 8 18.8V11.2Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'share-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 6H17.8C16.1198 6 15.2798 6 14.638 6.32698C14.0735 6.6146 13.6146 7.07354 13.327 7.63803C13 8.27976 13 9.11984 13 10.8V12M21 6L18 3M21 6L18 9M10 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V14" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'refresh-cw-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M22 10C22 10 19.995 7.26822 18.3662 5.63824C16.7373 4.00827 14.4864 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21C16.1031 21 19.5649 18.2543 20.6482 14.5M22 10V4M22 10H16" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-circle-broken':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'cursor-click-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 3.5V2M5.06066 5.06066L4 4M5.06066 13L4 14.0607M13 5.06066L14.0607 4M3.5 9H2M8.5 8.5L12.6111 21.2778L15.5 18.3889L19.1111 22L22 19.1111L18.3889 15.5L21.2778 12.6111L8.5 8.5Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'list':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M21 12L9 12M21 6L9 6M21 18L9 18M5 12C5 12.5523 4.55228 13 4 13C3.44772 13 3 12.5523 3 12C3 11.4477 3.44772 11 4 11C4.55228 11 5 11.4477 5 12ZM5 6C5 6.55228 4.55228 7 4 7C3.44772 7 3 6.55228 3 6C3 5.44772 3.44772 5 4 5C4.55228 5 5 5.44772 5 6ZM5 18C5 18.5523 4.55228 19 4 19C3.44772 19 3 18.5523 3 18C3 17.4477 3.44772 17 4 17C4.55228 17 5 17.4477 5 18Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'database-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 5C21 6.65685 16.9706 8 12 8C7.02944 8 3 6.65685 3 5M21 5C21 3.34315 16.9706 2 12 2C7.02944 2 3 3.34315 3 5M21 5V19C21 20.66 17 22 12 22C7 22 3 20.66 3 19V5M21 9.72021C21 11.3802 17 12.7202 12 12.7202C7 12.7202 3 11.3802 3 9.72021M21 14.44C21 16.1 17 17.44 12 17.44C7 17.44 3 16.1 3 14.44" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'tool-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 6L10.5 10.5M6 6H3L2 3L3 2L6 3V6ZM19.259 2.74101L16.6314 5.36863C16.2354 5.76465 16.0373 5.96265 15.9632 6.19098C15.8979 6.39183 15.8979 6.60817 15.9632 6.80902C16.0373 7.03735 16.2354 7.23535 16.6314 7.63137L16.8686 7.86863C17.2646 8.26465 17.4627 8.46265 17.691 8.53684C17.8918 8.6021 18.1082 8.6021 18.309 8.53684C18.5373 8.46265 18.7354 8.26465 19.1314 7.86863L21.5893 5.41072C21.854 6.05488 22 6.76039 22 7.5C22 10.5376 19.5376 13 16.5 13C16.1338 13 15.7759 12.9642 15.4298 12.8959C14.9436 12.8001 14.7005 12.7521 14.5532 12.7668C14.3965 12.7824 14.3193 12.8059 14.1805 12.8802C14.0499 12.9501 13.919 13.081 13.657 13.343L6.5 20.5C5.67157 21.3284 4.32843 21.3284 3.5 20.5C2.67157 19.6716 2.67157 18.3284 3.5 17.5L10.657 10.343C10.919 10.081 11.0499 9.95005 11.1198 9.81949C11.1941 9.68068 11.2176 9.60347 11.2332 9.44681C11.2479 9.29945 11.1999 9.05638 11.1041 8.57024C11.0358 8.22406 11 7.86621 11 7.5C11 4.46243 13.4624 2 16.5 2C17.5055 2 18.448 2.26982 19.259 2.74101ZM12.0001 14.9999L17.5 20.4999C18.3284 21.3283 19.6716 21.3283 20.5 20.4999C21.3284 19.6715 21.3284 18.3283 20.5 17.4999L15.9753 12.9753C15.655 12.945 15.3427 12.8872 15.0408 12.8043C14.6517 12.6975 14.2249 12.7751 13.9397 13.0603L12.0001 14.9999Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'refresh-ccw-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 18.8746C19.1213 17.329 20.5 14.8255 20.5 12C20.5 7.30555 16.6944 3.49998 12 3.49998H11.5M12 20.5C7.30558 20.5 3.5 16.6944 3.5 12C3.5 9.17444 4.87867 6.67091 7 5.12537M11 22.4L13 20.4L11 18.4M13 5.59998L11 3.59998L13 1.59998" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'file-code-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5 18.5C5 18.9644 5 19.1966 5.02567 19.3916C5.2029 20.7378 6.26222 21.7971 7.60842 21.9743C7.80337 22 8.03558 22 8.5 22H16.2C17.8802 22 18.7202 22 19.362 21.673C19.9265 21.3854 20.3854 20.9265 20.673 20.362C21 19.7202 21 18.8802 21 17.2V9.98822C21 9.25445 21 8.88757 20.9171 8.5423C20.8436 8.2362 20.7224 7.94356 20.5579 7.67515C20.3724 7.3724 20.113 7.11296 19.5941 6.59411L16.4059 3.40589C15.887 2.88703 15.6276 2.6276 15.3249 2.44208C15.0564 2.27759 14.7638 2.15638 14.4577 2.08289C14.1124 2 13.7455 2 13.0118 2H8.5C8.03558 2 7.80337 2 7.60842 2.02567C6.26222 2.2029 5.2029 3.26222 5.02567 4.60842C5 4.80337 5 5.03558 5 5.5M9 14.5L11.5 12L9 9.5M5 9.5L2.5 12L5 14.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'upload-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M16 12L12 8M12 8L8 12M12 8V17.2C12 18.5907 12 19.2861 12.5505 20.0646C12.9163 20.5819 13.9694 21.2203 14.5972 21.3054C15.5421 21.4334 15.9009 21.2462 16.6186 20.8719C19.8167 19.2036 22 15.8568 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 15.7014 4.01099 18.9331 7 20.6622" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'dataflow-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 4V15.2C12 16.8802 12 17.7202 12.327 18.362C12.6146 18.9265 13.0735 19.3854 13.638 19.673C14.2798 20 15.1198 20 16.8 20H17M17 20C17 21.1046 17.8954 22 19 22C20.1046 22 21 21.1046 21 20C21 18.8954 20.1046 18 19 18C17.8954 18 17 18.8954 17 20ZM7 4L17 4M7 4C7 5.10457 6.10457 6 5 6C3.89543 6 3 5.10457 3 4C3 2.89543 3.89543 2 5 2C6.10457 2 7 2.89543 7 4ZM17 4C17 5.10457 17.8954 6 19 6C20.1046 6 21 5.10457 21 4C21 2.89543 20.1046 2 19 2C17.8954 2 17 2.89543 17 4ZM12 12H17M17 12C17 13.1046 17.8954 14 19 14C20.1046 14 21 13.1046 21 12C21 10.8954 20.1046 10 19 10C17.8954 10 17 10.8954 17 12Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'zap-fast':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 17.5H3.5M6.5 12H2M9 6.5H4M17 3L10.4036 12.235C10.1116 12.6438 9.96562 12.8481 9.97194 13.0185C9.97744 13.1669 10.0486 13.3051 10.1661 13.3958C10.3011 13.5 10.5522 13.5 11.0546 13.5H16L15 21L21.5964 11.765C21.8884 11.3562 22.0344 11.1519 22.0281 10.9815C22.0226 10.8331 21.9514 10.6949 21.8339 10.6042C21.6989 10.5 21.4478 10.5 20.9454 10.5H16L17 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'server-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M19 9C19 12.866 15.866 16 12 16M19 9C19 5.13401 15.866 2 12 2M19 9H5M12 16C8.13401 16 5 12.866 5 9M12 16C13.7509 14.0832 14.7468 11.5956 14.8009 9C14.7468 6.40442 13.7509 3.91685 12 2M12 16C10.2491 14.0832 9.25498 11.5956 9.20091 9C9.25498 6.40442 10.2491 3.91685 12 2M12 16V18M5 9C5 5.13401 8.13401 2 12 2M14 20C14 21.1046 13.1046 22 12 22C10.8954 22 10 21.1046 10 20M14 20C14 18.8954 13.1046 18 12 18M14 20H21M10 20C10 18.8954 10.8954 18 12 18M10 20H3" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'settings-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M18.7273 14.7273C18.6063 15.0015 18.5702 15.3056 18.6236 15.6005C18.6771 15.8954 18.8177 16.1676 19.0273 16.3818L19.0818 16.4364C19.2509 16.6052 19.385 16.8057 19.4765 17.0265C19.568 17.2472 19.6151 17.4838 19.6151 17.7227C19.6151 17.9617 19.568 18.1983 19.4765 18.419C19.385 18.6397 19.2509 18.8402 19.0818 19.0091C18.913 19.1781 18.7124 19.3122 18.4917 19.4037C18.271 19.4952 18.0344 19.5423 17.7955 19.5423C17.5565 19.5423 17.3199 19.4952 17.0992 19.4037C16.8785 19.3122 16.678 19.1781 16.5091 19.0091L16.4545 18.9545C16.2403 18.745 15.9682 18.6044 15.6733 18.5509C15.3784 18.4974 15.0742 18.5335 14.8 18.6545C14.5311 18.7698 14.3018 18.9611 14.1403 19.205C13.9788 19.4489 13.8921 19.7347 13.8909 20.0273V20.1818C13.8909 20.664 13.6994 21.1265 13.3584 21.4675C13.0174 21.8084 12.5549 22 12.0727 22C11.5905 22 11.1281 21.8084 10.7871 21.4675C10.4461 21.1265 10.2545 20.664 10.2545 20.1818V20.1C10.2475 19.7991 10.1501 19.5073 9.97501 19.2625C9.79991 19.0176 9.55521 18.8312 9.27273 18.7273C8.99853 18.6063 8.69437 18.5702 8.39947 18.6236C8.10456 18.6771 7.83244 18.8177 7.61818 19.0273L7.56364 19.0818C7.39478 19.2509 7.19425 19.385 6.97353 19.4765C6.7528 19.568 6.51621 19.6151 6.27727 19.6151C6.03834 19.6151 5.80174 19.568 5.58102 19.4765C5.36029 19.385 5.15977 19.2509 4.99091 19.0818C4.82186 18.913 4.68775 18.7124 4.59626 18.4917C4.50476 18.271 4.45766 18.0344 4.45766 17.7955C4.45766 17.5565 4.50476 17.3199 4.59626 17.0992C4.68775 16.8785 4.82186 16.678 4.99091 16.5091L5.04545 16.4545C5.25503 16.2403 5.39562 15.9682 5.4491 15.6733C5.50257 15.3784 5.46647 15.0742 5.34545 14.8C5.23022 14.5311 5.03887 14.3018 4.79497 14.1403C4.55107 13.9788 4.26526 13.8921 3.97273 13.8909H3.81818C3.33597 13.8909 2.87351 13.6994 2.53253 13.3584C2.19156 13.0174 2 12.5549 2 12.0727C2 11.5905 2.19156 11.1281 2.53253 10.7871C2.87351 10.4461 3.33597 10.2545 3.81818 10.2545H3.9C4.2009 10.2475 4.49273 10.1501 4.73754 9.97501C4.98236 9.79991 5.16883 9.55521 5.27273 9.27273C5.39374 8.99853 5.42984 8.69437 5.37637 8.39947C5.3229 8.10456 5.18231 7.83244 4.97273 7.61818L4.91818 7.56364C4.74913 7.39478 4.61503 7.19425 4.52353 6.97353C4.43203 6.7528 4.38493 6.51621 4.38493 6.27727C4.38493 6.03834 4.43203 5.80174 4.52353 5.58102C4.61503 5.36029 4.74913 5.15977 4.91818 4.99091C5.08704 4.82186 5.28757 4.68775 5.50829 4.59626C5.72901 4.50476 5.96561 4.45766 6.20455 4.45766C6.44348 4.45766 6.68008 4.50476 6.9008 4.59626C7.12152 4.68775 7.32205 4.82186 7.49091 4.99091L7.54545 5.04545C7.75971 5.25503 8.03183 5.39562 8.32674 5.4491C8.62164 5.50257 8.9258 5.46647 9.2 5.34545H9.27273C9.54161 5.23022 9.77093 5.03887 9.93245 4.79497C10.094 4.55107 10.1807 4.26526 10.1818 3.97273V3.81818C10.1818 3.33597 10.3734 2.87351 10.7144 2.53253C11.0553 2.19156 11.5178 2 12 2C12.4822 2 12.9447 2.19156 13.2856 2.53253C13.6266 2.87351 13.8182 3.33597 13.8182 3.81818V3.9C13.8193 4.19253 13.906 4.47834 14.0676 4.72224C14.2291 4.96614 14.4584 5.15749 14.7273 5.27273C15.0015 5.39374 15.3056 5.42984 15.6005 5.37637C15.8954 5.3229 16.1676 5.18231 16.3818 4.97273L16.4364 4.91818C16.6052 4.74913 16.8057 4.61503 17.0265 4.52353C17.2472 4.43203 17.4838 4.38493 17.7227 4.38493C17.9617 4.38493 18.1983 4.43203 18.419 4.52353C18.6397 4.61503 18.8402 4.74913 19.0091 4.91818C19.1781 5.08704 19.3122 5.28757 19.4037 5.50829C19.4952 5.72901 19.5423 5.96561 19.5423 6.20455C19.5423 6.44348 19.4952 6.68008 19.4037 6.9008C19.3122 7.12152 19.1781 7.32205 19.0091 7.49091L18.9545 7.54545C18.745 7.75971 18.6044 8.03183 18.5509 8.32674C18.4974 8.62164 18.5335 8.9258 18.6545 9.2V9.27273C18.7698 9.54161 18.9611 9.77093 19.205 9.93245C19.4489 10.094 19.7347 10.1807 20.0273 10.1818H20.1818C20.664 10.1818 21.1265 10.3734 21.4675 10.7144C21.8084 11.0553 22 11.5178 22 12C22 12.4822 21.8084 12.9447 21.4675 13.2856C21.1265 13.6266 20.664 13.8182 20.1818 13.8182H20.1C19.8075 13.8193 19.5217 13.906 19.2778 14.0676C19.0339 14.2291 18.8425 14.4584 18.7273 14.7273Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'grid-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M8.4 3H4.6C4.03995 3 3.75992 3 3.54601 3.10899C3.35785 3.20487 3.20487 3.35785 3.10899 3.54601C3 3.75992 3 4.03995 3 4.6V8.4C3 8.96005 3 9.24008 3.10899 9.45399C3.20487 9.64215 3.35785 9.79513 3.54601 9.89101C3.75992 10 4.03995 10 4.6 10H8.4C8.96005 10 9.24008 10 9.45399 9.89101C9.64215 9.79513 9.79513 9.64215 9.89101 9.45399C10 9.24008 10 8.96005 10 8.4V4.6C10 4.03995 10 3.75992 9.89101 3.54601C9.79513 3.35785 9.64215 3.20487 9.45399 3.10899C9.24008 3 8.96005 3 8.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 3H15.6C15.0399 3 14.7599 3 14.546 3.10899C14.3578 3.20487 14.2049 3.35785 14.109 3.54601C14 3.75992 14 4.03995 14 4.6V8.4C14 8.96005 14 9.24008 14.109 9.45399C14.2049 9.64215 14.3578 9.79513 14.546 9.89101C14.7599 10 15.0399 10 15.6 10H19.4C19.9601 10 20.2401 10 20.454 9.89101C20.6422 9.79513 20.7951 9.64215 20.891 9.45399C21 9.24008 21 8.96005 21 8.4V4.6C21 4.03995 21 3.75992 20.891 3.54601C20.7951 3.35785 20.6422 3.20487 20.454 3.10899C20.2401 3 19.9601 3 19.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 14H15.6C15.0399 14 14.7599 14 14.546 14.109C14.3578 14.2049 14.2049 14.3578 14.109 14.546C14 14.7599 14 15.0399 14 15.6V19.4C14 19.9601 14 20.2401 14.109 20.454C14.2049 20.6422 14.3578 20.7951 14.546 20.891C14.7599 21 15.0399 21 15.6 21H19.4C19.9601 21 20.2401 21 20.454 20.891C20.6422 20.7951 20.7951 20.6422 20.891 20.454C21 20.2401 21 19.9601 21 19.4V15.6C21 15.0399 21 14.7599 20.891 14.546C20.7951 14.3578 20.6422 14.2049 20.454 14.109C20.2401 14 19.9601 14 19.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M8.4 14H4.6C4.03995 14 3.75992 14 3.54601 14.109C3.35785 14.2049 3.20487 14.3578 3.10899 14.546C3 14.7599 3 15.0399 3 15.6V19.4C3 19.9601 3 20.2401 3.10899 20.454C3.20487 20.6422 3.35785 20.7951 3.54601 20.891C3.75992 21 4.03995 21 4.6 21H8.4C8.96005 21 9.24008 21 9.45399 20.891C9.64215 20.7951 9.79513 20.6422 9.89101 20.454C10 20.2401 10 19.9601 10 19.4V15.6C10 15.0399 10 14.7599 9.89101 14.546C9.79513 14.3578 9.64215 14.2049 9.45399 14.109C9.24008 14 8.96005 14 8.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-up':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 15L12 9L6 15" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-down':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 9L12 15L18 9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M15 18L9 12L15 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 17L13 12L18 7M11 17L6 12L11 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 18L15 12L9 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 17L11 12L6 7M13 17L18 12L13 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'arrow-up-right':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M7 17L17 7M17 7H7M17 7V17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'plus':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 5V19M5 12H19" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-circle-broken':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-in-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 8L16 12M16 12L12 16M16 12H3M3.33782 7C5.06687 4.01099 8.29859 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C8.29859 22 5.06687 19.989 3.33782 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-out-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 8L22 12M22 12L18 16M22 12H9M15 4.20404C13.7252 3.43827 12.2452 3 10.6667 3C5.8802 3 2 7.02944 2 12C2 16.9706 5.8802 21 10.6667 21C12.2452 21 13.7252 20.5617 15 19.796" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-asc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H9M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-desc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H21M3 18H9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'clipboard-icon-svg':
				$xml = '<?xml version="1.0" encoding="utf-8"?>
				<svg version="1.1" id="Layer_3" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
					 viewBox="0 0 20 20" style="enable-background:new 0 0 20 20;" xml:space="preserve">
				<path d="M14,18H8c-1.1,0-2-0.9-2-2V7c0-1.1,0.9-2,2-2h6c1.1,0,2,0.9,2,2v9C16,17.1,15.1,18,14,18z M8,7v9h6V7H8z"/>
				<path d="M5,4h6V2H5C3.9,2,3,2.9,3,4v9h2V4z"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'x':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 7L7 17M7 7L17 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'diamond-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M2.49954 9H21.4995M9.99954 3L7.99954 9L11.9995 20.5L15.9995 9L13.9995 3M12.6141 20.2625L21.5727 9.51215C21.7246 9.32995 21.8005 9.23885 21.8295 9.13717C21.8551 9.04751 21.8551 8.95249 21.8295 8.86283C21.8005 8.76114 21.7246 8.67005 21.5727 8.48785L17.2394 3.28785C17.1512 3.18204 17.1072 3.12914 17.0531 3.09111C17.0052 3.05741 16.9518 3.03238 16.8953 3.01717C16.8314 3 16.7626 3 16.6248 3H7.37424C7.2365 3 7.16764 3 7.10382 3.01717C7.04728 3.03238 6.99385 3.05741 6.94596 3.09111C6.89192 3.12914 6.84783 3.18204 6.75966 3.28785L2.42633 8.48785C2.2745 8.67004 2.19858 8.76114 2.16957 8.86283C2.144 8.95249 2.144 9.04751 2.16957 9.13716C2.19858 9.23885 2.2745 9.32995 2.42633 9.51215L11.385 20.2625C11.596 20.5158 11.7015 20.6424 11.8279 20.6886C11.9387 20.7291 12.0603 20.7291 12.1712 20.6886C12.2975 20.6424 12.4031 20.5158 12.6141 20.2625Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-verified-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 12L11 14L15.5 9.5M7.33377 3.8187C8.1376 3.75455 8.90071 3.43846 9.51447 2.91542C10.9467 1.69486 13.0533 1.69486 14.4855 2.91542C15.0993 3.43846 15.8624 3.75455 16.6662 3.8187C18.5421 3.96839 20.0316 5.45794 20.1813 7.33377C20.2455 8.1376 20.5615 8.90071 21.0846 9.51447C22.3051 10.9467 22.3051 13.0533 21.0846 14.4855C20.5615 15.0993 20.2455 15.8624 20.1813 16.6662C20.0316 18.5421 18.5421 20.0316 16.6662 20.1813C15.8624 20.2455 15.0993 20.5615 14.4855 21.0846C13.0533 22.3051 10.9467 22.3051 9.51447 21.0846C8.90071 20.5615 8.1376 20.2455 7.33377 20.1813C5.45794 20.0316 3.96839 18.5421 3.8187 16.6662C3.75455 15.8624 3.43846 15.0993 2.91542 14.4855C1.69486 13.0533 1.69486 10.9467 2.91542 9.51447C3.43846 8.90071 3.75455 8.1376 3.8187 7.33377C3.96839 5.45794 5.45794 3.96839 7.33377 3.8187Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'line-chart-up-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 9L11.5657 14.4343C11.3677 14.6323 11.2687 14.7313 11.1545 14.7684C11.0541 14.8011 10.9459 14.8011 10.8455 14.7684C10.7313 14.7313 10.6323 14.6323 10.4343 14.4343L8.56569 12.5657C8.36768 12.3677 8.26867 12.2687 8.15451 12.2316C8.05409 12.1989 7.94591 12.1989 7.84549 12.2316C7.73133 12.2687 7.63232 12.3677 7.43431 12.5657L3 17M17 9H13M17 9V13M7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V7.8C21 6.11984 21 5.27976 20.673 4.63803C20.3854 4.07354 19.9265 3.6146 19.362 3.32698C18.7202 3 17.8802 3 16.2 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'code-browser':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 9H2M14 17.5L16.5 15L14 12.5M10 12.5L7.5 15L10 17.5M2 7.8L2 16.2C2 17.8802 2 18.7202 2.32698 19.362C2.6146 19.9265 3.07354 20.3854 3.63803 20.673C4.27976 21 5.11984 21 6.8 21H17.2C18.8802 21 19.7202 21 20.362 20.673C20.9265 20.3854 21.3854 19.9265 21.673 19.362C22 18.7202 22 17.8802 22 16.2V7.8C22 6.11984 22 5.27977 21.673 4.63803C21.3854 4.07354 20.9265 3.6146 20.362 3.32698C19.7202 3 18.8802 3 17.2 3L6.8 3C5.11984 3 4.27976 3 3.63803 3.32698C3.07354 3.6146 2.6146 4.07354 2.32698 4.63803C2 5.27976 2 6.11984 2 7.8Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'layout-alt-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17.5 17H6.5M17.5 13H6.5M3 9H21M7.8 3H16.2C17.8802 3 18.7202 3 19.362 3.32698C19.9265 3.6146 20.3854 4.07354 20.673 4.63803C21 5.27976 21 6.11984 21 7.8V16.2C21 17.8802 21 18.7202 20.673 19.362C20.3854 19.9265 19.9265 20.3854 19.362 20.673C18.7202 21 17.8802 21 16.2 21H7.8C6.11984 21 5.27976 21 4.63803 20.673C4.07354 20.3854 3.6146 19.9265 3.32698 19.362C3 18.7202 3 17.8802 3 16.2V7.8C3 6.11984 3 5.27976 3.32698 4.63803C3.6146 4.07354 4.07354 3.6146 4.63803 3.32698C5.27976 3 6.11984 3 7.8 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			default:
				$xml = '';

				break;

		}

		echo wp_kses( $xml, $allowed_html );
	}

	/**
	 * Save a dismissible notice in the "daextulmap_dismissible_notice_a" WordPress.
	 *
	 * @param string $message The message of the dismissible notice.
	 * @param string $element_class The class of the dismissible notice.
	 *
	 * @return void
	 */
	public function save_dismissible_notice( $message, $element_class ) {

		$dismissible_notice = array(
			'user_id' => get_current_user_id(),
			'message' => $message,
			'class'   => $element_class,
		);

		// Get the current option value.
		$dismissible_notice_a = get_option( 'daextulmap_dismissible_notice_a' );

		// If the option is not an array, initialize it as an array.
		if ( ! is_array( $dismissible_notice_a ) ) {
			$dismissible_notice_a = array();
		}

		// Add the dismissible notice to the array.
		$dismissible_notice_a[] = $dismissible_notice;

		// Save the dismissible notice in the "daextulmap_dismissible_notice_a" WordPress option.
		update_option( 'daextulmap_dismissible_notice_a', $dismissible_notice_a );
	}

	/**
	 * Display the dismissible notices stored in the "daextulmap_dismissible_notice_a" option.
	 *
	 * Note that the dismissible notice will be displayed only once to the user.
	 *
	 * The dismissable notice is first displayed (only to the same user with which has been generated) and then it is
	 * removed from the "daextulmap_dismissible_notice_a" option.
	 *
	 * @return void
	 */
	public function display_dismissible_notices() {

		$dismissible_notice_a = get_option( 'daextulmap_dismissible_notice_a' );

		// Iterate over the dismissible notices with the user id of the same user.
		if ( is_array( $dismissible_notice_a ) ) {
			foreach ( $dismissible_notice_a as $key => $dismissible_notice ) {

				// If the user id of the dismissible notice is the same as the current user id, display the message.
				if ( get_current_user_id() === $dismissible_notice['user_id'] ) {

					$message = $dismissible_notice['message'];
					$class   = $dismissible_notice['class'];

					?>
					<div class="<?php echo esc_attr( $class ); ?> notice">
						<p><?php echo esc_html( $message ); ?></p>
						<div class="notice-dismiss-button"><?php $this->echo_icon_svg( 'x' ); ?></div>
					</div>

					<?php

					// Remove the echoed dismissible notice from the "daextulmap_dismissible_notice_a" WordPress option.
					unset( $dismissible_notice_a[ $key ] );

					update_option( 'daextulmap_dismissible_notice_a', $dismissible_notice_a );

				}
			}
		}
	}

	/**
	 * Returns true if there are exportable data or false if here are no exportable data.
	 */
	public function exportable_data_exists() {

		return true;

	}

	/**
	 * Send a request to the remote server to get the plugin information.
	 *
	 * These plugin information are used:
	 *
	 * - To include the plugin information for our custom plugin using the callback of the "plugins_api" filter.
	 * - To report the plugin as out of date if needed by adding information to the transient.
	 * - As a verification of the license. Since a response with an error is returned if the license is not valid.
	 *
	 * @return false|object
	 */
	public function fetch_remote_plugin_info() {

		// The transient expiration is set to 24 hours.
		$transient_expiration = 86400;

		$remote = get_transient( DAEXTULMAP_WP_PLUGIN_UPDATE_INFO_TRANSIENT );

		/**
		 * If the transient does not exist, does not have a value, or has expired, then fetch the plugin information
		 * from the remote server on daext.com.
		 */
		if ( false === $remote ) {

			$license_provider = get_option( 'daextulmap_license_provider' );
			$license_key      = get_option( 'daextulmap_license_key' );

			// Prepare the body of the request.
			$body = wp_json_encode(
				array(
					'license_provider' => $license_provider,
					'license_key'      => $license_key,
					'slug'             => 'ultimate-markdown-pro',
					'domain'           => site_url(),
				)
			);

			$remote = wp_remote_post(
				DAEXTULMAP_WP_PLUGIN_UPDATE_INFO_API_URL,
				array(
					'method'  => 'POST',
					'timeout' => 10,
					'body'    => $body,
					'headers' => array(
						'Content-Type' => 'application/json',
					),
				)
			);

			$response_code = wp_remote_retrieve_response_code( $remote );

			if (
				is_wp_error( $remote )
				|| 200 !== $response_code
				|| empty( wp_remote_retrieve_body( $remote ) )
			) {

				/**
				 * For valid response where the license has been verified, and it's invalid, save a specific
				 * 'invalid_license' error in the transient.
				 */
				$remote_body = json_decode( wp_remote_retrieve_body( $remote ) );
				if ( 403 === $response_code && 'invalid_license' === $remote_body->error ) {

					$error_res = new WP_Error( 'invalid_license', 'Invalid License' );
					set_transient( DAEXTULMAP_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $error_res, $transient_expiration );

				}else{

					/**
					 * With other error response codes save a generic error response in the transient.
					 */
					$error_res = new WP_Error( 'generic_error', 'Generic Error' );
					set_transient( DAEXTULMAP_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $error_res, $transient_expiration );

				}

				return $error_res;

			} else {

				/**
				 * With a valid license, save the plugin information in the transient and return the plugin information.
				 * Otherwise, save the error in the transient and return the error.
				 */

				$remote = json_decode( wp_remote_retrieve_body( $remote ) );

				// Check if the fields of a valid response are also set.
				if ( isset( $remote->name ) &&
				     isset( $remote->slug ) ) {
					set_transient( DAEXTULMAP_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $remote, $transient_expiration );
					return $remote;
				} else {
					$error_res = new WP_Error( 'generic_error', 'Generic Error' );
					set_transient( DAEXTULMAP_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $error_res, $transient_expiration );
					return $error_res;
				}

			}

		}

		return $remote;
	}

	/**
	 * Verify the license key. If the license is not valid display a message and return false.
	 *
	 * @return bool
	 */
	public function is_valid_license() {

		$plugin_info = get_transient( DAEXTULMAP_WP_PLUGIN_UPDATE_INFO_TRANSIENT );

		if ( false === $plugin_info || is_wp_error( $plugin_info ) ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Display a notice to the user to activate the license.
	 *
	 * @return void
	 */
	public function display_license_activation_notice() {

		require_once $this->get( 'dir' ) . 'vendor/autoload.php';
		$plugin_update_checker = new PluginUpdateChecker(DAEXTULMAP_PLUGIN_UPDATE_CHECKER_SETTINGS);

		if ( ! $plugin_update_checker->is_valid_license() ) {
			?>
			<div class="daextulmap-license-activation-notice">
				<h2><?php esc_html_e( 'Activate Your License', 'ultimate-markdown-pro' ); ?></h2>
				<p><?php esc_html_e( "Please activate your license to enable plugin updates and access technical support. It's important to note that without updates, the plugin may stop functioning correctly when new WordPress versions are released. Furthermore, not receiving updates could expose your site to security issues.", 'ultimate-markdown-pro' ) . '</p>'; ?>
				<h4><?php esc_html_e( 'License Activation Steps', 'ultimate-markdown-pro' ); ?></h4>
				<ol>
					<li>
						<?php esc_html_e( 'If you purchased the plugin from daext.com, you can find your license key in your account at', 'ultimate-markdown-pro' ); ?>&nbsp<a href="https://daext.com/account/" target="_blank"><?php esc_html_e( 'https://daext.com/account/', 'ultimate-markdown-pro' ); ?></a>.
						<?php esc_html_e( 'For Envato Market purchases, use the item purchase code available in your', 'ultimate-markdown-pro' ); ?>&nbsp<strong><?php esc_html_e( 'Downloads', 'ultimate-markdown-pro' ); ?></strong>&nbsp<?php esc_html_e( 'area.', 'ultimate-markdown-pro' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Navigate to', 'ultimate-markdown-pro' ); ?>&nbsp<strong><?php esc_html_e( 'Options → License → License Management', 'ultimate-markdown-pro' ); ?></strong>&nbsp<?php esc_html_e( 'to enter your license key.', 'ultimate-markdown-pro' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Once in the', 'ultimate-markdown-pro' ); ?>&nbsp<strong><?php esc_html_e( 'License Management', 'ultimate-markdown-pro' ); ?></strong>&nbsp<?php esc_html_e( 'section, choose the appropriate license provider from the dropdown menu.', 'ultimate-markdown-pro' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Enter your license key into the provided field.', 'ultimate-markdown-pro' ); ?></li>
					<li>
						<?php esc_html_e( 'Click the', 'ultimate-markdown-pro' ); ?>&nbsp<strong><?php esc_html_e( 'Save settings', 'ultimate-markdown-pro' ); ?></strong>&nbsp<?php esc_html_e( 'button to activate your license.', 'ultimate-markdown-pro' ); ?>
					</li>
				</ol>
				<h4><?php esc_html_e( 'Reasons for This Notice', 'ultimate-markdown-pro' ); ?></h4>
				<p><?php esc_html_e( 'Here are the common reasons for this notification:', 'ultimate-markdown-pro' ) . '</p>'; ?>
				<ul>
					<li>
						<?php esc_html_e( 'A license key has not been provided.', 'ultimate-markdown-pro' ); ?></li>
					<li>
						<?php esc_html_e( 'The configured license key is invalid or expired.', 'ultimate-markdown-pro' ); ?></li>
					<li><?php esc_html_e( "The configured license key is already linked to other sites, and the license tier related to this key doesn't allow additional activations.", 'ultimate-markdown-pro' ); ?></li>
					<li>
						<?php echo esc_html__( 'Our server is temporarily unable to validate your license. To manually verify your license, click ', 'ultimate-markdown-pro' ) . '<a href="' . esc_url( $this->get_current_admin_page_verify_license_url() ) . '">' . esc_html__( 'this link', 'ultimate-markdown-pro' ) . '</a>' . esc_html__( '. A successful validation will dismiss this notice.', 'ultimate-markdown-pro' ); ?></li>
				</ul>
				<h4><?php esc_html_e( 'Support and Assistance', 'ultimate-markdown-pro' ); ?></h4>
				<p><?php esc_html_e( "If you're having trouble activating your license or locating your key, visit our", 'ultimate-markdown-pro' ); ?>&nbsp<a href="https://daext.com/support/" target="_blank"><?php esc_html_e( 'support page', 'ultimate-markdown-pro' ); ?></a>&nbsp<?php esc_html_e( 'for help.', 'ultimate-markdown-pro' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get the current page URL with the daextulmap_verify_license=1 parameter and a nonce.
	 *
	 * @return string|null
	 */
	public function get_current_admin_page_verify_license_url() {

		// Generate a nonce for the action "daextulmap_verify_license".
		$nonce = wp_create_nonce( 'daextulmap_verify_license' );

		// Add query parameters 'daextulmap_verify_license' and 'daextulmap_verify_license_nonce'.
		return admin_url(
			'admin.php?page=' . $this->get( 'slug' ) . '-documents' . '&' . http_build_query(
				array(
					'daextulmap_verify_license'       => 1,
					'daextulmap_verify_license_nonce' => $nonce,
				)
			)
		);
	}

}