<?php
/**
 * YoghBioLink setup
 *
 * @package YoghBioLink
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main YoghBioLink Class.
 *
 * @class YoghBioLink
 */
final class YoghBioLink {

	/**
	 * YoghBioLink version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * YoghBioLink Schema version.
	 *
	 * @since 1.0 started with version string 100.
	 *
	 * @var string
	 */
	public $db_version = '100';

	/**
	 * The single instance of the class.
	 *
	 * @var YoghBioLink
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main YoghBioLink Instance.
	 *
	 * Ensures only one instance of YoghBioLink is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see YBL()
	 * @return YoghBioLink - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * YoghBioLink Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', YBL_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( YBL_PLUGIN_FILE ) );
	}
}
