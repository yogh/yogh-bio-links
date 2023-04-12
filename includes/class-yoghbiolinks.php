<?php
/**
 * YoghBioLinks setup
 *
 * @package YoghBioLinks
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main YoghBioLinks Class.
 *
 * @class YoghBioLinks
 */
final class YoghBioLinks {

	/**
	 * YoghBioLinks version.
	 *
	 * @var string
	 */
	public $version = '1.0.0-beta2';

	/**
	 * YoghBioLinks Schema version.
	 *
	 * @since 1.0 started with version string 100.
	 *
	 * @var string
	 */
	public $db_version = '100';

	/**
	 * The single instance of the class.
	 *
	 * @var YoghBioLinks
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main YoghBioLinks Instance.
	 *
	 * Ensures only one instance of YoghBioLinks is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see YOGHBL()
	 * @return YoghBioLinks - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * YoghBioLinks Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define YoghBL Constants.
	 */
	private function define_constants() {
		$this->define( 'YOGHBL_ABSPATH', dirname( YOGHBL_PLUGIN_FILE ) . '/' );
		$this->define( 'YOGHBL_VERSION', $this->version );
		$this->define( 'YOGHBL_TEMPLATE_DEBUG_MODE', false );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		/**
		 * Core classes.
		 */
		include_once YOGHBL_ABSPATH . 'includes/yoghbl-core-functions.php';
		include_once YOGHBL_ABSPATH . 'includes/class-yoghbl-install.php';
		include_once YOGHBL_ABSPATH . 'includes/customizer/class-yoghbl-customizer.php';
		include_once YOGHBL_ABSPATH . 'includes/class-yoghbl-custom-colors.php';
		include_once YOGHBL_ABSPATH . 'includes/admin/class-yoghbl-admin-bar.php';

		if ( $this->is_request( 'admin' ) ) {
			include_once YOGHBL_ABSPATH . 'includes/admin/class-yoghbl-admin.php';
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}

		/**
		 * Self Directory
		 */
		include_once YOGHBL_ABSPATH . 'includes/selfdirectory/class-selfdirectory.php';
	}

	/**
	 * Include required frontend files.
	 */
	public function frontend_includes() {
		include_once YOGHBL_ABSPATH . 'includes/yoghbl-template-hooks.php';
		include_once YOGHBL_ABSPATH . 'includes/class-yoghbl-template-loader.php';
		include_once YOGHBL_ABSPATH . 'includes/class-yoghbl-frontend-scripts.php';
	}

	/**
	 * Function used to Init YoghBioLinks Template Functions - This makes them pluggable by plugins and themes.
	 */
	public function include_template_functions() {
		include_once YOGHBL_ABSPATH . 'includes/yoghbl-template-functions.php';
	}

	/**
	 * Init YoghBioLinks when WordPress Initialises.
	 */
	public function init() {
		/**
		 * Action triggered before YoghBioLinks initialization begins.
		 */
		do_action( 'before_yoghbl_init' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		/**
		 * Action triggered after YoghBioLinks initialization finishes.
		 */
		do_action( 'yoghbl_init' );
	}

	/**
	 * When WP has loaded all plugins, trigger the `yoghbl_loaded` hook.
	 *
	 * This ensures `yoghbl_loaded` is called only after all other plugins
	 * are loaded, to avoid issues caused by plugin directory naming changing
	 * the load order.
	 */
	public function on_plugins_loaded() {
		/**
		 * Action to signal that YoghBL has finished loading.
		 */
		do_action( 'yoghbl_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( YOGHBL_PLUGIN_FILE, array( 'YoghBL_Install', 'install' ) );
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), -1 );
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'selfd_register', array( $this, 'register_selfdirectory' ) );
		add_filter( 'elementor/theme/need_override_location', array( $this, 'elementor_theme_need_override_location' ) );
	}

	public function elementor_theme_need_override_location( $need_override_location ) {
		if ( yoghbl_get_page_id( 'biolinks' ) === get_the_ID() ) {
			$need_override_location = false;
		}
		return $need_override_location;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', YOGHBL_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( YOGHBL_PLUGIN_FILE ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		/**
		 * Filter to adjust the base templates path.
		 */
		return apply_filters( 'yoghbl_template_path', 'yoghbiolinks/' );
	}

	/**
	 * Register selfdirectory.
	 */
	public function register_selfdirectory() {
		selfd( YOGHBL_PLUGIN_FILE );
	}
}
