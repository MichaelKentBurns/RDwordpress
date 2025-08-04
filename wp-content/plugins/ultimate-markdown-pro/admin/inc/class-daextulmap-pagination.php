<?php
/**
 * This file is used to handle the pagination in the back-end menus.
 *
 * @package ultimate-markdown-pro
 */

/**
 * Handles the pagination on the back-end menus by returning the HTML content useful to represent the elements of the
 *  pagination.
 */
class Daextulmap_Pagination {

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextulmap_Shared|null
	 */
	private $shared = null;

	/**
	 * Total number of items.
	 *
	 * @var null
	 */
	public $total_items = null;

	/**
	 * Number of records to display per page.
	 *
	 * @var int
	 */
	private $record_per_page = 10;

	/**
	 * Target page url.
	 *
	 * @var string
	 */
	private $target_page = '';

	/**
	 * Store the current page value, this is set through the set_current_page() method.
	 *
	 * @var int
	 */
	private $current_page = 0;

	/**
	 * Store the number of adjacent pages to show on each side of the current page inside the pagination.
	 *
	 * @var int
	 */
	private $adjacents = 2;

	/**
	 * Store the $_GET parameter to use.
	 *
	 * @var string
	 */
	private $parameter_name = 'p';

	/**
	 * Constructor.
	 *
	 * @param Daextulmap_Shared $shared Instance of the shared class.
	 */
	public function __construct( $shared ) {

		// Assign an instance of the plugin info.
		$this->shared = $shared;
	}

	/**
	 * Set the total number of items.
	 *
	 * @param int $value The total number of items.
	 */
	public function set_total_items( $value ) {
		$this->total_items = intval( $value, 10 );
	}

	/**
	 * Set the number of items to show per page.
	 *
	 * @param int $value The number of items to show per page.
	 */
	public function set_record_per_page( $value ) {
		$this->record_per_page = intval( $value, 10 );
	}

	/**
	 * Set the page url
	 *
	 * @param string $value The page URL.
	 */
	public function set_target_page( $value ) {
		$this->target_page = $value;
	}

	/**
	 * Set the current page parameter by getting it from $_GET['p'], if it's not set or it's not > than 0 then set
	 * it to 1.
	 */
	public function set_current_page() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for data visualization.
		$page_number = isset( $_GET[ $this->parameter_name ] ) ? intval( $_GET[ $this->parameter_name ], 10 ) : null;

		if ( ! is_null( $page_number ) ) {

			if ( $page_number > 0 && $page_number <= ceil( $this->total_items / $this->record_per_page ) ) {
				$this->current_page = $page_number;
			} else {
				$this->current_page = 1;
			}
		} else {

			$this->current_page = 1;

		}
	}

	/**
	 * Set the number of adjacent pages to show on each side of the current page inside the pagination.
	 *
	 * @param int $value The number of adjacent pages to show on each side of the current page inside the pagination.
	 *
	 * @return void
	 */
	public function set_adjacents( $value ) {
		$this->adjacents = intval( $value, 10 );
	}

	/**
	 * Assing a different $_GET parameter instead of p.
	 *
	 * @param string $value The parameter name.
	 *
	 * @return void
	 */
	public function set_parameter_name( $value = '' ) {
		$this->parameter_name = $value;
	}

	/**
	 * Calculate and echo the pagination.
	 */
	public function show() {

		// Setup page vars for display.
		$first_page = 1;// First page.
		$prev       = $this->current_page - 1;// Previous page.
		$next       = $this->current_page + 1;// Next page.
		$last_page  = intval( ceil( $this->total_items / $this->record_per_page ), 10 );// Last page.
		$lpm1       = $last_page - 1;// Last page minus 1.

		// Generate the pagination if there is more than one page.
		if ( $last_page > 1 ) {

			// Generate the "Previous" button.
			if ( $this->current_page ) {

				if ( $this->current_page > 1 ) {

					// If the current page is > 1 the "First Page" button is clickable.
					$this->display_link( '&#171', $this->get_pagenum_link( $first_page ) );

					// If the current page is > 1 the "Previous" button is clickable.
					$this->display_link( '&#139', $this->get_pagenum_link( $prev ) );

				} else {

					// If the current page is > 1 the "First Page" button is clickable.
					$this->display_link( '&#171' );

					// If the current page is not > 1 the previous button is not clickable.
					$this->display_link( '&#139' );

				}
			}

			echo '<div class="daextulmap-crud-table-controls__pagination-paging-text">' .
			     esc_html( $this->current_page ) .
			     '&nbsp' .
			     esc_html__( 'of', 'ultimate-markdown-pro' ) .
			     '&nbsp' .
			     esc_html( $last_page ) .
			     '</div>';

			// Generate the "Next" button.
			if ( $this->current_page ) {

				if ( $this->current_page < $last_page ) {

					// If the current page is not the last page the "Next" button is clickable.
					$this->display_link( '&#155', $this->get_pagenum_link( $next ) );

					// If the current page is not the last page the "Last Page" button is clickable.
					$this->display_link( '&#187', $this->get_pagenum_link( $last_page ) );

				} else {

					// If the current page is the last page the "Next" button is not clickable.
					$this->display_link( '&#155' );

					// If the current page is not the last page the "Last Page" button is not clickable.
					$this->display_link( '&#187' );

				}
			}
		}
	}

	/**
	 * Return the complete url associated with this page id.
	 *
	 * @param int $id The page id.
	 *
	 * @return string The URL associated with the id.
	 */
	public function get_pagenum_link( $id ) {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for data visualization.

		// search: s --------------------------------------------------------------------------------------------------.
		if ( isset( $_GET['s'] ) ) {
			$s = sanitize_text_field( wp_unslash( $_GET['s'] ) );
			if ( strlen( trim( $s ) ) > 0 ) {
				$search = '&s=' . $s;
			} else {
				$search = '';
			}
		} else {
			$search = '';
		}

		if ( strpos( $this->target_page, '?' ) === false ) {
			return esc_url( $this->target_page . '?' . $this->parameter_name . '=' . $id . $search );
		} else {
			return esc_url( $this->target_page . '&' . $this->parameter_name . '=' . $id . $search );
		}

		// phpcs:enable
	}

	/**
	 * Generate the query string to use inside the SQL query.
	 *
	 * @return string
	 */
	public function query_limit() {

		// Calculate the $list_start position.
		$list_start = ( $this->current_page - 1 ) * $this->record_per_page;

		// Start of the list should be less than pagination count.
		if ( $list_start >= $this->total_items ) {
			$list_start = ( $this->total_items - $this->record_per_page );
		}

		// List start can't be negative.
		if ( $list_start < 0 ) {
			$list_start = 0;
		}

		return 'LIMIT ' . intval( $list_start, 10 ) . ', ' . intval( $this->record_per_page, 10 );
	}

	/**
	 * Display the pagination link based on the provided link text and url.
	 *
	 * @param string $text The text of the link.
	 * @param null   $url The url of the link.
	 */
	private function display_link( $text, $url = null ) {

		if ( null === $url ) {

			// Non-clickable and disabled links.

			if ( '&#139' === $text ) {
				echo '<a href="javascript: void(0)" class="disabled">';
				$this->shared->echo_icon_svg( 'chevron-left' );
				echo '</a>';
			} elseif ( '&#171' === $text ) {
				echo '<a href="javascript: void(0)" class="disabled">';
				$this->shared->echo_icon_svg( 'chevron-left-double' );
				echo '</a>';
			} elseif ( '&#155' === $text ) {
				echo '<a href="javascript: void(0)" class="disabled">';
				$this->shared->echo_icon_svg( 'chevron-right' );
				echo '</a>';
			} elseif ( '&#187' === $text ) {
				echo '<a href="javascript: void(0)" class="disabled">';
				$this->shared->echo_icon_svg( 'chevron-right-double' );
				echo '</a>';
			} else {
				echo '<a href="javascript: void(0)" class="disabled">' . esc_html( $text ) . '</a>';
			}
		} else {

			// Clickable and active links.

			if ( '&#139' === $text ) {
				echo '<a href="' . esc_url( $url ) . '">';
				$this->shared->echo_icon_svg( 'chevron-left' );
				echo '</a>';
			} elseif ( '&#171' === $text ) {
				echo '<a href="' . esc_url( $url ) . '">';
				$this->shared->echo_icon_svg( 'chevron-left-double' );
				echo '</a>';
			} elseif ( '&#155' === $text ) {
				echo '<a href="' . esc_url( $url ) . '">';
				$this->shared->echo_icon_svg( 'chevron-right' );
				echo '</a>';
			} elseif ( '&#187' === $text ) {
				echo '<a href="' . esc_url( $url ) . '">';
				$this->shared->echo_icon_svg( 'chevron-right-double' );
				echo '</a>';
			} else {
				echo '<a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
			}
		}
	}
}
