<?php
/**
 * Plugin Update Checker.
 *
 * @package Daextteam\PluginUpdateChecker
 */

namespace Daextteam\PluginUpdateChecker;

/**
 * This class enables seamless updates for WordPress plugins
 * hosted on daext.com. It is also used to perform
 * license validation checks.
 *
 * In the plugins, this class is used in four different ways:
 *
 * 1. To enable plugin updates. In this case the class performs the following:
 *    - Displays update details in the WordPress "Plugins" menu after clicking "View version x.xx details".
 *    - Automatically notifies administrators when a new version is available using the "Updates" menu and in other
 * contexts.
 *    - Facilitates plugin downloads by including update information in the plugin details
 *      returned to WordPress, allowing it to download the update from the daext.com server.
 *
 * 2. To perform a license check after clicking the manual license verification link
 *    shown in the invalid license notification.
 *
 * 3. To update the transient (used to store the plugin update information on the WordPress options) after saving the
 * plugin options.
 *
 * 4. To display or hide the invalid license notification message in the plugin's UI menus.
 *
 * Notes:
 *
 * - When used to enable plugin updates, it integrates with the WordPress update system
 *   by hooking into the "plugins_api" and "site_transient_update_plugins" filters.
 *
 * - When used for license validation, the license provider and license key
 *   are retrieved from stored options and sent as part of the remote API calls.
 *   If the license is valid, the daext.com API returns plugin information, and this is considered
 *   a valid license response. If the license is invalid, the API returns an error,
 *   and this is considered as an invalid license.
 */
class PluginUpdateChecker {

	/**
	 * Library version.
	 *
	 * This is for informational purposes only and does not control update logic.
	 */
	const VERSION = '1.0.11';

	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	private $slug = null;

	/**
	 * The URL of the API endpoint used to get the plugin information.
	 *
	 * @var mixed
	 */
	private $wp_plugin_update_info_api_url = null;

	/**
	 * The transient used to store the plugin update information.
	 *
	 * @var mixed
	 */
	private $wp_plugin_update_info_transient = null;

	/**
	 * The license provider.
	 *
	 * @var mixed
	 */
	private $license_provider = null;

	/**
	 * The license key.
	 *
	 * @var mixed
	 */
	private $license_key = null;

	/**
	 * Constructor.
	 *
	 * @param array $settings The settings.
	 */
	public function __construct( $settings ) {
		$this->slug                            = $settings['slug'];
		$this->wp_plugin_update_info_api_url   = $settings['wp_plugin_update_info_api_url'];
		$this->wp_plugin_update_info_transient = $settings['prefix'] . '_wp_plugin_update_info';
		$this->license_provider                = get_option($settings['prefix'] . '_license_provider');
		$this->license_key                     = get_option($settings['prefix'] . '_license_key');
	}

	/**
	 * Register the update checker callbacks on filters.
	 *
	 * @return void
	 */
	public function register_callbacks_on_filters() {

		/**
		 * Filters the response for the current WordPress.org Plugin Installation API request performed with
		 * the plugins_api() method.
		 *
		 * - https://developer.wordpress.org/reference/functions/plugins_api/
		 *
		 * This filter os used to provide the plugin information for our custom plugin. More in details, by modifying
		 * the result of the "plugins_api" filter callback, we can include the plugin information for our custom plugin.
		 *
		 * This filter runs for example when the user clicks on the "View version x.xx details." link in the Plugins
		 * menu of the WordPress dashboard.
		 *
		 * ## When is the "plugins_api" filter Used?
		 *
		 * The plugins_api filter is involved when WordPress fetches information about a plugin, such as:
		 *
		 * - Searching for plugins on the Plugins > Add New screen.
		 * - Displaying details about a plugin (e.g., clicking "More Details").
		 * - Showing lists of plugins (e.g., popular, recommended, or featured).
		 *
		 * Ref: https://developer.wordpress.org/reference/hooks/plugins_api/
		 */
		add_filter( 'plugins_api', array( $this, 'info' ), 20, 3 );

		/**
		 * Filter the "_site_transient_update_plugins" transient to report the plugin as out of date.
		 *
		 * If an update is available, the plugin update information are added to the "_site_transient_update_plugins"
		 * transient and as a consequence the notification, with included a link to update the plugin, will be displayed
		 * in the WordPress dashboard.
		 *
		 * When the Filter Runs:
		 *
		 * - This filter is applied when WordPress is about to return the result of a call of get_site_transient(),
		 * specifically when call is get_site_transient( 'update_plugins' ). This instruction is used in some WordPress core
		 * files and functions.
		 *
		 * Ref:
		 *
		 * - https://developer.wordpress.org/reference/functions/get_site_transient/
		 * - https://developer.wordpress.org/reference/hooks/site_transient_transient/
		 * - https://gist.github.com/danielbachhuber/7684646
		 *
		 * Notes:
		 * - The "_site_transient_update_plugins" transient contains information about all installed plugins and whether
		 * there are updates available.
		 * - The transient is cached and only updated when a scheduled update check runs, or when manually triggered
		 * (e.g., via the "Check for updates" button in the dashboard).
		 * - The plugin information added with this filter are also used to perform the update and download of the
		 * custom plugin for example when the user clicks on the "Update now" link in the Plugins menu of the WordPress
		 * dashboard.
		 *
		 * Note that the plugin information added with this filter are also used to perform the update and download of
		 * the custom plugin for example when the user clicks on the "Update now" link in the Plugins menu of the WordPress
		 * dashboard.
		 */
		add_filter( 'site_transient_update_plugins', array( $this, 'update' ) );

	}

	/**
	 * Include plugin information for our custom plugin using the callback of the "plugins_api" filter.
	 *
	 * The plugin information for the plugin is included when:
	 *
	 * - The $action is set to "plugin_information".
	 * - The $arg->slug is equal to "[plugin-slug]/init.php".
	 *
	 * Note that returning a non-false value will effectively short-circuit the WordPress.org API request.
	 *
	 * @param false|object|array $res This is the current result or false by default. You can modify it to return custom data instead of letting the plugins_api() function proceed.
	 * @param string             $action The action being requested.
	 * @param object             $arg The arguments passed to plugins_api() that control the details of the request. These can include the plugin slug, search terms, etc.
	 *
	 * @return mixed|stdClass
	 */
	public function info( $res, $action, $arg ) {

		// Do nothing if you're not getting plugin information right now.
		if ( 'plugin_information' !== $action ) {
			return $res;
		}

		// Do nothing if it is not this plugin.
		if ( ! isset( $arg->slug ) || $this->slug !== $arg->slug ) {
			return $res;
		}

		// Fetch the plugin information from the remote server.
		$fetched_plugin_info = $this->fetch_remote_plugin_info();

		/**
		 * Return the unmodified response under specific conditions.
		 */
		if ( ! $this->response_is_valid( $fetched_plugin_info ) ) {
			return $res;
		}

		$res = new \stdClass();

		$res->name           = $fetched_plugin_info->name;
		$res->slug           = $fetched_plugin_info->slug;
		$res->version        = $fetched_plugin_info->version;
		$res->tested         = $fetched_plugin_info->tested;
		$res->requires       = $fetched_plugin_info->requires;
		$res->author         = $fetched_plugin_info->author;
		$res->author_profile = $fetched_plugin_info->author_profile;
		$res->requires_php   = $fetched_plugin_info->requires_php;
		$res->last_updated   = $fetched_plugin_info->last_updated;

		$res->sections = array(
			'changelog' => $fetched_plugin_info->sections->changelog,
		);

		if ( ! empty( $fetched_plugin_info->banners ) ) {
			$res->banners = array(
				'low'  => $fetched_plugin_info->banners->low,
				'high' => $fetched_plugin_info->banners->high,
			);
		}

		return $res;
	}

	/**
	 *  Report the plugin as out of date if needed by adding information to the transient.
	 *
	 *  Details on the transient "_site_transient_update_plugins" structure:
	 *
	 * The "_site_transient_update_plugins" transient stores a complex object containing
	 * various pieces of information about installed plugins, primarily to manage and check
	 * for updates. This transient is updated and retrieved by WordPress as part of the plugin
	 * update process.
	 *
	 * Breakdown of main properties:
	 *
	 * - last_checked
	 *   - Type: int (Unix timestamp)
	 *   - Description: Records the last time WordPress checked for plugin updates.
	 *   - Purpose: Helps determine whether it’s time to recheck updates. WordPress generally
	 *     schedules this check every 12 hours by default.
	 *
	 * - checked
	 *   - Type: array
	 *   - Description: A key-value array of all active plugins and their currently installed versions.
	 *   - Key: The plugin's main file path (e.g., my-plugin/my-plugin.php).
	 *   - Value: The version number of the installed plugin as a string (e.g., 1.0.0).
	 *   - Purpose: Used to compare each plugin's installed version against the latest version
	 *     available on the WordPress plugin repository (or other sources) to determine if an update
	 *     is necessary.
	 *
	 * - response
	 *   - Type: array
	 *   - Description: Holds information about plugins that have updates available.
	 *   - Key: The plugin's main file path (e.g., my-plugin/my-plugin.php).
	 *   - Value: An object containing detailed update data:
	 *     - slug: Plugin slug, often used to identify the plugin (e.g., my-plugin).
	 *     - new_version: The latest version available (e.g., 1.1.0).
	 *     - package: The URL to download the plugin update package.
	 *     - tested: The latest WordPress version the update has been tested with.
	 *     - requires: The minimum required WordPress version for this update.
	 *     - Other Fields: Some updates may include additional fields like changelogs or update-specific notes.
	 *
	 * - no_update
	 *   - Type: array
	 *   - Description: Similar in structure to response, this array contains data for plugins
	 *     that are up-to-date (i.e., no updates are available).
	 *   - Key: The plugin's main file path.
	 *   - Value: An object with details about the plugin’s current version and update status,
	 *     including properties like slug, new_version, and potentially other metadata.
	 *
	 * - translations
	 *   - Type: array
	 *   - Description: Holds translation files available for each plugin.
	 *   - Content: Each item in the array contains information about language packs, including
	 *     the URL to download the translations, the language, and other metadata.
	 *   - Purpose: Manages available translations for plugins, helping WordPress automatically
	 *     install or update translation files.
	 *
	 * - version_checked
	 *   - Type: string (rarely used, optional)
	 *   - Description: Typically set to the WordPress version checked during the last update,
	 *     to keep track of compatibility.
	 *   - Purpose: Primarily for internal use in ensuring plugins align with the checked WordPress version.
	 *
	 * Note 1: The version_checked property is not always present in the _site_transient_update_plugins transient,
	 * and it’s actually quite rare for it to be included. The reason for this is that this field is not always set or
	 * needed in the transient, depending on the context and the way the updates are being checked.
	 *
	 * Note 2: This file runs multiple times when WordPress pages are loaded, this function updates the
	 * transient->response property each time by adding the plugin update information.
	 *
	 * @param array $transient This is the transient "_site_transient_update_plugins" that contains the information
	 *  about the plugin updates required.
	 */
	public function update( $transient ) {

		/**
		 * If the transient "checked" is empty return the transient. The plugin update information will be added to the
		 * transient when the "checked" property is not empty.
		 */
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$plugin_slug = $this->slug . '/init.php';

		/**
		 * If the plugin slug is not set in the transient "checked" property, return the transient. The plugin update
		 * information will be added when the plugin slug is available in the transient "checked" property.
		 *
		 * Note: This check has been added to prevent PHP warnings (E.g. "PHP Warning:  Undefined array key") caused by
		 * the fact that sometimes (due to timing) the $plugin_slug array key is not present in the transient "checked"
		 * property.
		 */
		if ( ! isset( $transient->checked[ $plugin_slug ] ) ) {
			return $transient;
		}

		$current_version = $transient->checked[ $plugin_slug ];

		// Fetch the plugin information from the remote server.
		$fetched_plugin_info = $this->fetch_remote_plugin_info();

		/**
		 * Under the following conditions, add the plugin update information to the transient:
		 *
		 * - The fetched plugin info are valid.
		 * - The current version is less than the fetched plugin version.
		 */
		if ( $this->response_is_valid( $fetched_plugin_info ) &&
		     version_compare( $current_version, $fetched_plugin_info->version, '<' )
		) {

			// Prepare the plugin update data.
			$transient->response[ $plugin_slug ] = (object) array(
				'slug'         => $fetched_plugin_info->slug,
				'plugin'       => $plugin_slug,
				'new_version'  => $fetched_plugin_info->version,
				'package'      => $fetched_plugin_info->download_url,
				'banners'      => array(
					'1x' => $fetched_plugin_info->banners->low,
					'2x' => $fetched_plugin_info->banners->high,
				),
				'requires'     => $fetched_plugin_info->requires,
				'tested'       => $fetched_plugin_info->tested,
				'requires_php' => $fetched_plugin_info->requires_php,
			);

		}

		return $transient;
	}

	/**
	 * Check if the response is valid.
	 *
	 * @param object $response The response object.
	 *
	 * @return bool
	 */
	public function response_is_valid( $response ) {

		if (
			( isset( $response->error ) && 'invalid_license' === $response->error ) ||
			is_wp_error( $response ) ||
			false === $response
		) {
			return false;
		} else {
			return true;
		}
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

		$remote = get_transient( $this->wp_plugin_update_info_transient );

		/**
		 * If the transient does not exist, does not have a value, or has expired, then fetch the plugin information
		 * from the remote server on daext.com.
		 */
		if ( false === $remote ) {

			// Prepare the body of the request.
			$body = wp_json_encode(
				array(
					'license_provider' => $this->license_provider,
					'license_key'      => $this->license_key,
					'slug'             => $this->slug,
					'domain'           => site_url(),
				)
			);

			$remote = wp_remote_post(
				$this->wp_plugin_update_info_api_url,
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

					$error_res = new \WP_Error( 'invalid_license', 'Invalid License' );
					set_transient( $this->wp_plugin_update_info_transient, $error_res, $transient_expiration );

				} else {

					/**
					 * With other error response codes save a generic error response in the transient.
					 */
					$error_res = new \WP_Error( 'generic_error', 'Generic Error' );
					set_transient( $this->wp_plugin_update_info_transient, $error_res, $transient_expiration );

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
					set_transient( $this->wp_plugin_update_info_transient, $remote, $transient_expiration );
					return $remote;
				} else {
					$error_res = new \WP_Error( 'generic_error', 'Generic Error' );
					set_transient( $this->wp_plugin_update_info_transient, $error_res, $transient_expiration );
					return $error_res;
				}
			}
		}

		return $remote;
	}

	/**
	 * Delete the transient used to store the plugin update information.
	 *
	 * @return void
	 */
	public function delete_transient() {
		delete_transient( $this->wp_plugin_update_info_transient );
	}

	/**
	 * Verify the license key. If the license is not valid display a message and return false.
	 *
	 * @return bool
	 */
	public function is_valid_license() {

		$plugin_info = get_transient( $this->wp_plugin_update_info_transient );

		if ( false === $plugin_info || is_wp_error( $plugin_info ) ) {
			return false;
		} else {
			return true;
		}
	}
}
