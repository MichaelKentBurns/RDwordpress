<?php
/**
 * Here the REST API endpoint of the plugin are registered.
 *
 * @package ultimate-markdown-pro
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

/**
 * This class should be used to work with the REST API.
 */
class Daextulmap_Rest {

	/**
	 * The singleton instance of the class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextulmap_Shared|null
	 */
	private $shared = null;

	/**
	 * The object to return when the authentication fails.
	 *
	 * @var WP_REST_Response|null
	 */
	private $invalid_authentication_object = null;

	/**
	 * An instance of the Front Matter class.
	 *
	 * @var Daextulmap_Front_Matter|null
	 */
	private $front_matter = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextulmap_Shared::get_instance();

		$this->invalid_authentication_object = new WP_REST_Response( 'Invalid authentication.', '403' );

		/*
		 * Add custom routes to the Rest API
		 */
		add_action( 'rest_api_init', array( $this, 'rest_api_register_route' ) );

		// Require and instantiate the class used to handle Front Matter.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulmap-front-matter.php';
		$this->front_matter = new Daextulmap_Front_Matter( $this->shared );
	}

	/**
	 * Create a singleton instance of the class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add custom routes to the Rest API.
	 *
	 * @return void
	 */
	public function rest_api_register_route() {

		// Add the GET 'ultimate-markdown-pro/v1/documents' endpoint to the Rest API.
		register_rest_route(
			'ultimate-markdown-pro/v1',
			'/documents',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_api_daext_ultimate_markdown_pro_documents_callback' ),
				'permission_callback' => '__return_true',
			)
		);

		// Add the POST 'ultimate-markdown-pro/v1/import' endpoint to the Rest API.
		register_rest_route(
			'ultimate-markdown-pro/v1',
			'/import',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_ultimate_markdown_pro_import_callback' ),
				'permission_callback' => '__return_true',
			)
		);

		// Add the POST 'ultimate-markdown-pro/v1/read-options/' endpoint to the Rest API.
		register_rest_route(
			'ultimate-markdown-pro/v1',
			'/read-options/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_ultimate_markdown_pro_read_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_ultimate_markdown_pro_read_options_callback_permission_check' ),
			)
		);

		// Add the POST 'ultimate-markdown-pro/v1/options/' endpoint to the Rest API.
		register_rest_route(
			'ultimate-markdown-pro/v1',
			'/options',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_ultimate_markdown_pro_update_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_ultimate_markdown_pro_update_options_callback_permission_check' ),
			)
		);

		// Add the GET 'ultimate-markdown-pro/v1/export-post/' endpoint to the Rest API.
		register_rest_route(
			'ultimate-markdown-pro/v1',
			'/export-post',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_ultimate_markdown_pro_export_post_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_ultimate_markdown_pro_export_post_callback_permission_check' ),
			)
		);

		// Add the POST 'ultimate-markdown-pro/v1/parse-markdown/' endpoint to the Rest API.
		register_rest_route(
			'ultimate-markdown-pro/v1',
			'/parse-markdown',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_ultimate_markdown_pro_parse_markdown_callback' ),
				'permission_callback' => array( $this, 'rest_api_ultimate_markdown_pro_parse_markdown_callback_permission_check' ),
			)
		);
	}

	/**
	 * Callback for the GET 'ultimate-markdown-pro/v1/documents' endpoint of the Rest API.
	 *
	 * @param array $request Data received from the request.
	 *
	 * @return false|WP_REST_Response
	 */
	public function rest_api_daext_ultimate_markdown_pro_documents_callback( $request ) {

		if ( ! $this->authenticate_rest_request( $request, 'read' ) ) {
			return $this->invalid_authentication_object;
		}

		/**
		 * Parameters:
		 *
		 * - document_id
		 */

		// Get data.
		$document_id = $request->get_param( 'document_id' ) !== null ? intval( $request->get_param( 'document_id' ) ) : null;

		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_document';

		if ( is_null( $document_id ) ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$document_a = $wpdb->get_results(
				"SELECT * FROM {$wpdb->prefix}daextulmap_document"
			);

		} else {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$document_a = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextulmap_document WHERE document_id = %d", $document_id )
			);

		}

		if ( $wpdb->num_rows > 0 ) {

			// Prepare the response.
			$response = new WP_REST_Response( $document_a, '200' );

		} else {

			return false;

		}

		return $response;
	}

	/**
	 * Callback for the POST 'ultimate-markdown-pro/v1/import' endpoint of the Rest API.
	 *
	 * @param array $request Data received from the request.
	 *
	 * @return false|WP_REST_Response
	 */
	public function rest_api_daext_ultimate_markdown_pro_import_callback( $request ) {

		if ( ! $this->authenticate_rest_request( $request, 'create' ) ) {
			return $this->invalid_authentication_object;
		}

		$result = false;

		// Get data.
		$title            = $request->get_param( 'title' );
		$markdown_content = $request->get_param( 'content' );

		// Sanitize data.
		$title            = sanitize_text_field( $title );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitization functions can't be applied to Markdown content because the Markdown syntax would be altered.
		$markdown_content = $markdown_content;

		if ( intval( get_option( $this->shared->get( 'slug' ) . '_rest_api_import_type' ), 10 ) === 0 ) {

			$category_id = intval( get_option( $this->shared->get( 'slug' ) . '_default_category' ), 10 );

			// Upload the referenced images to the media library and update the URLs.
			if ( intval( get_option( $this->shared->get( 'slug' ) . '_upload_referenced_images_import_documents_rest_api' ), 10 ) === 1 ) {
				$markdown_content = $this->shared->replace_referenced_images_with_local_images( $markdown_content );
			}

			// Standard Import (create a document in the plugin documents).
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$result = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}daextulmap_document SET 
                            title = %s,
                            content = %s,
                            category_id = %d",
					$title,
					$markdown_content,
					$category_id
				)
			);

		} else {

			// Classic Import (create a new post in draft status in the specified custom post type).

			// Get the Front Matter fields available in the string.
			$front_matter = $this->front_matter->get( $markdown_content );

			// if the title is set with Front Matter use it. Otherwise, use the REST API 'title' parameter.
			$title = isset( $front_matter['title'] ) ? $front_matter['title'] : $title;

			// Upload the referenced images to the media library and update the URLs.
			if ( intval( get_option( $this->shared->get( 'slug' ) . '_upload_referenced_images_import_classic_rest_api' ), 10 ) === 1 ) {
				$markdown_content = $this->shared->replace_referenced_images_with_local_images( $markdown_content );
			}

			// Create a post for the Classic editor.
			$new_post_id = $this->shared->create_classic_post( $title, $markdown_content, $front_matter, 'rest_api' );

			if ( 0 !== $new_post_id && ! is_wp_error( $new_post_id ) ) {
				$result = true;
			}
		}

		if ( $result ) {
			$response = new WP_REST_Response( 'Data successfully added.', '200' );
		} else {
			$response = new WP_REST_Response( 'Query failed.', '500' );
		}

		return $response;
	}

	/**
	 * Authenticates the request based on the type of request and on the authentication options defined in the plugin
	 * options.
	 *
	 * Two discretional category of requests have been created. The "read" category used to read the plugin data, and
	 * the "create" category used to create new items in the plugin data.
	 *
	 * @param array  $request Data received from the request.
	 * @param string $type Type of request.
	 *
	 * @return bool
	 */
	private function authenticate_rest_request( $request, $type = 'read' ) {

		switch ( $type ) {

			case 'read':
				$authentication = intval( get_option( $this->shared->get( 'slug' ) . '_rest_api_authentication_read' ), 10 );
				break;

			case 'create':
				$authentication = intval( get_option( $this->shared->get( 'slug' ) . '_rest_api_authentication_create' ), 10 );
				break;

		}

		$response = false;
		switch ( $authentication ) {

			// Cookies.
			case 0:
				if ( current_user_can( get_option( $this->shared->get( 'slug' ) . '_rest_api_required_capability' ) ) ) {
					$response = true;
				}
				break;

			// REST API Key.
			case 1:
				$rest_api_key = $request->get_param( 'rest_api_key' );
				if ( isset( $rest_api_key ) &&
					get_option( $this->shared->get( 'slug' ) . '_rest_api_key' ) === $rest_api_key ) {
					$response = true;
				}
				break;

			// None.
			case 2:
				if ( 'read' === $type ) {
					$response = true;
				}
				break;

		}

		return $response;
	}

	/**
	 * Callback for the GET 'ultimate-markdown-pro/v1/options' endpoint of the Rest API.
	 *
	 *   This method is in the following contexts:
	 *
	 *  - To retrieve the plugin options in the "Options" menu.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_ultimate_markdown_pro_read_options_callback() {

		// Generate the response.
		$response = array();
		foreach ( $this->shared->get( 'options' ) as $key => $value ) {
			$response[ $key ] = get_option( $key );
		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_ultimate_markdown_pro_read_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_read_error',
				'Sorry, you are not allowed to read the Ultimate Markdown options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the POST 'ultimate-markdown-pro/v1/options' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 *  - To update the plugin options in the "Options" menu.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_REST_Response
	 */
	public function rest_api_ultimate_markdown_pro_update_options_callback( $request ) {

		// get and sanitize data --------------------------------------------------------------------------------------.

		$options = array();

		// Tab - Conversion -------------------------------------------------------------------------------------------.

		// Section - Import (Classic Editor) --------------------------------------------------------------------------.
		$options['daextulmap_markdown_parser']                      = $request->get_param( 'daextulmap_markdown_parser' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_markdown_parser' ) ) : null;
		$options['daextulmap_rest_api_markdown_parser']                      = $request->get_param( 'daextulmap_rest_api_markdown_parser' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_rest_api_markdown_parser' ) ) : null;
		$options['daextulmap_editor_markdown_parser']               = $request->get_param( 'daextulmap_editor_markdown_parser' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_editor_markdown_parser' ) ) : null;
		$options['daextulmap_live_preview_markdown_parser']         = $request->get_param( 'daextulmap_live_preview_markdown_parser' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_live_preview_markdown_parser' ) ) : null;
		$options['daextulmap_live_preview_php_auto_refresh']         = $request->get_param( 'daextulmap_live_preview_php_auto_refresh' ) !== null ? intval( $request->get_param( 'daextulmap_live_preview_php_auto_refresh' ), 10 ) : null;
		$options['daextulmap_live_preview_php_debounce_delay'] = $request->get_param( 'daextulmap_live_preview_php_debounce_delay' ) !== null ? intval( $request->get_param( 'daextulmap_live_preview_php_debounce_delay' ), 10 ) : null;
		$options['daextulmap_cebe_markdown_html5']                  = $request->get_param( 'daextulmap_cebe_markdown_html5' ) !== null ? intval( $request->get_param( 'daextulmap_cebe_markdown_html5' ), 10 ) : null;
		$options['daextulmap_cebe_markdown_keep_list_start_number'] = $request->get_param( 'daextulmap_cebe_markdown_keep_list_start_number' ) !== null ? intval( $request->get_param( 'daextulmap_cebe_markdown_keep_list_start_number' ), 10 ) : null;
		$options['daextulmap_cebe_markdown_enable_new_lines']       = $request->get_param( 'daextulmap_cebe_markdown_enable_new_lines' ) !== null ? intval( $request->get_param( 'daextulmap_cebe_markdown_enable_new_lines' ), 10 ) : null;

		// Section - Image Upload -------------------------------------------------------------------------------------.
		$options['daextulmap_upload_referenced_images_import_menu']               = $request->get_param( 'daextulmap_upload_referenced_images_import_menu' ) !== null ? intval( $request->get_param( 'daextulmap_upload_referenced_images_import_menu' ), 10 ) : null;
		$options['daextulmap_upload_referenced_images_import_classic_menu']       = $request->get_param( 'daextulmap_upload_referenced_images_import_classic_menu' ) !== null ? intval( $request->get_param( 'daextulmap_upload_referenced_images_import_classic_menu' ), 10 ) : null;
		$options['daextulmap_upload_referenced_images_import_markdown_ess']       = $request->get_param( 'daextulmap_upload_referenced_images_import_markdown_ess' ) !== null ? intval( $request->get_param( 'daextulmap_upload_referenced_images_import_markdown_ess' ), 10 ) : null;
		$options['daextulmap_upload_referenced_images_load_markdown_ess']         = $request->get_param( 'daextulmap_upload_referenced_images_load_markdown_ess' ) !== null ? intval( $request->get_param( 'daextulmap_upload_referenced_images_load_markdown_ess' ), 10 ) : null;
		$options['daextulmap_upload_referenced_images_submit_markdown_ess']       = $request->get_param( 'daextulmap_upload_referenced_images_submit_markdown_ess' ) !== null ? intval( $request->get_param( 'daextulmap_upload_referenced_images_submit_markdown_ess' ), 10 ) : null;
		$options['daextulmap_upload_referenced_images_import_classic_rest_api']   = $request->get_param( 'daextulmap_upload_referenced_images_import_classic_rest_api' ) !== null ? intval( $request->get_param( 'daextulmap_upload_referenced_images_import_classic_rest_api' ), 10 ) : null;
		$options['daextulmap_upload_referenced_images_import_documents_rest_api'] = $request->get_param( 'daextulmap_upload_referenced_images_import_documents_rest_api' ) !== null ? intval( $request->get_param( 'daextulmap_upload_referenced_images_import_documents_rest_api' ), 10 ) : null;

		// Tab - REST API ---------------------------------------------------------------------------------------------.

		// Section - Authentication -----------------------------------------------------------------------------------.
		$options['daextulmap_rest_api_authentication_read']   = $request->get_param( 'daextulmap_rest_api_authentication_read' ) !== null ? intval( $request->get_param( 'daextulmap_rest_api_authentication_read' ), 10 ) : null;
		$options['daextulmap_rest_api_authentication_create'] = $request->get_param( 'daextulmap_rest_api_authentication_create' ) !== null ? intval( $request->get_param( 'daextulmap_rest_api_authentication_create' ), 10 ) : null;
		$options['daextulmap_rest_api_key']                   = $request->get_param( 'daextulmap_rest_api_key' ) !== null ? sanitize_text_field( $request->get_param( 'daextulmap_rest_api_key' ) ) : null;
		$options['daextulmap_rest_api_required_capability']   = $request->get_param( 'daextulmap_rest_api_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_rest_api_required_capability' ) ) : null;

		// Section - Processing ---------------------------------------------------------------------------------------.
		$options['daextulmap_rest_api_import_type'] = $request->get_param( 'daextulmap_rest_api_import_type' ) !== null ? intval( $request->get_param( 'daextulmap_rest_api_import_type' ), 10 ) : null;

		// Tab - Advanced ---------------------------------------------------------------------------------------------.

		// Section - Import Options -----------------------------------------------------------------------------------.
		$options['daextulmap_import_post_type'] = $request->get_param( 'daextulmap_import_post_type' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_import_post_type' ) ) : null;
		$options['daextulmap_rest_api_post_type'] = $request->get_param( 'daextulmap_rest_api_post_type' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_rest_api_post_type' ) ) : null;
		$options['daextulmap_default_category'] = $request->get_param( 'daextulmap_default_category' ) !== null ? intval( $request->get_param( 'daextulmap_default_category' ), 10 ) : null;

		// Section - Export Options -----------------------------------------------------------------------------------.
		$options['daextulmap_exported_files_extension'] = $request->get_param( 'daextulmap_exported_files_extension' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_exported_files_extension' ) ) : null;
		$options['daextulmap_exported_files_include_front_matter'] = $request->get_param( 'daextulmap_exported_files_include_front_matter' ) !== null ? intval( $request->get_param( 'daextulmap_exported_files_include_front_matter' ), 10 ) : null;
		$options['daextulmap_exported_files_include_empty_front_matter_fields'] = $request->get_param( 'daextulmap_exported_files_include_empty_front_matter_fields' ) !== null ? intval( $request->get_param( 'daextulmap_exported_files_include_empty_front_matter_fields' ), 10 ) : null;

		// Section - Capabilities -------------------------------------------------------------------------------------.
		$options['daextulmap_documents_menu_required_capability']  = $request->get_param( 'daextulmap_documents_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_documents_menu_required_capability' ) ) : null;
		$options['daextulmap_categories_menu_required_capability'] = $request->get_param( 'daextulmap_categories_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_categories_menu_required_capability' ) ) : null;
		$options['daextulmap_tools_menu_required_capability']      = $request->get_param( 'daextulmap_tools_menu_required_capability' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_tools_menu_required_capability' ) ) : null;

		// Tab - License ----------------------------------------------------------------------------------------------.

		// Section - License Management -------------------------------------------------------------------------------.
		$options['daextulmap_license_provider'] = $request->get_param( 'daextulmap_license_provider' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_license_provider' ) ) : null;
		$options['daextulmap_license_key']      = $request->get_param( 'daextulmap_license_key' ) !== null ? sanitize_key( $request->get_param( 'daextulmap_license_key' ) ) : null;

		// Update the options -----------------------------------------------------------------------------------------.
		foreach ( $options as $key => $option ) {
			if ( null !== $option ) {
				update_option( $key, $option );
			}
		}

		require_once $this->shared->get( 'dir' ) . 'vendor/autoload.php';
		$plugin_update_checker = new PluginUpdateChecker(DAEXTULMAP_PLUGIN_UPDATE_CHECKER_SETTINGS);

		// Delete the transient used to store the plugin info previously retrieved from the remote server.
		$plugin_update_checker->delete_transient();

		// Fetch the plugin information from the remote server and saved it in the transient.
		$plugin_update_checker->fetch_remote_plugin_info();

		return new WP_REST_Response( 'Data successfully added.', '200' );
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_ultimate_markdown_pro_update_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to update the Ultimate Markdown options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 *  Callback for the GET 'ultimate-markdown-pro/v1/options' endpoint of the Rest API.
	 *
	 * This method is used to retrieve title and content of a Markdown file based on the post ID of the post containing
	 * the HTML content. It's used in the following contexts:
	 *
	 * - To export a post in Markdown format after clicking the "Export" button of the "Export Markdown" editor sidebar
	 * section.
	 *
	 * @param array $request Data received from the request.
	 *
	 * @return void|WP_REST_Response
	 */
	public function rest_api_daext_ultimate_markdown_pro_export_post_callback( $request ) {

		// Init vars.
		$data = null;

		// Get and sanitize data --------------------------------------------------------------------------------------.
		$post_id = $request->get_param( 'post_id' ) !== null ? intval( $request->get_param( 'post_id' ), 10 ) : null;

		// Get the current post content.
		$title        = get_the_title( $post_id );
		$html_content = get_post_field( 'post_content', $post_id );

		// An instance of the HTML to Markdown parsers is created and saved as global variable.
		require_once $this->shared->get( 'dir' ) . 'globalize-html-converter-parser.php';

		// Globalize the HTML to Markdown parser.
		global $daextulmap_converter;

		/**
		 * Convert HTML to Markdown. Note that the try/catch is used because the convert() method generates
		 * errors when the provided HTML is invalid. An example of content considered invalid is a post with
		 * HTML block comments and no HTML.
		 */
		try {

			$markdown_content = $daextulmap_converter->convert( $html_content );

			// Generates the Front Matter from the post data.
			$front_matter = $this->front_matter->generate_front_matter_from_post($post_id);

			// If Front Matter is set, add it to the Markdown content.
			if ( ! empty( $front_matter ) ) {
				$markdown_content = $this->front_matter->set( $markdown_content, $front_matter );
			}

			$data = array(
				'title'   => $title,
				'content' => $markdown_content,
			);

		} catch ( Exception $e ) {

			$data = array(
				'title'   => $title,
				'content' => '',
			);

		}

		if ( isset( $data ) && ! is_null( $data['content'] ) ) {

			return new WP_REST_Response( $data, 200 );

		}
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_ultimate_markdown_pro_export_post_callback_permission_check() {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to export posts.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 *  Callback for the GET 'ultimate-markdown-pro/v1/parse-markdown' endpoint of the Rest API.
	 *
	 * This method is used to retrieve title and content of a Markdown file based on the post ID of the post containing
	 * the HTML content. It's used in the following contexts:
	 *
	 * - To export a post in Markdown format after clicking the "Export" button of the "Export Markdown" editor sidebar
	 * section.
	 *
	 * @param array $request Data received from the request.
	 *
	 * @return void|WP_REST_Response
	 */
	public function rest_api_ultimate_markdown_pro_parse_markdown_callback( $request ) {

		$markdown_content = $request->get_param( 'markdown_content' ) !== null ? $request->get_param( 'markdown_content' ) : null;

		// Remove the YAML part from the document.
		$markdown_content = $this->shared->remove_yaml( $markdown_content );

		// Generate HTML from text with the Markdown syntax.
		$html_content = $this->shared->convert_markdown_to_html( $markdown_content, 'live_preview' );

		if ( isset( $html_content ) ) {

			return new WP_REST_Response( $html_content, 200 );

		}
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_ultimate_markdown_pro_parse_markdown_callback_permission_check() {

		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_documents_menu_required_capability' ) ) ) {
			return new WP_Error(
				'rest_read_error',
				'Sorry, you are not allowed to access the parser via the Documents menu.',
				array( 'status' => 403 )
			);
		}

		return true;
	}
}
