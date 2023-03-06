<?php
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
	 * at install time (e.g. populating tables), use the 'yoghbiolinks_installed' hook.
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
		$yoghbl_version      = get_option( 'yoghbiolinks_version' );
		$yoghbl_code_version = YoghBL()->version;
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
		self::maybe_get_translation();

		// Use add_option() here to avoid overwriting this value with each
		// plugin version update. We base plugin age off of this value.
		add_option( 'yoghbiolinks_admin_install_timestamp', time() );

		/**
		 * Run after YoghBioLinks has been installed or updated.
		 */
		do_action( 'yoghbiolinks_installed' );
	}

	/**
	 * Maybe get remote translation and copy to WP_LANG_DIR.
	 *
	 * @todo Need to be removed before directory publish.
	 */
	public static function maybe_get_translation() {
		global $wp_filesystem;

		$locale           = get_locale();
		$file_basename    = "yogh-bio-links-{$locale}.mo";
		$lang_dir_plugins = WP_LANG_DIR . '/plugins';
		$destination      = "$lang_dir_plugins/{$file_basename}";
		if ( ! file_exists( $destination ) ) {

			// Ensure WP_Filesystem() is declared.
			require_once ABSPATH . 'wp-admin/includes/file.php';

			if ( file_exists( YOGHBL_ABSPATH . "languages/{$file_basename}" ) ) {
				$tmpfname = YOGHBL_ABSPATH . "languages/{$file_basename}";
			} else {
				$version  = preg_replace( '/[^\d.]/', '', YoghBL()->version );
				$url      = "https://github.com/yogh/yogh-bio-links/raw/translate/v{$version}/{$file_basename}";
				$tmpfname = download_url( $url );
			}

			if ( is_wp_error( $tmpfname ) ) {
				return;
			}

			WP_Filesystem();

			if ( ! $wp_filesystem->exists( $lang_dir_plugins ) ) {
				if ( ! $wp_filesystem->mkdir( $lang_dir_plugins, FS_CHMOD_DIR ) ) {
					return;
				}
			}

			if ( ! $wp_filesystem->is_writable( $lang_dir_plugins ) ) {
				return;
			}

			$wp_filesystem->move( $tmpfname, $destination );
		}
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
		$current_db_version = get_option( 'yoghbiolinks_db_version', null );
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
		update_option( 'yoghbiolinks_version', YoghBL()->version );
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
		update_option( 'yoghbiolinks_db_version', is_null( $version ) ? YoghBL()->version : $version );
	}

	/**
	 * Create pages on installation.
	 */
	public static function maybe_create_pages() {
		if ( empty( get_option( 'yoghbiolinks_db_version' ) ) ) {
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
