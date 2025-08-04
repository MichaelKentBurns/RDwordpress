<?php
/**
 * This class handles Front Matter.
 *
 * @package ultimate-markdown-pro
 */

/**
 * This class handles Front Matter.
 */
class Daextulmap_Front_Matter {

	private $shared = null;

	public function __construct( $shared ) {

		// Assign an instance of the plugin info.
		$this->shared = $shared;
	}

	/**
	 * Get the YAML data available in the provided string.
	 *
	 * @param string $str The string that contains the YAML data.
	 *
	 * @return array
	 */
	public function get( $str ) {

		// Get an array with the Front Matter data by using the FrontYAML library.
		require_once $this->shared->get( 'dir' ) . 'globalize-front-yaml.php';
		global $front_yaml_parser;

		try {
			$document = $front_yaml_parser->parse( $str );
			$data     = $document->getYAML();
		} catch ( Exception $e ) {
			$data = array();
		}

		try {
			// Sanitize and prepare the YAML data.
			$data = $this->prepare_fields_for_import( $data );
		} catch ( Exception $e ) {
			$data = array( 'error' => $e->getMessage() );
		}

		return $data;
	}

	/**
	 * Add the YAML data at the beginning of the provided string.
	 *
	 * @param string $str The string that contains the content.
	 * @param array  $yaml_data An array with the YAML data.
	 *
	 * @return string|array
	 */
	public function set($str, $yaml_data) {

		try {
			// Sanitize and prepare the YAML data.
			$yaml_data = $this->prepare_fields_for_export( $yaml_data );

			// Convert the YAML data to a string.
			$yaml_string = $this->dump_yaml( $yaml_data );

			// Add the YAML data to the string.
			return $yaml_string . "\n" . $str;
		} catch ( Exception $e ) {
			return array( 'error' => $e->getMessage() );
		}
	}

	/**
	 * Sanitize and validate the data provided by the user as Front Matter fields.
	 *
	 * @param array $yaml_data An array with the YAML data.
	 *
	 * @return array
	 */
	private function prepare_fields_for_import( $yaml_data ) {

		$sanitized_data = array();

		// Sanitization -----------------------------------------------------------------------------------------------.

		// Title.
		$sanitized_data['title'] = isset( $yaml_data['title'] ) ? sanitize_text_field( $yaml_data['title'] ) : null;

		// Excerpt.
		$sanitized_data['excerpt'] = isset( $yaml_data['excerpt'] ) ? sanitize_text_field( $yaml_data['excerpt'] ) : null;

		// Categories.
		$sanitized_data['categories'] = isset( $yaml_data['categories'] ) ? array_map( 'sanitize_text_field', $yaml_data['categories'] ) : null;

		// Tags.
		$sanitized_data['tags'] = isset( $yaml_data['tags'] ) ? array_map( 'sanitize_text_field', $yaml_data['tags'] ) : null;

		// Author.
		$sanitized_data['author'] = isset( $yaml_data['author'] ) ? sanitize_text_field( $yaml_data['author'] ) : null;

		// Date.
		$sanitized_data['date'] = isset( $yaml_data['date'] ) ? sanitize_text_field( $yaml_data['date'] ) : null;

		// Status.
		$sanitized_data['status'] = isset( $yaml_data['status'] ) ? sanitize_key( $yaml_data['status'] ) : null;

		// Slug.
		$sanitized_data['slug'] = isset( $yaml_data['slug'] ) ? sanitize_title( $yaml_data['slug'] ) : null;

		// Thumbnail.
		$sanitized_data['thumbnail'] = isset( $yaml_data['thumbnail'] ) ? esc_url_raw( $yaml_data['thumbnail'] ) : null;

		// Thumbnail Alt.
		$sanitized_data['thumbnail_alt'] = isset( $yaml_data['thumbnail_alt'] ) ? sanitize_text_field( $yaml_data['thumbnail_alt'] ) : null;

		// Thumbnail Title.
		$sanitized_data['thumbnail_title'] = isset( $yaml_data['thumbnail_title'] ) ? sanitize_text_field( $yaml_data['thumbnail_title'] ) : null;

		// Validation -------------------------------------------------------------------------------------------------.

		$validated_data = array();

		// Title.
		if ( !is_null($sanitized_data['title']) && strlen( trim( $sanitized_data['title'] ) ) > 0 ) {
			$validated_data['title'] = $sanitized_data['title'];
		} else {
			$validated_data['title'] = null;
		}

		// Excerpt.
		if ( !is_null($sanitized_data['excerpt']) && strlen( trim( $sanitized_data['excerpt'] ) ) > 0 ) {
			$validated_data['excerpt'] = $sanitized_data['excerpt'];
		} else {
			$validated_data['excerpt'] = null;
		}

		// Categories.
		$validated_data['categories'] = $this->prepare_categories( $sanitized_data['categories'] );

		// Tags.
		$validated_data['tags'] = $this->prepare_tags( $sanitized_data['tags'] );

		// Author.
		$validated_data['author'] = $this->prepare_author( $sanitized_data['author'] );

		// Date.
		$validated_data['date'] = $this->prepare_date( $sanitized_data['date'] );

		// Status.
		$validated_data['status'] = $this->prepare_status( $sanitized_data['status'] );

		// Slug.
		$validated_data['slug'] = $this->prepare_slug( $sanitized_data['slug'] );

		// Thumbnail.
		$validated_data['thumbnail'] = $this->prepare_thumbnail(
			$sanitized_data['thumbnail'],
			$sanitized_data['thumbnail_alt'],
			$sanitized_data['thumbnail_title']
		);

		// Thumbnail Alt.
		if ( !is_null($sanitized_data['thumbnail_alt']) && strlen( trim( $sanitized_data['thumbnail_alt'] ) ) > 0 ) {
			$validated_data['thumbnail_alt'] = $sanitized_data['thumbnail_alt'];
		} else {
			$validated_data['thumbnail_alt'] = null;
		}

		// Thumbnail Title.
		if ( isset($validated_data['thumbnail_title']) && !is_null($validated_data['thumbnail_title']) && strlen( trim( $sanitized_data['thumbnail_title'] ) ) > 0 ) {
			$validated_data['thumbnail_title'] = $sanitized_data['thumbnail_title'];
		} else {
			$validated_data['thumbnail_title'] = null;
		}

		return $validated_data;
	}

	/**
	 * Sanitize and validate the data retrieved from the post and make them suitable for the Front Matter section of the
	 * exported Markdown files.
	 *
	 * @param array $yaml_data An array with the YAML data.
	 *
	 * @return array
	 */
	private function prepare_fields_for_export( $yaml_data ) {

		$sanitized_data = array();

		// Sanitization -----------------------------------------------------------------------------------------------.

		// Title.
		$sanitized_data['title'] = isset( $yaml_data['title'] ) ? html_entity_decode( wp_strip_all_tags( $yaml_data['title'] ) ) : null;

		// Excerpt.
		$sanitized_data['excerpt'] = isset( $yaml_data['excerpt'] ) ? html_entity_decode( wp_strip_all_tags( $yaml_data['excerpt'] ) ) : null;

		// Categories.
		$sanitized_data['categories'] = isset( $yaml_data['categories'] ) ? array_map( 'sanitize_text_field', $yaml_data['categories'] ) : null;

		// Tags.
		$sanitized_data['tags'] = isset( $yaml_data['tags'] ) ? array_map( 'sanitize_text_field', $yaml_data['tags'] ) : null;

		// Author.
		$sanitized_data['author'] = isset( $yaml_data['author'] ) ? sanitize_text_field( $yaml_data['author'] ) : null;

		// Date.
		$sanitized_data['date'] = isset( $yaml_data['date'] ) ? sanitize_text_field( $yaml_data['date'] ) : null;

		// Status.
		$sanitized_data['status'] = isset( $yaml_data['status'] ) ? sanitize_key( $yaml_data['status'] ) : null;

		// Slug.
		$sanitized_data['slug'] = isset( $yaml_data['slug'] ) ? sanitize_title( $yaml_data['slug'] ) : null;

		// Thumbnail.
		$sanitized_data['thumbnail'] = isset( $yaml_data['thumbnail'] ) ? esc_url_raw( $yaml_data['thumbnail'] ) : null;

		// Thumbnail Alt.
		$sanitized_data['thumbnail_alt'] = isset( $yaml_data['thumbnail_alt'] ) ? sanitize_text_field( $yaml_data['thumbnail_alt'] ) : null;

		// Thumbnail Title.
		$sanitized_data['thumbnail_title'] = isset( $yaml_data['thumbnail_title'] ) ? sanitize_text_field( $yaml_data['thumbnail_title'] ) : null;

		// Validation -------------------------------------------------------------------------------------------------.

		$validated_data = array();

		// Title.
		if ( !is_null($sanitized_data['title']) && strlen( trim( $sanitized_data['title'] ) ) > 0 ) {
			$validated_data['title'] = $sanitized_data['title'];
		} else {
			$validated_data['title'] = null;
		}

		// Excerpt.
		if ( !is_null($sanitized_data['excerpt']) && strlen( trim( $sanitized_data['excerpt'] ) ) > 0 ) {
			$validated_data['excerpt'] = $sanitized_data['excerpt'];
		} else {
			$validated_data['excerpt'] = null;
		}

		// Categories.
		$validated_data['categories'] = $sanitized_data['categories'];

		// Tags.
		$validated_data['tags'] = $sanitized_data['tags'];

		// Author.
		$validated_data['author'] = $sanitized_data['author'];

		// Date.
		$validated_data['date'] = $this->prepare_date( $sanitized_data['date'] );

		// Status.
		$validated_data['status'] = $this->prepare_status( $sanitized_data['status'] );

		// Slug.
		$validated_data['slug'] = $this->prepare_slug( $sanitized_data['slug'] );

		// Thumbnail.
		$validated_data['thumbnail'] = $sanitized_data['thumbnail'];

		// Thumbnail Alt.
		if ( !is_null($sanitized_data['thumbnail_alt']) && strlen( trim( $sanitized_data['thumbnail_alt'] ) ) > 0 ) {
			$validated_data['thumbnail_alt'] = $sanitized_data['thumbnail_alt'];
		} else {
			$validated_data['thumbnail_alt'] = null;
		}

		// Thumbnail Title.
		if ( isset($sanitized_data['thumbnail_title']) && !is_null($sanitized_data['thumbnail_title']) && strlen( trim( $sanitized_data['thumbnail_title'] ) ) > 0 ) {
			$validated_data['thumbnail_title'] = $sanitized_data['thumbnail_title'];
		} else {
			$validated_data['thumbnail_title'] = null;
		}

		/**
		 * If the option to include empty fields is enabled, the fields that are not set will be included in the
		 * Front Matter section of the exported Markdown files as empty strings (for the fields: 'title', 'excerpt',
		 * 'author', 'date', 'status', 'slug', 'thumbnail', 'thumbnail_alt', 'thumbnail_title') or empty arrays based on
		 * they type.
		 *
		 * If the option to include empty fields is enabled the front matter fields that are not set, are empty array,
		 * or empty string are removed from the Front Matter section of the exported Markdown files.
		 */
		if ( 1 === intval( get_option( $this->shared->get( 'slug' ) . '_exported_files_include_empty_front_matter_fields' ), 10 ) ) {

			$fields_with_empty_string = array(
				'title', 'excerpt', 'author', 'date', 'status', 'slug',
				'thumbnail', 'thumbnail_alt', 'thumbnail_title'
			);

			$cleaned_data = array(); // new array to build the updated version

			foreach ($validated_data as $key => $value) {
				if (is_null($value)) {
					$cleaned_data[$key] = in_array($key, $fields_with_empty_string, true) ? '' : array();
				} else {
					$cleaned_data[$key] = $value;
				}
			}

			$validated_data = $cleaned_data; // overwrite original if needed


		}else{

			$validated_data = array_filter( $validated_data, function ( $value ) {
				if ( ! is_null( $value ) &&
				     ( is_array( $value ) && count( $value ) > 0 ) ||
				     ( is_string( $value ) && strlen( trim( $value ) ) > 0 ) ) {
					return true;
				}
			} );

		}

		return $validated_data;
	}

	/**
	 * Generates an array with existing category IDs from an array that includes:
	 *
	 * - Non verified category names
	 * - Non verified category IDs
	 *
	 * @param $raw_categories
	 *
	 * @return array An array with existing category IDs
	 */
	private function prepare_categories( $raw_categories ) {

		if ( null === $raw_categories ) {
			return null;
		}

		$categories = array();
		foreach ( $raw_categories as $category ) {

			/**
			 * If the provided value is numberic it is a category ID. Otherwise, it is a category name.
			 */
			if ( is_numeric( $category ) ) {

				// If a category with the provided ID exists use it.
				if ( term_exists( intval( $category, 10 ), 'category' ) ) {
					$categories[] = intval( $category, 10 );
				}
			} else {

				// Find the categories IDs from category name.
				$found_category_id = get_cat_ID( $category );
				if ( $found_category_id !== 0 ) {
					$categories[] = $found_category_id;
				}
			}
		}

		if ( count( $categories ) > 0 ) {
			return $categories;
		} else {
			return null;
		}
	}

	/**
	 * Generates an array with existing category IDs from an array that includes:
	 *
	 * - Non verified category names
	 * - Non verified category IDs
	 *
	 * @param $raw_categories
	 *
	 * @return array An array with existing category IDs
	 */
	private function prepare_tags( $raw_tags ) {

		if ( null === $raw_tags ) {
			return null;
		}

		$tags = array();
		foreach ( $raw_tags as $tag ) {

			if ( intval( $tag, 10 ) ) {

				// If a tag with the provided ID exists use it.
				if ( term_exists( $tag, 'post_tag' ) ) {
					$tags[] = $tag;
				}
			} else {

				// Find the tags IDs from tag name.
				$term_obj = get_term_by( 'name', $tag, 'post_tag' );

				if ( false !== $term_obj ) {
					$tags[] = $term_obj->term_id;
				}
			}
		}

		if ( count( $tags ) > 0 ) {
			return $tags;
		} else {
			return null;
		}
	}

	/**
	 * If a valid user ID is provided returned the user ID. Otherwise, retrieve the user from the user login name.
	 *
	 * @param $value
	 *
	 * @return int
	 */
	private function prepare_author( $value ) {

		if ( null === $value ) {
			return null;
		}

		// If the user is provided with a valid ID return its ID.
		if ( $this->shared->user_id_exists( $value ) ) {

			return $value;

		} else {

			// Get the ID of the user from the user login name.
			$user_obj = get_user_by( 'login', $value );

			if ( false !== $user_obj ) {

				return $user_obj->ID;

			}
		}

		return null;
	}


	/**
	 * If the date is valid, returns it as a string in 'Y-m-d H:i:s' format. Otherwise returns null.
	 *
	 * @param mixed $date
	 *
	 * @return string|null
	 */
	private function prepare_date( $date ) {

		if ( null === $date ) {
			return null;
		}

		/**
		 * The FrontYAML parser (Mni\FrontYAML\Parser::getYAML()) behaves inconsistently
		 * when parsing the `date` field:
		 *
		 * - If the date is written as an unquoted string like `date: 2025-07-25 10:06:15`,
		 *   the parser automatically converts it into a UNIX timestamp (integer).
		 *
		 * - If the date is written as a quoted string like `date: '2025-07-25 10:06:15'`,
		 *   the parser preserves it as a plain string.
		 *
		 * This inconsistency requires preprocessing to ensure all dates are normalized
		 * to the 'Y-m-d H:i:s' string format.
		 */

		// If it's a DateTime object (less common, but possible), format it to string.
		if ( $date instanceof DateTimeInterface ) {
			$date = $date->format( 'Y-m-d H:i:s' );
		}

		// If it's a numeric UNIX timestamp (due to unquoted YAML date), convert to string format.
		elseif ( is_numeric( $date ) && (int) $date == $date ) {
			$date = gmdate( 'Y-m-d H:i:s', (int) $date );
		}

		// At this point, we expect $date to be a string in 'Y-m-d H:i:s' format.
		if ( !is_string( $date ) ) {
			throw new Exception( 'invalid_date_format' );
		}

		// Validate the final date string format using a strict 24-hour regex.
		$date_regex = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])\s([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/';

		if ( !preg_match( $date_regex, $date ) ) {
			return null;
		}

		return $date;
	}

	/**
	 * If the provided status is valid returns the status, otherwise returns false.
	 *
	 * Note that only six of the height default WordPress statuses are valid.
	 * See: https://wordpress.org/support/article/post-status/
	 *
	 * @param $status
	 *
	 * @return mixed|null
	 */
	private function prepare_status( $status ) {

		$valid_statuses = array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' );
		if ( in_array( $status, $valid_statuses ) ) {
			return $status;
		} else {
			return null;
		}
	}

	/**
	 * If the provided slug is valid returns the slug, otherwise returns false.
	 *
	 * See: https://wordpress.org/support/article/post-status/
	 *
	 * @param $status
	 *
	 * @return mixed|null
	 */
	private function prepare_slug( $slug ) {

		if ( !is_null($slug) && trim( $slug ) > 0 ) {
			return $slug;
		} else {
			return null;
		}
	}

	/**
	 *
	 * This method does what follows:
	 *
	 * - If the provided thumbnail is a valid Thumbnail ID returns the Thumbnail ID.
	 * - If the provided value is a valid image hosted on the web download the image, create a new image in the media
	 * library, and return the Thumbnail ID.
	 * - If the provided value is not a valid Thumbnail ID and is neither a valid image hosted on the web returns false.
	 *
	 * See: https://wordpress.org/support/article/post-status/
	 *
	 * @param $status
	 *
	 * @return mixed|null
	 */
	private function prepare_thumbnail( $thumbnail, $thumbnail_alt, $thumbnail_title ) {

		if ( wp_attachment_is( 'image', $thumbnail ) ) {

			return $thumbnail;

		} else {

			// Thumbnail alt.
			if ( is_null($thumbnail_alt) || strlen( trim( $thumbnail_alt ) ) === 0 ) {
				$thumbnail_alt = null;
			}

			// Thumbnail title.
			if ( is_null($thumbnail_title) || strlen( trim( $thumbnail_title ) ) === 0 ) {
				$thumbnail_title = null;
			}

			// Upload the referenced images to the media library and update the URLs.
			$thumbnail_id = $this->shared->create_image_attachment_from_image_url(
				$thumbnail,
				$thumbnail_alt,
				$thumbnail_title
			);

			if ( false !== $thumbnail_id ) {
				return $thumbnail_id;
			} else {
				return null;
			}
		}
	}

	/**
	 * Dumps the provided data as a YAML string.
	 *
	 * @param array $data The data to dump.
	 *
	 * @return string The YAML string.
	 */
	public function dump_yaml(array $data): string {

		// Dumps the provided data as a YAML string using the Symfony YAML component.
		require_once $this->shared->get( 'dir' ) . 'globalize-symfony-yaml.php';
		global $symfony_yaml;
		$yaml_string = $symfony_yaml->dump($data);

		// Wrap the YAML string in front matter delimiters and return the string.
		return "---\n" . $yaml_string . "---\n";

	}

	/**
	 * Generates the Front Matter from the post information.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return array The Front Matter fields.
	 */
	public function generate_front_matter_from_post($post_id){

		if ( 0 === intval( get_option( 'daextulmap_exported_files_include_front_matter' ), 10 ) ) {
			return array();
		}

		// Get the post object.
		$post = get_post( $post_id );

		/**
		 * The Front Matter fields are generated based on the post information and meta data. The following fields
		 * are used:
		 *
		 * ---
		 * title: The post title
		 * excerpt: The post excerpt
		 * categories:
		 * - Category 1
		 * - Category 2
		 * tags:
		 * - Tag 1
		 * - Tag 2
		 * author: John Doe
		 * date: 2022-04-16 10:30:42
		 * status: private
		 * slug: my-first-article
		 * thumbnail: https://example.com/laptop-computer.png
		 * thumbnail_alt: Laptop computer displaying the logo of WordPress
		 * thumbnail_title: WordPress is a free and open-source content management system written in PHP
		 * ---
		 */
		$front_matter = array(
			'title'           => get_the_title( $post->ID ),
			'excerpt'         => get_the_excerpt( $post->ID ),
			'categories'      => wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) ),
			'tags'            => wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) ),
			'author'          => get_the_author_meta( 'display_name', get_post_field( 'post_author', $post->ID ) ),
			'date'            => strtotime( get_the_date( 'Y-m-d H:i:s', $post->ID ) ),
			'status'          => get_post_status( $post->ID ),
			'slug'            => get_post_field( 'post_name', $post->ID ),
			'thumbnail'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'thumbnail_alt'   => get_post_meta( get_post_thumbnail_id( $post->ID ), '_wp_attachment_image_alt', true ),
			'thumbnail_title' => get_the_title( get_post_thumbnail_id( $post->ID ) ),
		);

		return $front_matter;

	}

}
