<?php
/**
 * Parent class used to create the admin pages.
 *
 * @package ultimate-markdown-pro
 */

/**
 * Parent class used to create the admin pages.
 */
class Daextulmap_Menu_Elements {

	/**
	 * The capability required to access the menu.
	 *
	 * @var string
	 */
	public $capability = null;

	/**
	 * The context of the menu.
	 *
	 * @var string
	 */
	public $context = null;

	/**
	 * Array with general menu data, like toolbar menu items.
	 *
	 * @var self
	 */
	public $config = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextulmap_Shared
	 */
	public $shared = null;

	/**
	 * The menu slug.
	 *
	 * @var null
	 */
	public $menu_slug = null;

	/**
	 * The plural version of the slug.
	 *
	 * @var null
	 */
	public $slug_plural = null;

	/**
	 * The singular version of the displayed menu label.
	 *
	 * @var null
	 */
	public $label_singular = null;

	/**
	 * The plural version of the displayed menu label.
	 *
	 * @var null
	 */
	public $label_plural = null;

	/**
	 * The primary key of the database table associated with the managed back-end page.
	 *
	 * @var null
	 */
	public $primary_key = null;

	/**
	 * The name of the database table associated with the managed back-end page.
	 *
	 * @var null
	 */
	public $db_table = null;

	/**
	 * The list of columns to display in the table.
	 *
	 * @var null
	 */
	public $list_table_columns = null;

	/**
	 * The list of database table fields that can be searched using the menu search field.
	 *
	 * @var null
	 */
	public $searchable_fields = null;

	/**
	 * The default values of the echoed form fields.
	 *
	 * @var null
	 */
	public $default_values = null;

	/**
	 * The instance of the class.
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * The constructor.
	 *
	 * @param Daextulmap_Shared $shared An instance of the shared class.
	 * @param string            $page_query_param The query parameter used to identify the current page.
	 * @param array             $config The configuration array.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		// assign an instance of the plugin info.
		$this->shared = $shared;

		$this->config = $config;

		add_action( 'admin_init', array( $this, 'handle_duplicate' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_delete' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_bulk_actions' ), 10 );

		// check if this instance has the method "process_form".
		if ( method_exists( $this, 'process_form' ) ) {
			add_action( 'admin_init', array( $this, 'process_form' ), 10 );
		}
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
	 * Generate the table with the list of items for the provided database table.
	 *
	 * @param string $db_table The name of the database table.
	 * @param array  $columns The list of columns to display in the table.
	 * @param array  $searchable_fields The list of database table fields that can be searched using the menu search field.
	 * @param string $item_name_plural The plural version of the displayed menu label.
	 * @param string $page_slug The slug of the menu page.
	 * @param string $db_primary_key The primary key of the database table associated with the managed back-end page.
	 *
	 * @return void
	 */
	public function display_list_table(
		$db_table,
		$columns,
		$searchable_fields,
		$item_name_plural,
		$page_slug,
		$db_primary_key
	) {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce not required for data visualization.
		$post_search_input = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : null;

		// Table -----------------------------------------------------------------------------------------------.

			global $wpdb;

			$filter = '';

			// create the query part used to filter the results when a search is performed.
		if ( ! is_null( $post_search_input ) && mb_strlen( trim( $post_search_input ) ) > 0 ) {

			foreach ( $searchable_fields as $key => $searchable_field ) {

				if ( 0 === $key ) {
					$filter .= 'WHERE (';
				} else {
					$filter .= ' OR ';
				}

				// Sanitize the field name.
				$searchable_field = sanitize_key( $searchable_field );

				// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $searchable_field is sanitized above.
				$filter .= $wpdb->prepare(
					$searchable_field . ' LIKE %s',
					'%' . $post_search_input . '%'
				);

				if ( count( $searchable_fields ) - 1 === $key ) {
					$filter .= ')';
				}
			}
		}

			// Retrieve the total number of items.

			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is already prepared.
			$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daextulmap_$db_table $filter" );

			// Initialize the pagination class.
			require_once $this->shared->get( 'dir' ) . '/admin/inc/class-daextulmap-pagination.php';
			$pag = new Daextulmap_Pagination( $this->shared );
			$pag->set_total_items( $total_items );// Set the total number of items.
			$pag->set_record_per_page( 10 ); // Set records per page.
			$pag->set_target_page( 'admin.php?page=' . $this->shared->get( 'slug' ) . '-' . $page_slug );// Set target page.
			$pag->set_current_page();// set the current page number from $_GET.

		?>

			<!-- Query the database -->
			<?php
			$query_limit = $pag->query_limit();

			// Sanitize the field name.
			$db_primary_key = sanitize_key( $db_primary_key );

			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$results = $wpdb->get_results(
				"SELECT * FROM {$wpdb->prefix}daextulmap_$db_table $filter ORDER BY $db_primary_key DESC $query_limit",
				ARRAY_A
			);
			// phpcs:enable

			?>

			<?php if ( count( $results ) > 0 ) : ?>

				<?php $this->display_crud_menu_search_form( $post_search_input ); ?>

				<input type="hidden" name="items-filter" value="1">

				<div class="daextulmap-crud-table">

					<!-- list of tables -->
					<table class="daextulmap-crud-table__daext-items">
						<thead>
						<tr>
							<th>
								<input type="checkbox" class="daextulmap-cb-select-all">
							</th>
							<?php
							foreach ( $columns as $column ) {
								?>
								<th>
									<div><?php echo esc_html( $column['label'] ); ?></div>
									<div class="help-icon"></div>
								</th>
								<?php
							}
							?>
						</tr>
						</thead>
						<tbody>
						<?php foreach ( $results as $result ) : ?>
							<tr>
								<td><input type="checkbox"
											class="daextulmap-bulk-action-checkbox"
											id="cb-select-<?php echo intval( $result[ $db_primary_key ], 10 ); ?>"
											value="<?php echo intval( $result[ $db_primary_key ], 10 ); ?>" name="post[]"></td>
								<?php

								foreach ( $columns as $key => $column ) {
									?>
									<td>

										<?php if ( 0 === $key ) : ?>
											<a class="daextulmap-crud-table__item-name" href="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>&edit_id=<?php echo esc_attr( $result[ $db_primary_key ] ); ?>">
												<?php echo esc_html( stripslashes( $result[ $column['db_field'] ] ) ); ?>
											</a>
											<div class="daextulmap-crud-table__row-actions">
											<div class="daextulmap-crud-table__row-actions-single-action">
												<a href="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>&edit_id=<?php echo esc_attr( $result[ $db_primary_key ] ); ?>">Edit</a>
											</div>
											<div>&nbsp|&nbsp</div>
											<div class="daextulmap-crud-table__row-actions-single-action">
												<form method="POST"
													action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>"
													id="clone-item-<?php echo esc_attr( $result[ $db_primary_key ] ); ?>">
													<?php wp_nonce_field( 'daextulmap_clone_' . $this->menu_slug . '_' . intval( $result[ $db_primary_key ], 10 ), 'daextulmap_clone_' . $this->menu_slug . '_nonce' ); ?>
													<button type="submit" name="clone_id" value="<?php echo esc_html( $result[ $db_primary_key ] ); ?>">Duplicate</button>
												</form>
											</div>
											<div>&nbsp|&nbsp</div>
											<?php if ( 'document' === $this->menu_slug ) : ?>
												<div class="daextulmap-crud-table__row-actions-single-action">
													<form method="POST"
															action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>"
															id="clone-item-<?php echo esc_attr( $result[ $db_primary_key ] ); ?>">
														<?php wp_nonce_field( 'daextulmap_export_' . $this->menu_slug . '_' . intval( $result[ $db_primary_key ], 10 ), 'daextulmap_export_' . $this->menu_slug . '_nonce' ); ?>
														<input type="hidden" name="daextulmap_export_document_id" value="<?php echo esc_html( $result[ $db_primary_key ] ); ?>">
														<button type="submit"><?php esc_html_e( 'Export', 'ultimate-markdown-pro' ); ?></button>
													</form>
												</div>
												<div>&nbsp|&nbsp</div>
											<?php endif; ?>
											<div class="daextulmap-crud-table__row-actions-single-action daextulmap-crud-table__row-actions-single-action-delete">
												<form method="POST"
														action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>"
														id="delete-item-<?php echo esc_attr( $result[ $db_primary_key ] ); ?>">
													<?php wp_nonce_field( 'daextulmap_delete_' . $this->menu_slug . '_' . intval( $result[ $db_primary_key ], 10 ), 'daextulmap_clone_' . $this->menu_slug . '_nonce' ); ?>
													<input type="hidden" name="delete_id" value="<?php echo esc_html( $result[ $db_primary_key ] ); ?>">
													<button type="submit" value="<?php echo esc_html( $result[ $db_primary_key ] ); ?>"><?php esc_html_e( 'Delete', 'ultimate-markdown-pro' ); ?></button>
												</form>
											</div>
										</div>

										<?php else : ?>
											<?php

											// Custom output function.
											if ( isset( $column['custom_output_function'] ) ) {
												$this->shared->{$column['custom_output_function']}( $result[ $db_primary_key ] );
											} elseif ( isset( $column['prepare_displayed_value'] ) && null !== $column['prepare_displayed_value'] ) {
												echo esc_html( $column['prepare_displayed_value']( $result[ $column['db_field'] ] ) );
											} else {
												echo esc_html( stripslashes( $result[ $column['db_field'] ] ) );
											}

											?>
										<?php endif; ?>
									</td>
									<?php
								}

								?>
							</tr>
						<?php endforeach; ?>

						</tbody>

						<tfoot>
						<tr>
							<th>
								<input type="checkbox" class="daextulmap-cb-select-all">
							</th>
							<?php
							foreach ( $columns as $column ) {
								?>
								<th>
									<div><?php echo esc_html( $column['label'] ); ?></div>
									<div class="help-icon"></div>
								</th>
								<?php
							}
							?>
						</tr>
						</tfoot>

					</table>

				</div>

				<!-- Bulk Actions -->
				<div class="daextulmap-crud-table-controls">

					<div class="daextulmap-crud-table-controls__bulk-actions">
						<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $this->slug_plural ); ?>">
							<select name="bulk_action" id="bulk_action">
								<option value=""><?php esc_html_e( 'Bulk actions', 'ultimate-markdown-pro' ); ?></option>
								<option value="delete"><?php esc_html_e( 'Delete', 'ultimate-markdown-pro' ); ?></option>
							</select>
							<?php wp_nonce_field( 'daextulmap_bulk_action_' . $this->menu_slug, 'daextulmap_bulk_action_' . $this->menu_slug . '_nonce' ); ?>
							<input id="bulk-action-selected-items" type="hidden" name="bulk-action-selected-items" value="">
							<input id="daextulmap-submit-bulk-action" type="submit" class="button daextulmap-admin-page-button" value="<?php esc_html_e( 'Apply', 'ultimate-markdown-pro' ); ?>">
						</form>
					</div>

					<div class="daextulmap-crud-table-controls__pagination-container">
						<!-- Display the pagination -->
						<?php if ( $pag->total_items > 0 ) : ?>
							<div class="daextulmap-crud-table-controls__daext-tablenav">
									<span class="daextulmap-crud-table-controls__daext-displaying-num"><?php echo esc_html( $pag->total_items ); ?>&nbsp<?php esc_html_e( 'items', 'ultimate-markdown-pro' ); ?></span>
									<?php $pag->show(); ?>
							</div>
						<?php endif; ?>
					</div>

				</div>

			<?php else : ?>

				<?php

				if ( mb_strlen( trim( $filter ) ) > 0 ) {
					$this->shared->save_dismissible_notice(
						__( 'There are no results that match your filter.', 'ultimate-markdown-pro' ),
						'updated'
					);
				}

				// Display the dismissible notices.
				$this->shared->display_dismissible_notices();

				// Display the search form of the CRUD menu.
				$this->display_crud_menu_search_form( $post_search_input );

				// If the filters are not applied and there are no items, display a message.
				if ( 0 === mb_strlen( trim( $filter ) ) ) {
					echo '<div class="daextulmap-crud-table__no-items-found-message">' . esc_html__( 'Nothing to show yet! Add some items by clicking the Add New button.', 'ultimate-markdown-pro' ) . '</div>';
				}

				?>

			<?php endif; ?>

			<div>

		</div>

		<?php
	}

	/**
	 * Display the header bar.
	 *
	 * @return void
	 */
	public function header_bar() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce not required for data visualization.
		$action  = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
		$edit_id = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : null;
		// phpcs:enable

		if ( 'new' === $action ) {
			$page_title = __( 'Add New', 'ultimate-markdown-pro' ) . ' ' . $this->label_singular;
		} elseif ( null !== $edit_id ) {
			$page_title = __( 'Edit', 'ultimate-markdown-pro' ) . ' ' . $this->label_singular;
		} else {
			$page_title = $this->label_plural;
		}

		?>

		<div class="daextulmap-header-bar">

			<div class="daextulmap-header-bar__left">
				<div class="daextulmap-header-bar__page-title"><?php echo esc_html( $page_title ); ?></div>
				<?php if ( 'list' === $action && 'crud' === $this->context && null === $edit_id ) : ?>
					<a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=daextulmap-' . $this->slug_plural . '&action=new' ); ?>"
						class="daextulmap-button daextulmap-header-bar__add-new-button">
						<?php $this->shared->echo_icon_svg( 'plus' ); ?>
						<div class="daextulmap-header-bar__add-new-button-text"><?php esc_html_e( 'Add New', 'ultimate-markdown-pro' ); ?></div>
					</a>
				<?php endif; ?>
			</div>

			<div class="daextulmap-header-bar__right">
				<?php if ( 'new' === $action || null !== $edit_id ) : ?>
					<a href="#" onclick="document.getElementById('form1').submit()" class="daextulmap-btn daextulmap-btn-primary"><?php esc_html_e( 'Save Changes', 'ultimate-markdown-pro' ); ?></a>
				<?php endif; ?>
			</div>

		</div>

		<?php
	}

	/**
	 * Generate a form for the creation of a new item.
	 */
	public function new_item() {

		?>

		<div class="daextulmap-main-form">

			<form id="form1" name="form1" method="POST"
					action="admin.php?page=daextulmap-<?php echo esc_attr( $this->slug_plural ); ?>"
					autocomplete="off">

				<input type="hidden" value="1" name="form_submitted">

				<?php

				wp_nonce_field( 'daextulmap_create_update_' . $this->menu_slug, 'daextulmap_create_update_' . $this->menu_slug . '_nonce' );

				$this->print_form_fields( $this->default_values );

				?>

			</form>

		</div>

		<?php
	}

	/**
	 * Generate an edit form for the provided database table.
	 *
	 * @param int $edit_id The id of the edited item.
	 *
	 * @return void
	 */
	public function edit_item( $edit_id ) {

		?>

		<div class="daextulmap-main-form">

			<form id="form1" name="form1" method="POST"
					action="admin.php?page=daextulmap-<?php echo esc_attr( $this->slug_plural ); ?>"
					autocomplete="off">

				<input type="hidden" value="1" name="form_submitted">

				<?php

				// Get the object from the db table using $wpdb.
				global $wpdb;

				// Sanitize the field name.
				$db_table_name = $wpdb->prefix . 'daextulmap_' . $this->db_table;

				// Sanitize the field name.
				$primary_key = sanitize_key( $this->primary_key );

				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $db_table_name and $primary_key are sanitized above.
				$item_obj = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM $db_table_name WHERE $primary_key = %d",
						$edit_id
					),
					ARRAY_A
				);
				// phpcs:enable

				wp_nonce_field( 'daextulmap_create_update_' . $this->menu_slug, 'daextulmap_create_update_' . $this->menu_slug . '_nonce' );

				echo '<input type="hidden" name="update_id" value="' . esc_attr( $item_obj[ $this->primary_key ] ) . '"/>';

				$this->print_form_fields( $item_obj );

				?>

			</form>

		</div>

		<?php
	}

	/**
	 * Display the header of a section of the menu. The header includes the section name and a toggle to open and close
	 * the caption.
	 *
	 * @param string $label The displayed name of the section.
	 * @param string $section_id The alphanumeric id of the section.
	 * @param string $icon_id The id of the icon to display.
	 *
	 * @return void
	 */
	public function section_header( $label, $section_id, $icon_id = null ) {

		?>

		<div class="daextulmap-main-form__section-header group-trigger" data-trigger-target="<?php echo esc_attr( $section_id ); ?>">
			<div class="daextulmap-main-form__section-header-title">
				<?php $this->shared->echo_icon_svg( $icon_id ); ?>
				<div class="daextulmap-main-form__section-header-title-text"><?php echo esc_html( $label ); ?></div>
			</div>
			<div class="daextulmap-main-form__section-header-toggle">
				<?php $this->shared->echo_icon_svg( 'chevron-down' ); ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Generate an HTML input field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $placeholder The placeholder of the field.
	 * @param string $value The value of the field.
	 * @param bool   $required If the field is required.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function input_field(
		$name = '',
		$label = '',
		$description = '',
		$placeholder = '',
		$value = null,
		$maxlength = null,
		$required = false,
		$section_id = null
	) {

		?>

		<div class="daextulmap-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?><?php echo $required ? ' <span class="daextulmap-required">*</span>' : ''; ?></label></div>
			<div>
				<input type="text" id="<?php echo esc_attr( $name ); ?>" maxlength="<?php echo esc_attr( $maxlength ); ?>" size="30"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
					<?php
					if ( ! is_null( $value ) ) {
						echo 'value="' . esc_attr( $value ) . '"';
					}
					?>
					/>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML textarea field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $placeholder The placeholder of the field.
	 * @param string $value The value of the field.
	 * @param bool   $required If the field is required.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function textarea_field(
		$name = '',
		$label = '',
		$description = '',
		$placeholder = '',
		$value = null,
		$maxlength = null,
		$required = false,
		$section_id = null
	) {

		?>

		<div class="daextulmap-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?><?php echo $required ? ' <span class="daextulmap-required">*</span>' : ''; ?></label></div>
			<div class="daextulmap-markdown-editor">
				<div id="editor-container" class="daextulmap-markdown-editor__editor-container">
					<textarea type="text" id="<?php echo esc_attr( $name ); ?>" maxlength="<?php echo esc_attr( $maxlength ); ?>" size="30"
							placeholder="<?php echo esc_attr( $placeholder ); ?>"
							name="<?php echo esc_attr( $name ); ?>"
					        class="daextulmap-markdown-editor__textarea-content"
					/><?php if ( ! is_null( $value ) ) { echo esc_html( $value ); } ?></textarea>
					<div id="editor-render-container" class="daextulmap-markdown-editor__editor-render-container">
						<div class="daextulmap-markdown-editor__editor-render-header">
							<div class="daextulmap-markdown-editor__editor-render-title">
								<div class="daextulmap-markdown-editor__editor-render-title-label"><?php esc_html_e('Preview', 'ultimate-markdown-pro'); ?></div>
								<div id="parser-indicator" class="daextulmap-markdown-editor__editor-render-parser-name"></div>
							</div>
							<button id="refresh-php-preview" class="daextulmap-markdown-editor__refresh-php-preview">
								<?php $this->shared->echo_icon_svg( 'refresh-cw-01' ); ?>
							</button>
						</div>
						<div id="editor-render" class="daextulmap-markdown-editor__editor-render"></div>
					</div>
				</div>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML select field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $options The options of the field.
	 * @param string $value The value of the field.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function select_field(
		$name = '',
		$label = '',
		$description = '',
		$options = null,
		$value = null,
		$section_id = null
	) {

		?>

		<div class="daextulmap-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?></label></div>
			<div>
				<select id="<?php echo esc_attr( $name ); ?>"
						name="<?php echo esc_attr( $name ); ?>">
					<?php
					foreach ( $options as $key => $option ) {

						/**
						 * Convert the key and value to integers if they are numeric. This prevents data types
						 * comparison issues in the next if statement that should use the identical operator.
						 */
						if ( is_numeric( $key ) && is_numeric( $value ) ) {
							$key   = intval( $key, 10 );
							$value = intval( $value, 10 );
						}

						echo '<option value="' . esc_attr( $key ) . '"';
						if ( $value === $key ) {
							echo 'selected';}
						echo '>' . esc_html( $option ) . '</option>';
					}
					?>
				</select>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML select multiple field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $options The options of the field.
	 * @param string $value The value of the field.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function select_multiple_field(
		$name = '',
		$label = '',
		$description = '',
		$options = null,
		$value = null,
		$section_id = null
	) {

		$value_a = maybe_unserialize( $value );

		?>

		<div class="daextulmap-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?></label></div>
			<div>
				<select id="<?php echo esc_attr( $name ); ?>"
						name="<?php echo esc_attr( $name ); ?>[]" multiple>
					<?php
					foreach ( $options as $key => $option ) {

						/**
						 * Convert the key and value to integers if they are numeric. This prevents data types
						 * comparison issues in the next if statement that should use the identical operator.
						 */
						if ( is_array( $value_a ) ) {
							foreach ( $value_a as $value_a_value ) {
								if ( is_numeric( $value_a_value ) && is_numeric( $key ) ) {
									$value_a_value = intval( $value_a_value, 10 );
									$key           = intval( $key, 10 );
								}
								if ( $value_a_value === $key ) {
									$selected = 'selected';
									break;
								} else {
									$selected = '';
								}
							}
						} else {
							$selected = '';
						}

						echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected );
						echo '>' . esc_html( $option ) . '</option>';
					}
					?>
				</select>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML input field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $value The value of the field.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function toggle_field(
		$name = '',
		$label = '',
		$description = '',
		$value = null,
		$section_id = null
	) {

		?>

		<div class="daextulmap-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div class="switch-container">
				<div class="switch-left">
					<label class="switch">
						<input id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" type="checkbox" <?php checked( intval( $value, 10 ), 1 ); ?>>
						<span class="slider round"></span>
					</label>
				</div>
				<div class="switch-right">
					<div><label for="title"><?php echo esc_html( $label ); ?></label></div>
				</div>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML input field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $value The value of the field.
	 * @param string $section_id The id of the section.
	 * @param string $min The minimum value of the range.
	 * @param string $max The maximum value of the range.
	 *
	 * @return void
	 */
	public function input_range_field(
		$name = '',
		$label = '',
		$description = '',
		$value = null,
		$section_id = null,
		$min = null,
		$max = null
	) {

		?>

		<div class="daextulmap-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?></label></div>
			<div>
				<input
						type="range"
						id="<?php echo esc_attr( $name ); ?>"
						maxlength="100"
						size="30"
						name="<?php echo esc_attr( $name ); ?>"
						min="<?php echo esc_attr( $min ); ?>"
						max="<?php echo esc_attr( $max ); ?>"
						data-range-sync-id="<?php echo esc_attr( $name ); ?>"
					<?php
					if ( ! is_null( $value ) ) {
						echo 'value="' . esc_attr( $value ) . '"';
					}
					?>
				/>
				<input
						class="inputNumber"
						type="number"
						min="<?php echo esc_attr( $min ); ?>"
						max="<?php echo esc_attr( $max ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						data-range-sync-id="<?php echo esc_attr( $name ); ?>"
				/>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Display the admin toolbar. Which is the top section of the plugin admin menus.
	 *
	 * @return void
	 */
	public function display_admin_toolbar() {

		?>

		<div class="daextulmap-admin-toolbar">
			<div class="daextulmap-admin-toolbar__left-section">
				<div class="daextulmap-admin-toolbar__menu-items">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=daextulmap-documents' ) ); ?>" class="daextulmap-admin-toolbar__plugin-logo">
						<img src="<?php echo esc_url( $this->shared->get( 'url' ) . 'admin/assets/img/plugin-logo.svg' ); ?>" alt="<?php esc_attr_e( 'Ultimate Markdown', 'ultimate-markdown-pro' ); ?>" />
					</a>
					<?php

					foreach ( $this->config['admin_toolbar']['items'] as $key => $item ) {

						?>

						<a href="<?php echo esc_attr( $item['link_url'] ); ?>" class="daextulmap-admin-toolbar__menu-item <?php echo 'daextulmap-' . $this->menu_slug === $item['menu_slug'] ? 'is-active' : ''; ?>">
							<div class="daextulmap-admin-toolbar__menu-item-wrapper">
								<?php $this->shared->echo_icon_svg( $item['icon'] ); ?>
								<div class="daextulmap-admin-toolbar__menu-item-text"><?php echo esc_html( $item['link_text'] ); ?></div>
							</div>
						</a>

						<?php

					}

					?>

					<div class="daextulmap-admin-toolbar__menu-item daextulmap-admin-toolbar__menu-item-more">
						<div class="daextulmap-admin-toolbar__menu-item-wrapper">
							<?php $this->shared->echo_icon_svg( 'grid-01' ); ?>
							<div class="daextulmap-admin-toolbar__menu-item-text"><?php esc_html_e( 'More', 'ultimate-markdown-pro' ); ?></div>
							<?php $this->shared->echo_icon_svg( 'chevron-down' ); ?>
						</div>
						<ul class="daextulmap-admin-toolbar__pop-sub-menu">

							<?php

							foreach ( $this->config['admin_toolbar']['more_items'] as $key => $more_item ) {

								?>

								<li>
									<a href="<?php echo esc_attr( $more_item['link_url'] ); ?>" <?php echo 1 === intval( $more_item['pro_badge'], 10 ) ? 'target="_blank"' : ''; ?>>
										<?php echo '<div class="daextulmap-admin-toolbar__more-item-item-text">' . esc_html( $more_item['link_text'] ) . '</div>'; ?>
										<?php

										if ( true === isset( $more_item['pro_badge'] ) && $more_item['pro_badge'] ) {
											echo '<div class="daextulmap-admin-toolbar__pro-badge">' . esc_html__( 'PRO', 'ultimate-markdown-pro' ) . '</div>';
										}

										?>
									</a>
								</li>

								<?php

							}

							?>

						</ul>
					</div>
				</div>
			</div>
			<div class="daextulmap-admin-toolbar__right-section">
				<!-- Display the upgrade button in the Free version. -->
				<?php if ( constant( 'DAEXTULMAP_EDITION' ) === 'FREE' ) : ?>
				<a href="https://daext.com/ultimate-markdown/" target="_blank" class="daextulmap-admin-toolbar__upgrade-button">
					<?php $this->shared->echo_icon_svg( 'diamond-01' ); ?>
					<div class="daextulmap-admin-toolbar__upgrade-button-text"><?php esc_html_e( 'Unlock Extra Features with UM Pro', 'ultimate-markdown-pro' ); ?></div>
				</a>
				<?php endif; ?>
				<a href="https://daext.com" target="_blank" class="daextulmap-admin-toolbar__daext-logo-container">
				<img class="daextulmap-admin-toolbar__daext-logo" src="<?php echo esc_url( $this->shared->get( 'url' ) . 'admin/assets/img/daext-logo.svg' ); ?>" alt="DAEXT" />
				</a>
			</div>
		</div>

		<?php
	}

	/**
	 * Display a section with that includes information on the Pro version. Note that the Pro Features section is
	 * displayed only in the free version.
	 *
	 * @return void
	 */
	public function display_pro_features() {

		if ( constant( 'DAEXTULMAP_EDITION' ) !== 'FREE' ) {
			return;
		}

		?>

		<div class="daextulmap-admin-body">

			<div class="daextulmap-pro-features">

				<div class="daextulmap-pro-features__wrapper">

					<div class="daextulmap-pro-features__left">
						<div class="daextulmap-pro-features__title">
							<div class="daextulmap-pro-features__title-text"><?php esc_html_e( 'Unlock Advanced Features with Ultimate Markdown Pro', 'ultimate-markdown-pro' ); ?></div>
							<div class="daextulmap-pro-features__pro-badge"><?php esc_html_e( 'PRO', 'ultimate-markdown-pro' ); ?></div>
						</div>
						<div class="daextulmap-pro-features__description">
							<?php
							esc_html_e(
								'Choose your favorite Markdown parser, automatically upload the referenced images, import articles in the Classic Editor, send Markdown files to WordPress using the REST API, convert WordPress articles to Markdown files, and more!',
								'ultimate-markdown-pro'
							);
							?>
						</div>
						<div class="daextulmap-pro-features__buttons-container">
							<a class="daextulmap-pro-features__button-1" href="https://daext.com/ultimate-markdown/" target="_blank">
								<div class="daextulmap-pro-features__button-text">
									<?php esc_html_e( 'Learn More', 'ultimate-markdown-pro' ); ?>
								</div>
								<?php $this->shared->echo_icon_svg( 'arrow-up-right' ); ?>
							</a>
							<a class="daextulmap-pro-features__button-2" href="https://daext.com/ultimate-markdown/#pricing" target="_blank">
								<div class="daextulmap-pro-features__button-text">
									<?php esc_html_e( 'View Pricing & Upgrade', 'ultimate-markdown-pro' ); ?>
								</div>
								<?php
								$this->shared->echo_icon_svg( 'arrow-up-right' );
								?>
							</a>
						</div>
					</div>
					<div class="daextulmap-pro-features__right">

						<?php

						$pro_features_data_a = array(
							array(
								'icon'        => 'check-done-02',
								'name_part_1' => 'Markdown',
								'name_part_2' => 'Conversions',
							),
							array(
								'icon'        => 'edit-05',
								'name_part_1' => 'Markdown',
								'name_part_2' => 'Editor',
							),
							array(
								'icon'        => 'upload-04',
								'name_part_1' => 'Bulk',
								'name_part_2' => 'Import',
							),
							array(
								'icon'        => 'share-05',
								'name_part_1' => 'Bulk',
								'name_part_2' => 'Export',
							),
							array(
								'icon'        => 'brackets-ellipses',
								'name_part_1' => 'REST',
								'name_part_2' => 'API',
							),
							array(
								'icon'        => 'file-02',
								'name_part_1' => 'Front Matter',
								'name_part_2' => 'Support',
							),
						);

						foreach ( $pro_features_data_a as $key => $pro_feature_data ) {

							?>

							<div class="daextulmap-pro-features__single-feature">
								<div class="daextulmap-pro-features__single-feature-wrapper">
									<?php $this->shared->echo_icon_svg( $pro_feature_data['icon'] ); ?>
									<div class="daextulmap-pro-features__single-feature-name">
										<?php echo esc_html( $pro_feature_data['name_part_1'] ); ?>
										<br>
										<?php echo esc_html( $pro_feature_data['name_part_2'] ); ?>
									</div>
								</div>
							</div>

							<?php

						}

						?>

					</div>

				</div>

				<div class="daextulmap-pro-features__footer-wrapper">
					<div class="daextulmap-pro-features__footer-wrapper-inner">
						<div class="daextulmap-pro-features__footer-wrapper-left">
							<?php esc_html_e( 'Built for WordPress creators by the DAEXT team', 'ultimate-markdown-pro' ); ?>
						</div>
						<a class="daextulmap-pro-features__footer-wrapper-right" href="https://daext.com/products/" target="_blank">
							<div class="daextulmap-pro-features__footer-wrapper-right-text">
								<?php esc_html_e( 'More Tools from DAEXT', 'ultimate-markdown-pro' ); ?>
							</div>
							<?php $this->shared->echo_icon_svg( 'arrow-up-right' ); ?>
						</a>
					</div>
				</div>

			</div>

		</div>

		<?php
	}

	/**
	 * Handle the duplication of an item.
	 *
	 * In details, when the $_POST['clone_id'] is set, the method will duplicate the corresponding item in the
	 * database.
	 *
	 * @return void
	 */
	public function handle_duplicate() {

		$data             = array();
		$data['clone_id'] = isset( $_POST['clone_id'] ) ? intval( $_POST['clone_id'], 10 ) : null;

		// clone an item.
		if ( ! is_null( $data['clone_id'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextulmap_clone_' . $this->menu_slug . '_' . $data['clone_id'], 'daextulmap_clone_' . $this->menu_slug . '_nonce' );

			$this->duplicate_record( $this->db_table, $this->primary_key, $data['clone_id'] );

			$this->shared->save_dismissible_notice(
				__( 'The item has been successfully duplicated.', 'ultimate-markdown-pro' ),
				'updated'
			);

		}
	}

	/**
	 * Handle the deletion of an item.
	 *
	 * In details, when the $_POST['delete_id'] is set, the method will delete the corresponding item from the
	 * database.
	 *
	 * @return void
	 */
	public function handle_delete() {

		$data              = array();
		$data['delete_id'] = isset( $_POST['delete_id'] ) ? intval( $_POST['delete_id'], 10 ) : null;

		// delete an item.
		if ( ! is_null( $data['delete_id'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextulmap_delete_' . $this->menu_slug . '_' . $data['delete_id'], 'daextulmap_clone_' . $this->menu_slug . '_nonce' );

			// Check deletion conditions.
			$result = $this->item_is_deletable( $data['delete_id'] );

			// prevent deletion if the item is not deletable.
			if ( ! $result['is_deletable'] ) {

				$this->shared->save_dismissible_notice(
					$result['dismissible_notice_message'],
					'error'
				);

			} else {

				global $wpdb;

				// Sanitize the db table name.
				$db_table_name = $wpdb->prefix . 'daextulmap_' . $this->db_table;

				// Sanitize the field name.
				$primary_key = sanitize_key( $this->primary_key );

				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $db_table_name and $primary_key are sanitized above.
				$query_result = $wpdb->query(
					$wpdb->prepare( "DELETE FROM $db_table_name WHERE $primary_key = %d", $data['delete_id'] )
				);
				// phpcs:enable

				if ( false !== $query_result ) {
					$this->shared->save_dismissible_notice(
						__( 'The item has been successfully deleted.', 'ultimate-markdown-pro' ),
						'updated'
					);
				}
			}
		}
	}

	/**
	 * Handles the processing of bulk actions.
	 *
	 * @return void
	 */
	public function handle_bulk_actions() {

		$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) ) : null;

		if ( 'delete' === $bulk_action ) {

			$delete_id = ( isset( $_POST['bulk-action-selected-items'] ) && ! empty( $_POST['bulk-action-selected-items'] ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['bulk-action-selected-items'] ) ) ) : null;

			if ( ! is_array( $delete_id ) ) {
				return;
			}

			// Convert all the $delete_id values to numeric values with base 10 using intval.
			$delete_id = array_map(
				function ( $value ) {
					return intval( $value, 10 );
				},
				$delete_id
			);

			if ( ! is_null( $delete_id ) ) {

				// Nonce verification.
				check_admin_referer( 'daextulmap_bulk_action_' . $this->menu_slug, 'daextulmap_bulk_action_' . $this->menu_slug . '_nonce' );

				$delete_id_deletable     = array();
				$delete_id_non_deletable = array();

				// Create two new array that includes deletable and non-deletable items.
				foreach ( $delete_id as $key => $value ) {

					$result = $this->item_is_deletable( $value );

					if ( $result['is_deletable'] ) {
						$delete_id_deletable[] = $value;
					} else {
						$delete_id_non_deletable[] = $value;
					}
				}

				// Delete the items.
				global $wpdb;
				$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_' . $this->db_table;

				if ( count( $delete_id_deletable ) > 0 ) {

					// Sanitize the db table name.
					$table_name = sanitize_key( $table_name );

					// Sanitize the field name.
					$primary_key_name = sanitize_key( $this->primary_key );

					// phpcs:disable WordPress.DB.DirectDatabaseQuery
					// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $variables are sanitized above.
					// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
					$query_result = $wpdb->query(
						"DELETE FROM $table_name WHERE $primary_key_name IN (" . implode( ',', $delete_id_deletable ) . ')'
					);
					// phpcs:enable

				}

				if ( isset( $query_result ) && false !== $query_result ) {

					// Get the number of deleted items with $wpdb.
					$deleted_items_count = $wpdb->rows_affected;

					$this->shared->save_dismissible_notice(
						$deleted_items_count . ' ' . __( 'items have been successfully deleted.', 'ultimate-markdown-pro' ),
						'updated'
					);

				}

				if ( count( $delete_id_non_deletable ) > 0 ) {

					$this->shared->save_dismissible_notice(
						__( 'The', 'ultimate-markdown-pro' ) . ' ' . strtolower( $this->label_plural ) . ' ' . __( "with the following IDs are used in one or more automatic links and can't be deleted:", 'ultimate-markdown-pro' ) . ' ' . implode( ', ', $delete_id_non_deletable ) . '.',
						'error'
					);

				}
			}
		}
	}

	/**
	 * Verify the provided user capability.
	 *
	 * Die with a message if the user does not have the required capability.
	 *
	 * @return void
	 */
	public function verify_user_capability() {

		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ultimate-markdown-pro' ) );
		}
	}

	/**
	 * Displays in the admin area the elements of a CRUD menu.
	 *
	 * The elements can be one of the following:
	 *
	 * - The table with the list of items.
	 * - The form to add an item.
	 * - The form to edit an item.
	 */
	public function display_crud_menu() {

		?>

		<div class="daextulmap-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->display_dismissible_notices();

			// Display the license activation notice.
			$this->shared->display_license_activation_notice();

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce not required for data visualization.
			$action  = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
			$edit_id = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : null;
			// phpcs:enable

			if ( 'new' === $action ) {
				$this->new_item();
			} elseif ( null !== $edit_id ) {
				$this->edit_item( $edit_id );
			} else {

				$this->display_list_table(
					$this->db_table,
					$this->list_table_columns,
					$this->searchable_fields,
					$this->label_plural,
					$this->slug_plural,
					$this->primary_key
				);

			}

			?>

		</div>

		<!-- Dialog Confirm -->
		<div id="dialog-confirm" title="<?php esc_attr_e( 'Delete the item?', 'ultimate-markdown-pro' ); ?>" class="daext-display-none">
			<p>
			<?php
			esc_html_e(
				'This item will be permanently deleted and cannot be recovered. Are you sure?',
				'ultimate-markdown-pro'
			);
			?>
					</p>
		</div>

		<?php
	}

	/**
	 * Provided the db table name and the primary_key name, and the primary_key value of the record to duplicate, the method will duplicate the record in the
	 *  database. Note that this method is generic and should work with any database table.
	 *
	 * @param string $table_name The name of the database table.
	 * @param string $primary_key_name The name of the primary key.
	 * @param string $primary_key_value The value of the primary key.
	 *
	 * @return void
	 */
	public function duplicate_record( $table_name, $primary_key_name, $primary_key_value ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_' . $table_name;

		// retrieve the record to duplicate.

		// Sanitize the db table name.
		$table_name = sanitize_key( $table_name );

		// Sanitize the field name.
		$primary_key_name = sanitize_key( $primary_key_name );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table_name and $primary_key are sanitized above.
		$record = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE $primary_key_name = %d",
				$primary_key_value
			),
			ARRAY_A
		);
		// phpcs:enable

		// remove the primary key from the record.
		unset( $record[ $primary_key_name ] );

		// insert the record into the database.

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->insert( $table_name, $record );
	}

	/**
	 * This method echos the HTML of the form fields based on the provided form field data provided as an array with the
	 * $sections parameter.
	 *
	 * @param array $sections The sections of the form.
	 *
	 * @return void
	 */
	public function print_form_fields_from_array( $sections ) {

		// Iterate over the $data array.
		foreach ( $sections as $section ) {

			// Print the opening tag of the section.
			echo '<div class="daextulmap-main-form__daext-form-section" data-id="' . esc_attr( $section['section_id'] ) . '">';

			// If the section has a visible header, display it.
			if ( false !== $section['display_header'] ) {

				$this->section_header(
					$section['label'],
					$section['section_id'],
					$section['icon_id']
				);

			}

			// Print the opening tag of the section body.
			echo '<div class="daextulmap-main-form__daext-form-section-body" data-section-id="' . esc_attr( $section['section_id'] ) . '">';

			// Iterate over the fields of the section.
			foreach ( $section['fields'] as $field ) {

				// If the field is a text input, display it.
				if ( 'text' === $field['type'] ) {

					$this->input_field(
						$field['name'],
						$field['label'],
						$field['description'],
						isset( $field['placeholder'] ) ? $field['placeholder'] : '',
						$field['value'],
						$field['maxlength'],
						$field['required'],
						$section['section_id']
					);

				}

				// If the field is a text input, display it.
				if ( 'textarea' === $field['type'] ) {

					$this->textarea_field(
						$field['name'],
						$field['label'],
						$field['description'],
						isset( $field['placeholder'] ) ? $field['placeholder'] : '',
						$field['value'],
						$field['maxlength'],
						$field['required'],
						$section['section_id']
					);

				}

				// If the field is a select input, display it.
				if ( 'select' === $field['type'] ) {

					$this->select_field(
						$field['name'],
						$field['label'],
						$field['description'],
						$field['options'],
						$field['value'],
						$section['section_id']
					);

				}

				// If the field is a select multiple input, display it.
				if ( 'select_multiple' === $field['type'] ) {

					$this->select_multiple_field(
						$field['name'],
						$field['label'],
						$field['description'],
						$field['options'],
						$field['value'],
						$section['section_id']
					);

				}

				// If the field is a toggle, display it.
				if ( 'toggle' === $field['type'] ) {

					$this->toggle_field(
						$field['name'],
						$field['label'],
						$field['description'],
						$field['value'],
						$section['section_id']
					);

				}

				// If the field is an input range, display it.
				if ( 'input_range' === $field['type'] ) {

					$this->input_range_field(
						$field['name'],
						$field['label'],
						$field['description'],
						$field['value'],
						$section['section_id'],
						$field['min'],
						$field['max']
					);

				}
			}

			// Print the closing tag of the section body.
			echo '</div>';

			// Print the closing tag of the section.
			echo '</div>';

		}
	}

	/**
	 * Displays the content of the admin menu.
	 *
	 * @return void
	 */
	public function display_menu_content() {

		// Verify user capability.
		$this->verify_user_capability();

		// Display the Admin Toolbar.
		$this->display_admin_toolbar();

		// Display the Header Bar.
		$this->header_bar();

		// Display the main content of the menu.
		if ( 'crud' === $this->context ) {

			// Body for CRUD menus defined in the parent class.
			$this->display_crud_menu();

		} else {

			// Custom body content defined in the menu child class.
			$this->display_custom_content();

		}

		// Display the Pro features section.
		$this->display_pro_features();
	}

	/**
	 * Display the search form of the CRUD menu.
	 *
	 * @param String $post_search_input The search input value.
	 *
	 * @return void
	 */
	public function display_crud_menu_search_form( $post_search_input ) {

		// Search Form -------------------------------------------------------------------------------------------------------.

		?>

		<input type="hidden" name="items-filter" value="1">

		<form action="admin.php" method="GET" id="items-filter">

			<div class="daextulmap-crud-table-search-form">

				<div id="daext-search-form">

					<input type="hidden" name="page" value="daextulmap-<?php echo esc_attr( $this->slug_plural ); ?>">
					<input class="daextulmap-crud-table-search-form__post-search-input" type="text" name="s"
							value="<?php echo null !== $post_search_input ? esc_attr( stripslashes( $post_search_input ) ) : ''; ?>" autocomplete="off" maxlength="255">
					<input class="button daextulmap-admin-page-button" type="submit" value="Search <?php echo esc_attr( $this->label_plural ); ?>">

				</div>

			</div>

		</form>

		<?php
	}

}
