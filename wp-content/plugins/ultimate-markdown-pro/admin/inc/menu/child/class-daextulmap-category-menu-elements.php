<?php
/**
 * Class used to implement the back-end functionalities of the "Category" menu.
 *
 * @package ultimate-markdown-pro
 */

/**
 * Class used to implement the back-end functionalities of the "Category" menu.
 */
class Daextulmap_Category_Menu_Elements extends Daextulmap_Menu_Elements {

	/**
	 * Daextulmap_Category_Menu_Elements constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'category';
		$this->slug_plural        = 'categories';
		$this->label_singular     = 'Category';
		$this->label_plural       = 'Categories';
		$this->primary_key        = 'category_id';
		$this->db_table           = 'category';
		$this->list_table_columns = array(
			array(
				'db_field' => 'name',
				'label'    => 'Name',
			),
			array(
				'db_field' => 'description',
				'label'    => 'Description',
			),
		);
		$this->searchable_fields  = array(
			'name',
			'description',
		);
	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 * 1. Sanitization
	 * 2. Validation
	 * 3. Database update
	 *
	 * @return void
	 */
	public function process_form() {

		if ( isset( $_POST['update_id'] ) ||
			isset( $_POST['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextulmap_create_update_' . $this->menu_slug, 'daextulmap_create_update_' . $this->menu_slug . '_nonce' );

		}

		// Preliminary operations ---------------------------------------------------------------------------------------------.
		global $wpdb;

		// Sanitization -------------------------------------------------------------------------------------------------------.

		$data = array();

		// Actions.
		$data['update_id']      = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : null;
		$data['form_submitted'] = isset( $_POST['form_submitted'] ) ? intval( $_POST['form_submitted'], 10 ) : null;

		// Sanitization.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			$data['name']        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : null;
			$data['description'] = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : null;

		}

		// Validation ---------------------------------------------------------------------------------------------------------.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			$invalid_data_message = '';

			// Validation on "name".
			if ( mb_strlen( trim( $data['name'] ) ) === 0 || mb_strlen( trim( $data['name'] ) ) > 100 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Name" field.', 'ultimate-markdown-pro' ),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "description".
			if ( mb_strlen( trim( $data['description'] ) ) === 0 || mb_strlen( trim( $data['description'] ) ) > 255 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Description" field.', 'ultimate-markdown-pro' ),
					'error'
				);
				$invalid_data = true;
			}
		}

		// Database record update -------------------------------------------------------------------------------------.
		if ( ! is_null( $data['update_id'] ) && ! isset( $invalid_data ) ) {

			// Update the database.

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}daextulmap_category SET 
		                name = %s,
		                description = %s
		                WHERE category_id = %d",
					$data['name'],
					$data['description'],
					$data['update_id']
				)
			);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The category has been successfully updated.', 'ultimate-markdown-pro' ),
					'updated'
				);
			}
		} elseif ( ! is_null( $data['form_submitted'] ) && ! isset( $invalid_data ) ) {

			// Add record to database ------------------------------------------------------------------------------------.

				// insert into the database.

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$query_result = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}daextulmap_category SET 
			                name = %s,
			                description = %s",
						$data['name'],
						$data['description']
					)
				);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The category has been successfully added.', 'ultimate-markdown-pro' ),
					'updated'
				);
			}
		}
	}

	/**
	 * Defines the form fields present in the add/edit form and call the method to print them.
	 *
	 * @param array $item_obj The item object.
	 *
	 * @return void
	 */
	public function print_form_fields( $item_obj = null ) {

		// Add the form data in the $sections array.
		$sections = array(
			array(
				'label'          => 'Main',
				'section_id'     => 'main',
				'display_header' => false,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'name',
						'label'       => __( 'Name', 'ultimate-markdown-pro' ),
						'description' => __( 'The name of the category.', 'ultimate-markdown-pro' ),
						'placeholder' => '',
						'value'       => isset( $item_obj ) ? $item_obj['name'] : null,
						'maxlength'   => 100,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'description',
						'label'       => __( 'Description', 'ultimate-markdown-pro' ),
						'description' => __( 'The description of the category.', 'ultimate-markdown-pro' ),
						'placeholder' => '',
						'value'       => isset( $item_obj ) ? $item_obj['description'] : null,
						'maxlength'   => 255,
						'required'    => true,
					),
				),
			),
		);

		$this->print_form_fields_from_array( $sections );
	}

	/**
	 * Check if the item is deletable. If not, return the message to be displayed.
	 *
	 * @param int $item_id The ID of the item to be checked.
	 *
	 * @return array
	 */
	public function item_is_deletable( $item_id ) {

		if ( $this->shared->category_is_used( $item_id ) ) {
			$is_deletable               = false;
			$dismissible_notice_message = __( "This category is associated with one or more AIL and can't be deleted.", 'ultimate-markdown-pro' );
		} else {
			$is_deletable               = true;
			$dismissible_notice_message = null;
		}

		return array(
			'is_deletable'               => $is_deletable,
			'dismissible_notice_message' => $dismissible_notice_message,
		);
	}
}
