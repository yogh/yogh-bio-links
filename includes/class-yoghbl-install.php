<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Installation related functions and actions.
 *
 * @package YoghBioLinks\Classes
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * YoghBL_Install Class.
 */
class YoghBL_Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * Please note that these functions are invoked when YoghBioLinks is updated from a previous version,
	 * but NOT when YoghBioLinks is newly installed.
	 *
	 * Database schema changes must be incorporated to the SQL returned by get_schema, which is applied
	 * via dbDelta at both install and update time. If any other kind of database change is required
	 * at install time (e.g. populating tables), use the 'yoghbl_installed' hook.
	 *
	 * @var array
	 */
	private static $db_updates = array();

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
	}

	/**
	 * Check YoghBioLink version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		$yoghbl_version      = sanitize_text_field(get_option( 'yoghbl_version', '1.0.0' ));
		
		$yoghbl_code_version = wp_strip_all_tags(YoghBL()->version);
		$requires_update     = version_compare( $yoghbl_version, $yoghbl_code_version, '<' );
		
		if ( ! defined( 'IFRAME_REQUEST' ) && $requires_update ) {
			self::install();
			do_action( 'yoghbl_updated' );
			// If there is no yoghbl_version option, consider it as a new install.
			if ( ! $yoghbl_version ) {
				/**
				 * Run when YoghBL has been installed for the first time.
				 */
				do_action( 'yoghbl_newly_installed' );
			}
		}
	}

	/**
	 * Install YoghBL.
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( self::is_installing() ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'yoghbl_installing', 'yes', MINUTE_IN_SECONDS * 10 );
		yoghbl_maybe_define_constant( 'YOGHBL_INSTALLING', true );

		self::maybe_create_pages();
		self::maybe_update_db_version();

		// Use add_option() here to avoid overwriting this value with each
		// plugin version update. We base plugin age off of this value.
		add_option( 'yoghbl_admin_install_timestamp', time() );

		/**
		 * Run after YoghBioLinks has been installed or updated.
		 */
		do_action( 'yoghbl_installed' );
	}

	/**
	 * Returns true if we're installing.
	 *
	 * @return bool
	 */
	private static function is_installing() {
		return 'yes' === get_transient( 'yoghbl_installing' );
	}

	/**
	 * Is a DB update needed?
	 *
	 * @return boolean
	 */
	public static function needs_db_update() {
		$current_db_version = sanitize_text_field(get_option( 'yoghbl_db_version', null ));
		$updates            = self::get_db_update_callbacks();
		$update_versions    = array_keys( $updates );
		usort( $update_versions, 'version_compare' );

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}

	/**
	 * See if we need to show or run database updates during install.
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			self::update();
		} else {
			self::update_db_version();
		}
	}

	/**
	 * Update YoghBL version to current.
	 */
	private static function update_yoghbl_version() {
		update_option( 'yoghbl_version', sanitize_text_field(YoghBL()->version) );
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string|null $version New YoghBioLinks DB version or null.
	 */
	public static function update_db_version( $version = null ) {
		update_option( 'yoghbl_db_version', is_null( $version ) ? sanitize_text_field(YoghBL()->version) : sanitize_text_field($version) );
	}

	/**
	 * Create pages on installation.
	 */
	public static function maybe_create_pages() {
		if ( empty( sanitize_text_field(get_option( 'yoghbl_db_version' )) ) ) {
			self::create_pages();
		}
	}

	/**
	 * Create pages that the plugin relies on, storing page IDs in variables.
	 */
	public static function create_pages() {
		include_once dirname( __FILE__ ) . '/admin/yoghbl-admin-functions.php';

		/**
		 * Determines which pages are created during install.
		 */
		$pages = apply_filters(
			'yoghbl_create_pages',
			array(
				'biolinks' => array(
					'name'  => _x( 'biolinks', 'Page slug', 'yogh-bio-links' ),
					'title' => _x( 'Bio Links', 'Page title', 'yogh-bio-links' ),
				),
			)
		);

		foreach ( $pages as $key => $page ) {
			yoghbl_create_page(
				esc_sql( $page['name'] ),
				'yoghbl_' . $key . '_page_id',
				$page['title'],
				isset( $page['content'] ) ? $page['content'] : '',
				! empty( $page['parent'] ) ? yoghbl_get_page_id( $page['parent'] ) : '',
				! empty( $page['post_status'] ) ? $page['post_status'] : 'publish'
			);
		}
	}
}

YoghBL_Install::init();
