<?php
/**
 * This file contains the class Daextulmap_Ajax, used to include ajax actions.
 *
 * @package ultimate-markdown-pro
 */

/**
 * This class should be used to include ajax actions.
 */
class Daextulmap_Ajax {

	/**
	 * The instance of the Daextulmap_Ajax class.
	 *
	 * @var Daextulmap_Ajax
	 */
	protected static $instance = null;

	/**
	 * The instance of the Daextulmap_Shared class.
	 *
	 * @var Daextulmap_Shared
	 */
	private $shared = null;

	/**
	 * An instance of the Front Matter class.
	 *
	 * @var Daextulmap_Front_Matter|null
	 */
	private $front_matter = null;

	/**
	 * The constructor of the Daextulmap_Ajax class.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextulmap_Shared::get_instance();

		// Ajax requests for logged-in users --------------------------------------------------------------------------.
		add_action( 'wp_ajax_daextulmap_import_document', array( $this, 'daextulmap_import_document' ) );
		add_action( 'wp_ajax_daextulmap_load_document', array( $this, 'daextulmap_load_document' ) );
		add_action( 'wp_ajax_daextulmap_submit_markdown', array( $this, 'daextulmap_submit_markdown' ) );

		// Require and instantiate the class used to handle Front Matter.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulmap-front-matter.php';
		$this->front_matter = new Daextulmap_Front_Matter( $this->shared );
	}

	/**
	 * Return an instance of this class.
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
	 * Ajax handler used to retrieve the title and the content of the uploaded markdown file.
	 *
	 * This method is called when the "Import" button available in the post editor sidebar is clicked and then a markdown
	 * file is selected.
	 *
	 * @return void
	 */
	public function daextulmap_import_document() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daextulmap', 'security', false ) ) {
			echo esc_html__( 'Invalid AJAX Request', 'ultimate-markdown-pro' );
			die();
		}

		// Check the capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			echo esc_html__( 'Invalid Capability', 'ultimate-markdown-pro' );
			die();
		}

		if ( isset( $_FILES['uploaded_file'] ) ) {

			// Sanitize the uploaded file.
			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The sanitization is performed with sanitize_uploaded_file().
			$file_data = $this->shared->sanitize_uploaded_file( $_FILES['uploaded_file'] );

			// Get the file content.
			$document = $this->shared->get_markdown_file_content( $file_data );

			// Upload the referenced images to the media library and update the URLs.
			if ( intval( get_option( $this->shared->get( 'slug' ) . '_upload_referenced_images_import_markdown_ess' ), 10 ) === 1 ) {
				$document = $this->shared->replace_referenced_images_with_local_images( $document );
			}

			// Get the Front Matter data.
			$front_matter = $this->front_matter->get( $document );

			// if the title is set with the YAML data use it. Otherwise, obtain the title from the file name.
			$title = isset( $front_matter['title'] ) ?
				$front_matter['title'] :
				sanitize_text_field( $this->shared->get_title_from_file_name( $file_data['name'] ) );

			// Echo the JSON data associated with the Markdown document.
			$this->shared->generate_markdown_document_json( $title, $document, $front_matter );

		}

	}

	/**
	 *
	 * Ajax handler used to return the title and the content of the submitted document id.
	 *
	 * This method is called when the user selects a document with the selector available in the "Load Document"
	 * component available in the post sidebar of the editor.
	 *
	 * @return void
	 */
	public function daextulmap_load_document() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daextulmap', 'security', false ) ) {
			echo esc_html__( 'Invalid AJAX Request', 'ultimate-markdown-pro' );
			die();
		}

		// Check the capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			echo esc_html__( 'Invalid Capability', 'ultimate-markdown-pro' );
			die();
		}

		$document_id = isset( $_POST['document_id'] ) ? intval( $_POST['document_id'], 10 ) : 0;

		// Get the document object.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$document_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextulmap_document WHERE document_id = %d ", $document_id )
		);

		if ( false !== $document_obj ) {

			// Upload the referenced images to the media library and update the URLs.
			if ( intval( get_option( $this->shared->get( 'slug' ) . '_upload_referenced_images_load_markdown_ess' ), 10 ) === 1 ) {
				$content = $this->shared->replace_referenced_images_with_local_images( stripslashes( $document_obj->content ) );
			} else {
				$content = stripslashes( $document_obj->content );
			}

			// Get the Front Matter data.
			$front_matter = $this->front_matter->get( stripslashes( $content ) );

			// if the title is set with Front Matter use it. Otherwise, use the file name as title.
			$title = isset( $front_matter['title'] ) ? stripslashes( $front_matter['title'] ) : $document_obj->title;

			// Echo the JSON data associated with the Markdown document.
			$this->shared->generate_markdown_document_json( $title, $content, $front_matter );

		} else {

			die();

		}

	}

	/**
	 * Ajax handler used to return the title and the content of the submitted document.
	 *
	 * This method is called when the user clicks the "Submit Text" button of the "Load Document" sidebar section
	 * available in the post sidebar of the editor.
	 *
	 * @return void
	 */
	public function daextulmap_submit_markdown() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daextulmap', 'security', false ) ) {
			echo esc_html__( 'Invalid AJAX Request', 'ultimate-markdown-pro' );
			die();
		}

		// Check the capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			echo esc_html__( 'Invalid Capability', 'ultimate-markdown-pro' );
			die();
		}

		/**
		 * Get raw markdown content, without sanitize_textarea_field(). This is because sanitize_textarea_field() is not
		 * applicable to the Markdown content, as it would remove or alter some of the Markdown syntax.
		 */
		$content = isset( $_POST['markdowntext'] ) ? wp_unslash( $_POST['markdowntext'] ) : '';

		// Upload the referenced images to the media library and update the URLs.
		if ( intval( get_option( $this->shared->get( 'slug' ) . '_upload_referenced_images_submit_markdown_ess' ), 10 ) === 1 ) {
			$content = $this->shared->replace_referenced_images_with_local_images( $content );
		}

		// Get the Front Matter data.
		$front_matter = $this->front_matter->get( $content );

		// if the title is set with Front Matter use it. Otherwise, use the file name as title.
		$title = isset( $front_matter['title'] ) ? $front_matter['title'] : null;

		// Echo the JSON data associated with the Markdown document.
		$this->shared->generate_markdown_document_json( $title, $content, $front_matter );

	}
}
