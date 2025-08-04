<?php
/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 *
 * @package ultimate-markdown-pro
 */

/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 */
class Daextulmap_Tools_Menu_Elements extends Daextulmap_Menu_Elements {

	/**
	 * The class used to handle Front Matter.
	 *
	 * @var Daextulmap_Front_Matter The class used to handle Front Matter.
	 */
	private $front_matter = null;

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug      = 'tool';
		$this->slug_plural    = 'tools';
		$this->label_singular = __( 'Tool', 'ultimate-markdown-pro' );
		$this->label_plural   = __( 'Tools', 'ultimate-markdown-pro' );

		// Require and instantiate the class used to handle Front Matter.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulmap-front-matter.php';
		$this->front_matter = new Daextulmap_Front_Matter( $this->shared );

		// Require and instantiate the class used to handle the exports.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulmap-export.php';
		Daextulmap_Export::get_instance();
	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 *  1. Sanitization
	 *  2. Validation
	 *  3. Database update
	 *
	 * @return false|void
	 */
	public function process_form() {

		// Process the Markdown file upload (import) ------------------------------------------------------------------.
		if ( isset( $_FILES['file_to_upload'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextulmap_tools_import', 'daextulmap_tools_import_nonce' );

			if ( isset( $_POST['destination'] ) ) {
				$destination = intval( $_POST['destination'], 10 );

				if ( 0 === $destination ) {

					// Document destination.
					$generated_a   = array();
					$list_of_posts = array();

					// Process all the uploaded files.
					$num_files = isset( $_FILES['file_to_upload']['name'] ) ? count( $_FILES['file_to_upload']['name'] ) : 0;
					for ( $i = 0; $i < $num_files; $i++ ) {

						//phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- The sanitization is performed with sanitize_uploaded_file().
						$file_data = $this->shared->sanitize_uploaded_file(
							array(
								'name'     => $_FILES['file_to_upload']['name'][ $i ],
								'type'     => $_FILES['file_to_upload']['type'][ $i ],
								'tmp_name' => $_FILES['file_to_upload']['tmp_name'][ $i ],
								'error'    => $_FILES['file_to_upload']['error'][ $i ],
								'size'     => $_FILES['file_to_upload']['size'][ $i ],
							)
						);
						//phpcs:enable

						// Get the markdown content of the file.
						$markdown_content = $this->shared->get_markdown_file_content( $file_data );

						// Upload the referenced images to the media library and update the URLs.
						if ( intval( get_option( $this->shared->get( 'slug' ) . '_upload_referenced_images_import_menu' ), 10 ) === 1 ) {
							$markdown_content = $this->shared->replace_referenced_images_with_local_images( $markdown_content );
						}

						$title       = sanitize_text_field( $this->shared->get_title_from_file_name( $file_data['name'] ) );
						$category_id = intval( get_option( $this->shared->get( 'slug' ) . '_default_category' ), 10 );

						// Create a new record in the "document" database table created by the plugin -----------------.
						global $wpdb;

						// phpcs:disable WordPress.DB.DirectDatabaseQuery
						$query_result = $wpdb->query(
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

						if ( false !== $query_result ) {
							$list_of_posts[] = array(
								'id'    => $wpdb->insert_id,
								'title' => $title,
							);
						}
					}

					$dm_notice = __( 'The following documents have been generated:', 'ultimate-markdown-pro' ) . ' ';
					foreach ( $list_of_posts as $key => $post ) {
						$dm_notice .= $post['title'];
						if ( $key < count( $list_of_posts ) - 1 ) {
							$dm_notice .= ', ';
						} else {
							$dm_notice .= '.';
						}
					}
				} else {

					// Post destination.

					// Process the Markdown file upload.

					$list_of_posts = array();

					// Process all the uploaded files.
					$num_files = isset( $_FILES['file_to_upload']['name'] ) ? count( $_FILES['file_to_upload']['name'] ) : 0;
					for ( $i = 0; $i < $num_files; $i++ ) {

						//phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- The sanitization is performed with sanitize_uploaded_file().
						$file_data = $this->shared->sanitize_uploaded_file(
							array(
								'name'     => $_FILES['file_to_upload']['name'][ $i ],
								'type'     => $_FILES['file_to_upload']['type'][ $i ],
								'tmp_name' => $_FILES['file_to_upload']['tmp_name'][ $i ],
								'error'    => $_FILES['file_to_upload']['error'][ $i ],
								'size'     => $_FILES['file_to_upload']['size'][ $i ],
							)
						);

						// Get the markdown content of the file.
						$markdown_content = $this->shared->get_markdown_file_content( $file_data );

						// Upload the referenced images to the media library and update the URLs.
						if ( intval( get_option( $this->shared->get( 'slug' ) . '_upload_referenced_images_import_classic_menu' ), 10 ) === 1 ) {
							$markdown_content = $this->shared->replace_referenced_images_with_local_images( $markdown_content );
						}

						// Get the Front Matter fields available in the string.
						$front_matter = $this->front_matter->get( $markdown_content );

						// if the title is set with Front Matter use it. Otherwise, use the file name as title.
						$title = isset( $front_matter['title'] ) ?
							$front_matter['title'] :
							sanitize_text_field( $this->shared->get_title_from_file_name( $file_data['name'] ) );

						// Create a post for the Classic editor.
						$new_post_id = $this->shared->create_classic_post( $title, $markdown_content, $front_matter, 'bulk_import' );

						if ( 0 !== $new_post_id && ! is_wp_error( $new_post_id ) ) {

							$list_of_posts[] = array(
								'id'    => $new_post_id,
								'title' => $title,
							);

						}
					}

					$dm_notice = __( 'The following posts have been successfully created:', 'ultimate-markdown-pro' ) . ' ';
					foreach ( $list_of_posts as $key => $post ) {
						$dm_notice .= $post['title'];
						if ( $key < count( $list_of_posts ) - 1 ) {
							$dm_notice .= ', ';
						} else {
							$dm_notice .= '.';
						}
					}
				}
			}

			$this->shared->save_dismissible_notice(
				$dm_notice,
				'updated'
			);

		}

	}

	/**
	 * Display the form.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div class="daextulmap-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->display_dismissible_notices();

			// Display the license activation notice.
			$this->shared->display_license_activation_notice();

			?>

			<div class="daextulmap-tools-menu">

				<div class="daextulmap-main-form">

					<div class="daextulmap-main-form__wrapper-half">



						<!-- Import form -->

						<div class="daextulmap-main-form__daext-form-section">

							<div class="daextulmap-main-form__section-header">
								<div class="daextulmap-main-form__section-header-title">
									<?php $this->shared->echo_icon_svg( 'log-in-04' ); ?>
									<div class="daextulmap-main-form__section-header-title-text"><?php esc_html_e( 'Import', 'ultimate-markdown-pro' ); ?></div>
								</div>
							</div>

							<form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form daextulmap-tools-menu__import-upload-form"
							      action="admin.php?page=daextulmap-<?php echo esc_attr( $this->slug_plural ); ?>">

							<div class="daextulmap-main-form__daext-form-section-body">


									<?php
									wp_nonce_field( 'daextulmap_tools_import', 'daextulmap_tools_import_nonce' );
									?>

									<!-- Destination -->
									<div class="daextulmap-main-form__daext-form-field">
										<div>
											<label for="source"><?php esc_html_e( 'Destination', 'ultimate-markdown-pro' ); ?></label>
										</div>

										<div>
											<select id="destination" name="destination" class="daext-display-none">
												<option value="0"><?php esc_html_e( 'Documents', 'ultimate-markdown-pro' ); ?></option>
												<option value="1"><?php esc_html_e( 'Posts', 'ultimate-markdown-pro' ); ?></option>
											</select>
											<div class="help-icon"
												title='<?php esc_attr_e( 'todo.', 'ultimate-markdown-pro' ); ?>'></div>
										</div>
									</div>


										<div class="daextulmap-main-form__daext-form-field">
											<div class="daextulmap-input-wrapper">
												<label for="upload"
														class="custom-file-upload"><?php esc_html_e( 'Choose file', 'ultimate-markdown-pro' ); ?></label>
												<div class="custom-file-upload-text"
													id="upload-text"><?php esc_html_e( 'No file chosen', 'ultimate-markdown-pro' ); ?></div>
												<input type="file" id="upload" name="file_to_upload[]"
														accept=".md,.markdown,.mdown,.mkdn,.mkd,.mdwn,.mdtxt,.mdtext,.text,.txt" multiple
														class="custom-file-upload-input">
											</div>
										</div>

										<div>
											<input type="submit" name="submit" id="submit"
													class="daextulmap-btn daextulmap-btn-primary"
													value="<?php esc_attr_e( 'Upload files and import', 'ultimate-markdown-pro' ); ?>">
										</div>
								</form>


							</div>

							</form>

						</div>



						<!-- the data sent through this form are handled by the export_xml_controller() method called with the WordPress init action -->

							<div class="daextulmap-main-form__daext-form-section">

								<div class="daextulmap-main-form__section-header">
									<div class="daextulmap-main-form__section-header-title">
										<?php $this->shared->echo_icon_svg( 'log-out-04' ); ?>
										<div class="daextulmap-main-form__section-header-title-text"><?php esc_html_e( 'Export', 'ultimate-markdown-pro' ); ?></div>
									</div>
								</div>

								<form method="POST"
								      action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $this->slug_plural ); ?>" class="daextulmap-tools-menu__export-form">

								<div class="daextulmap-main-form__daext-form-section-body">

									<!-- Export form -->

									<!-- Source -->
									<div class="daextulmap-main-form__daext-form-field">
										<div>
											<label for="source"><?php esc_html_e( 'Source', 'ultimate-markdown-pro' ); ?></label>
										</div>

										<div>
											<select id="source" name="source" class="daext-display-none">
												<option value="0"><?php esc_html_e( 'Documents', 'ultimate-markdown-pro' ); ?></option>
												<option value="1"><?php esc_html_e( 'Posts', 'ultimate-markdown-pro' ); ?></option>
											</select>
											<div class="help-icon"
												title='<?php esc_attr_e( 'The source of the export. Select "Documents" to export all the Markdown documents stored in the plugin or "Posts" to export all the WordPress posts that meet the criteria defined in the "Advanced" section.', 'ultimate-markdown-pro' ); ?>'></div>
										</div>
									</div>

									<!-- Post Types -->
									<div class="daextulmap-main-form__daext-form-field daextulmap-main-form__selection-dependent-posts daext-display-none">
										<div>
											<label for="post-types"><?php esc_html_e( 'Post Types', 'ultimate-markdown-pro' ); ?></label>
										</div>
										<div>
											<?php

											$defaults_post_types_a = array( 'post', 'page' );

											$available_post_types_a = get_post_types(
												array(
													'public'  => true,
													'show_ui' => true,
												)
											);

											// Remove the "attachment" post type.
											$available_post_types_a = array_diff( $available_post_types_a, array( 'attachment' ) );

											echo '<select id="post-types" name="post_types[]" class="daext-display-none" multiple>';

											foreach ( $available_post_types_a as $single_post_type ) {
												if ( is_array( $defaults_post_types_a ) && in_array( $single_post_type, $defaults_post_types_a, true ) ) {
													$selected = 'selected';
												} else {
													$selected = '';
												}
												$post_type_obj = get_post_type_object( $single_post_type );
												echo '<option value="' . esc_attr( $single_post_type ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $post_type_obj->label ) . '</option>';
											}

											echo '</select>';
											echo '<div class="help-icon" title="' . esc_attr__(
												'With this option you are able to determine which post types will be included in the export. Note that this option will be considered only if the source of the export is equal to "Posts".',
												'ultimate-markdown-pro'
											) . '"></div>';

											?>
										</div>
									</div>

									<!-- Categories -->
									<div class="daextulmap-main-form__daext-form-field daextulmap-main-form__selection-dependent-posts daext-display-none">
										<label for="categories"><?php esc_html_e( 'Categories', 'ultimate-markdown-pro' ); ?></label>
										<div>
											<?php

											$defaults_categories_a = array();

											echo '<select id="categories" name="categories[]" class="daext-display-none" multiple>';

											$categories = get_categories(
												array(
													'hide_empty' => 0,
													'orderby' => 'term_id',
													'order'   => 'DESC',
												)
											);

											foreach ( $categories as $category ) {
												if ( is_array( $defaults_categories_a ) && in_array( $category->term_id, $defaults_categories_a, true ) ) {
													$selected = 'selected';
												} else {
													$selected = '';
												}
												echo '<option value="' . esc_attr( $category->term_id ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $category->name ) . '</option>';
											}

											echo '</select>';
											echo '<div class="help-icon" title="' . esc_attr__(
												'With this option you are able to determine which categories will be included in the export. Note that this option will be considered only if the source of the export is equal to "Posts".',
												'ultimate-markdown-pro'
											) . '"></div>';

											?>
										</div>
									</div>

									<!-- Tags -->
									<div class="daextulmap-main-form__daext-form-field daextulmap-main-form__selection-dependent-posts daext-display-none">
										<label for="tags">
											<?php esc_html_e( 'Tags', 'ultimate-markdown-pro' ); ?>
										</label>
										<div>
											<?php

											$defaults_tags_a = array();

											echo '<select id="tags" name="tags[]" class="daext-display-none" multiple>';

											$categories = get_categories(
												array(
													'hide_empty' => 0,
													'orderby'  => 'term_id',
													'order'    => 'DESC',
													'taxonomy' => 'post_tag',
												)
											);

											foreach ( $categories as $category ) {
												if ( is_array( $defaults_tags_a ) && in_array( $category->term_id, $defaults_tags_a, true ) ) {
													$selected = 'selected';
												} else {
													$selected = '';
												}
												echo '<option value="' . esc_attr( $category->term_id ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $category->name ) . '</option>';
											}

											echo '</select>';
											echo '<div class="help-icon" title="' . esc_attr__(
												'With this option you are able to determine which tags will be included in the export. Note that this option will be considered only if the source of the export is equal to "Posts".',
												'ultimate-markdown-pro'
											) . '"></div>';

											?>
										</div>
									</div>

									<div class="daext-widget-submit">
										<input name="daextulmap_export"
												class="daextulmap-btn daextulmap-btn-primary" type="submit"
												value="<?php esc_attr_e( 'Export', 'ultimate-markdown-pro' ); ?>"
											<?php
											if ( ! $this->shared->exportable_data_exists() ) {
												echo 'disabled="disabled"';
											}
											?>
										>
									</div>

									<?php wp_nonce_field( 'daextulmap_tools_export', 'daextulmap_tools_export_nonce' ); ?>

								</div>

								</form>

							</div>

						</form>

					</div>

				</div>

			</div>

		</div>

		<?php
	}
}
