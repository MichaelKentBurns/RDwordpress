<?php
/**
 * This class should be used to handle the export of the documents stored in the plugin.
 *
 * @package ultimate-markdown-pro
 */

/**
 * This class should be used to handle the export of the documents stored in the plugin.
 */
class Daextulmap_Export {

	/**
	 * The instance of the class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * An instance of the shared class.
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
	 * The constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextulmap_Shared::get_instance();

		// Export controller.
		add_action( 'init', array( $this, 'export_controller' ) );

		// Require and instantiate the class used to handle Front Matter.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulmap-front-matter.php';
		$this->front_matter = new Daextulmap_Front_Matter( $this->shared );
	}

	/**
	 * Get the singleton instance of the class.
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
	 * The click on the "Export" button available in the "Export" menu is intercepted and the
	 *  method that generates the downloadable ZIP file that includes all the markdown files generated from the
	 *  documents stored in the plugin.
	 *
	 * @return void
	 */
	public function export_controller() {

		/**
		 * Intercept requests that come from the "Export" button of the
		 * "Ultimate Markdown -> Export" menu and generate the downloadable ZIP file.
		 */
		if ( isset( $_POST['daextulmap_export'] ) ) {

			// Verify capability.
			if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_tools_menu_required_capability' ) ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ultimate-markdown-pro' ) );
			}

			// Get the type of export.
			$source = isset( $_POST['source'] ) ? intval( $_POST['source'], 10 ) : null;

			switch ( $source ) {

				case 0:
					// Generates a ZIP file that includes all the documents stored in the plugin.
					$this->export_documents();

					break;

				case 1:
					// Generates a ZIP file that includes the WordPress posts converted in Markdown format.
					$this->export_posts();

					break;

			}
		}

		/**
		 * Intercept requests that come from the "Export" button of the "Ultimate Markdown -> Documents" menu and
		 * generate the single Markdown document.
		 */

		if ( isset( $_POST['daextulmap_export_document_id'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextulmap_export_document_' . intval( $_POST['daextulmap_export_document_id'], 10 ), 'daextulmap_export_document_nonce', );

			// Verify capability.
			if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_documents_menu_required_capability' ) ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ultimate-markdown-pro' ) );
			}

			$document_id = intval( $_POST['daextulmap_export_document_id'], 10 );

			// Get the data from the 'connect' db.
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$document_obj = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextulmap_document WHERE document_id = %d ", $document_id )
			);

			if ( ! is_null( $document_obj ) ) {

				// Set the header.
				header( 'Content-Encoding: UTF-8' );
				header( 'Content-type: text/plain; charset=UTF-8' );
				header( 'Content-Disposition: attachment; filename=document-' . $document_obj->document_id . '.md' );
				header( 'Pragma: no-cache' );
				header( 'Expires: 0' );

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Not necessary for text files.
				echo stripslashes( $document_obj->content );

				die();

			}
		}
	}

	/**
	 * Generates a ZIP file that includes all the documents stored in the plugin.
	 *
	 * @return false|void
	 */
	private function export_documents() {

		// Get the data from the 'connect' db.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$document_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daextulmap_document ORDER BY document_id ASC", ARRAY_A );

		// Stripslash title and content in the document array.
		foreach ( $document_a as $key => $document ) {
			$document_a[ $key ]['title']   = stripslashes( $document['title'] );
			$document_a[ $key ]['content'] = stripslashes( $document['content'] );
		}

		// Create the zip archive.
		$this->generate_zip_with_markdown_files( $document_a );
	}

	/**
	 * Generates a ZIP file that includes the WordPress posts converted in Markdown format
	 */
	private function export_posts() {

		// Nonce verification.
		check_admin_referer( 'daextulmap_tools_export', 'daextulmap_tools_export_nonce' );

		$post_types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_key', $_POST['post_types'] ) : null;
		$categories = isset( $_POST['categories'] ) ? array_map( 'sanitize_key', $_POST['categories'] ) : null;
		$tags       = isset( $_POST['tags'] ) ? array_map( 'sanitize_key', $_POST['tags'] ) : null;

		// An instance of the HTML to Markdown parsers is created and saved as global variable.
		require_once $this->shared->get( 'dir' ) . 'globalize-html-converter-parser.php';

		// Globalize the HTML to Markdown parser.
		global $daextulmap_converter;

		$data_a = array();

		$args = array(
			'post_type'    => $post_types,
			'category__in' => $categories,
			'tag__in'      => $tags,
			'numberposts'  => -1,
		);

		$latest_posts = get_posts( $args );

		foreach ( $latest_posts as $post ) {

			/**
			 * Get the post title and the post content.
			 */
			$title        = $post->post_title;
			$html_content = $post->post_content;

			/**
			 * Convert HTML to Markdown. Note that the try/catch is used because the convert() method generates
			 * errors when the provided HTML is invalid. An example of content considered invalid is a post with
			 * HTML block comments and no HTML.
			 */
			try {

				$markdown_content = $daextulmap_converter->convert( $html_content );

				// Generates the Front Matter from the post data.
				$front_matter = $this->front_matter->generate_front_matter_from_post($post->ID);

				// If Front Matter is set, add it to the Markdown content.
				if ( ! empty( $front_matter ) ) {
					$markdown_content = $this->front_matter->set( $markdown_content, $front_matter );
				}

				$data_a[] = array(
					'title'   => $title,
					'content' => $markdown_content,
				);

			} catch ( Exception $e ) {

				$data_a[] = array(
					'title'   => $title,
					'content' => __( "This post can't be converted to Markdown. Reason: Invalid HTML in the post.", 'ultimate-markdown-pro' )
				);

			}
		}

		// Create the zip archive.
		$this->generate_zip_with_markdown_files( $data_a );
	}

	/**
	 * Generates a ZIP file with all the documents.
	 *
	 * @param array $document_a The array of documents.
	 */
	private function generate_zip_with_markdown_files( $document_a ) {

		// Export Documents -------------------------------------------------------------------------------------------.
		$written_file_names = array();
		$file_path         = WP_CONTENT_DIR . '/uploads/daextulmap_uploads/';

		// Create the upload directory of the plugin if the directory doesn't exist.
		wp_mkdir_p( $file_path );

		// If there are data iterate the array.
		if ( count( $document_a ) > 0 ) {

			// Renames the items with a duplicate name.
			$document_a = $this->shared->rename_duplicate_names_in_document( $document_a );

			global $wp_filesystem;
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();

			// Add all the files in the upload folder.
			foreach ( $document_a as $document ) {

				$file_name = sanitize_file_name( $document['title'] . '.' . get_option( $this->shared->get( 'slug' ) . '_exported_files_extension' ) );

				// Create the single markdown file in the plugin upload folder.
				$content = $document['content'];

				/**
				 * If a file with the same name already exists, add a suffix "[counter]" to the file name and try
				 * again.
				 */
				$counter = 0;
				while ( $wp_filesystem->exists( $file_path . '/' . $file_name ) ) {
					++$counter;
					$file_name = sanitize_file_name( $document['title'] ) . $counter .  '.md';
				}

				$wp_filesystem->put_contents( $file_path . '/' . $file_name, $content );
				$written_file_names[] = $file_name;

			}

			$creation_status = false;

			if ( class_exists( 'ZipArchive' ) ) {

				$archive_file_name = 'data-' . time() . '.zip';
				$content_type_header = 'application/zip';

				/**
				 * If it's available use the native PHP class ZipArchive.
				 *
				 * Ref: https://www.php.net/manual/en/class.ziparchive.php
				 */

				// Create and open a new zip archive.
				$zip = new ZipArchive();
				if ( $zip->open( $file_path . $archive_file_name, ZIPARCHIVE::CREATE ) !== true ) {

					// Cannot open the zip archive.
					$creation_status = false;

				}

				// Add each files of the $file_name array to the archive.
				foreach ( $written_file_names as $files ) {
					$zip->addFile( $file_path . $files, $files );
				}
				$zip->close();

				// Set creation status to true if everything succeeded.
				$creation_status = true;

			}

			// Use PclZip as the first fallback method.
			if(false === $creation_status){

				$archive_file_name = 'data-' . time() . '.zip';
				$content_type_header = 'application/zip';

				/**
				 * If the native PHP class ZipArchive is not available, use the PclZip library as a fallback.
				 *
				 * Note that the library is included in the WordPress. It's sufficient to include the file to use it.
				 */

				// Load the PclZip library.
				require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';

				// Create a new archive with PclZip.
				$archive = new PclZip( $file_path . $archive_file_name );

				// Prepare files to add to the archive.
				$files_to_add = array();
				foreach ( $written_file_names as $files ) {
					$files_to_add[] = $file_path . $files;
				}

				// Add files to the archive.
				if( 0 === $archive->create( $files_to_add, PCLZIP_OPT_REMOVE_PATH, $file_path ) ){

					// If the archive creation fails, return false.
					$creation_status = false;

				}else{

					$creation_status = true;

				}

			}

			// Use Phar as the second fallback method.
			if(false === $creation_status){

				$archive_file_name = 'data-' . time() . '.gz';
				$content_type_header = 'application/gzip';

				try {

					$temp_tar_file = $file_path . 'temp.tar';

					// Create a new PharData archive.
					$phar = new PharData($temp_tar_file);

					// Add each file from the $written_file_names array to the archive.
					foreach ($written_file_names as $files) {
						$phar->addFile($file_path . $files, $files);
					}

					// Compress the tar file to a gzip archive.
					$phar->compress(Phar::GZ);

					// Remove the uncompressed tar file to keep only the .tar.gz archive.
					unset($phar); // Close the PharData object before deletion.
					wp_delete_file($temp_tar_file);

					// Rename the compressed archive to the final name.
					$gz_file = $temp_tar_file . '.gz';
					$wp_filesystem->move( $gz_file, $file_path . $archive_file_name, true );

					// Archive created successfully.
					$creation_status = true;

				} catch (Exception $e) {

					// Error creating archive.
					$creation_status = false;

				}

			}

			if(file_exists($file_path . $archive_file_name)){

				// Generate the header of a zip file.
				header( 'Content-type: ' . $content_type_header );
				header( 'Content-Disposition: attachment; filename=' . $archive_file_name );
				header( 'Pragma: no-cache' );
				header( 'Expires: 0' );
				header('Content-Length: ' . filesize($file_path . $archive_file_name));

				global $wp_filesystem;
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Not necessary for zip files.
				echo $wp_filesystem->get_contents( $file_path . $archive_file_name );

				// Delete all the files used to create the archive.
				foreach ( $written_file_names as $written_file_name ) {
					wp_delete_file( $file_path . $written_file_name );
				}

				// Delete the archive file.
				wp_delete_file( $file_path . $archive_file_name );

			} else {
				return false;
			}

			die();

		}

	}

}
