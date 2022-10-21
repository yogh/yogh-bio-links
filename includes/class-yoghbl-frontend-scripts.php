<?php
/**
 * Handle frontend scripts
 *
 * @package YoghBioLinks\Classes
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Frontend scripts class.
 */
class YoghBL_Frontend_Scripts {

	/**
	 * Contains an array of script handles registered by YoghBL.
	 *
	 * @var array
	 */
	private static $scripts = array();

	/**
	 * Contains an array of script handles registered by YoghBL.
	 *
	 * @var array
	 */
	private static $styles = array();

	/**
	 * Contains an array of script handles localized by YoghBL.
	 *
	 * @var array
	 */
	private static $wp_localize_scripts = array();

	/**
	 * Contains an array of style handles added styles by YoghBL.
	 *
	 * @var array
	 */
	private static $added_inline_style = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_inline_styles' ), 12 );
		add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
	}

	/**
	 * Get styles for the frontend.
	 *
	 * @return array
	 */
	public static function get_styles() {
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;

		/**
		 * Filter list of YoghBioLinks styles to enqueue.
		 *
		 * @param array List of default YoghBioLinks styles.
		 * @retrun array List of styles to enqueue.
		 */
		$styles = apply_filters(
			'yoghbl_enqueue_styles',
			array(
				'yoghbiolinks-general' => array(
					'src'     => self::get_asset_url( 'assets/css/yoghbiolinks.css' ),
					'deps'    => array(),
					'version' => $version,
					'media'   => 'all',
					'has_rtl' => true,
				),
			)
		);
		return is_array( $styles ) ? array_filter( $styles ) : array();
	}

	/**
	 * Return asset URL.
	 *
	 * @param string $path Assets path.
	 * @return string
	 */
	private static function get_asset_url( $path ) {
		return apply_filters( 'yoghbl_get_asset_url', plugins_url( $path, YOGHBL_PLUGIN_FILE ), $path );
	}

	/**
	 * Register a script for use.
	 *
	 * @uses   wp_register_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = YOGHBL_VERSION, $in_footer = true ) {
		self::$scripts[] = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @uses   wp_enqueue_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = YOGHBL_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts, true ) && $path ) {
			self::register_script( $handle, $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle );
	}

	/**
	 * Register a style for use.
	 *
	 * @uses   wp_register_style()
	 * @param  string   $handle  Name of the stylesheet. Should be unique.
	 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
	 * @param  string   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
	 * @param  boolean  $has_rtl If has RTL version to load too.
	 */
	private static function register_style( $handle, $path, $deps = array(), $version = YOGHBL_VERSION, $media = 'all', $has_rtl = false ) {
		self::$styles[] = $handle;
		wp_register_style( $handle, $path, $deps, $version, $media );

		if ( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 * Register and enqueue a styles for use.
	 *
	 * @uses   wp_enqueue_style()
	 * @param  string   $handle  Name of the stylesheet. Should be unique.
	 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
	 * @param  string   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
	 * @param  boolean  $has_rtl If has RTL version to load too.
	 */
	private static function enqueue_style( $handle, $path = '', $deps = array(), $version = YOGHBL_VERSION, $media = 'all', $has_rtl = false ) {
		if ( ! in_array( $handle, self::$styles, true ) && $path ) {
			self::register_style( $handle, $path, $deps, $version, $media, $has_rtl );
		}
		wp_enqueue_style( $handle );
	}

	/**
	 * Register all YoghBL scripts.
	 */
	private static function register_scripts() {
		$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;

		$register_scripts = array();
		foreach ( $register_scripts as $name => $props ) {
			self::register_script( $name, $props['src'], $props['deps'], $props['version'] );
		}
	}

	/**
	 * Register all YoghBL styles.
	 */
	private static function register_styles() {
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;

		$register_styles = array();
		foreach ( $register_styles as $name => $props ) {
			self::register_style( $name, $props['src'], $props['deps'], $props['version'], 'all', $props['has_rtl'] );
		}
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {
		if ( ! did_action( 'before_yoghbl_init' ) || ! is_yoghbiolinks() ) {
			return;
		}

		self::register_scripts();
		self::register_styles();

		// CSS Styles.
		$enqueue_styles = self::get_styles();
		if ( $enqueue_styles ) {
			foreach ( $enqueue_styles as $handle => $args ) {
				if ( ! isset( $args['has_rtl'] ) ) {
					$args['has_rtl'] = false;
				}

				self::enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'], $args['has_rtl'] );
			}
		}
	}

	/**
	 * Localize a YoghBL script once.
	 *
	 * @param string $handle Script handle the data will be attached to.
	 */
	private static function localize_script( $handle ) {
		if ( ! in_array( $handle, self::$wp_localize_scripts, true ) && wp_script_is( $handle ) ) {
			$data = self::get_script_data( $handle );

			if ( ! $data ) {
				return;
			}

			$name                        = str_replace( '-', '_', $handle ) . '_params';
			self::$wp_localize_scripts[] = $handle;
			wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
		}
	}

	/**
	 * Return data for script handles.
	 *
	 * @param  string $handle Script handle the data will be attached to.
	 * @return array|bool
	 */
	private static function get_script_data( $handle ) {
		switch ( $handle ) {
			default:
				$params = false;
		}

		return apply_filters( 'yoghbl_get_script_data', $params, $handle );
	}

	/**
	 * Localize scripts only when enqueued.
	 */
	public static function localize_printed_scripts() {
		foreach ( self::$scripts as $handle ) {
			self::localize_script( $handle );
		}
	}

	/**
	 * Return extra CSS for styles handles.
	 *
	 * @param  string $handle Style handle the extra CSS styles will be attached to.
	 * @return array|bool
	 */
	private static function get_extra_style_data( $handle ) {
		switch ( $handle ) {
			case 'yoghbiolinks-general':
				$data = array();

				// Add stick footer height 100% with or without admin bar.
				$html = array( 'font-size:initial' );
				if ( is_admin_bar_showing() ) {
					$html   = array_merge( array( 'height:calc(100% - 32px)' ), $html );
					$data[] = '@media screen and (max-width:782px){html{height:calc(100% - 46px)}}';
				}
				$data = array_merge( array( sprintf( 'html{%s}', implode( ';', $html ) ) ), $data );

				$data = implode( "\n", $data );
				break;
			default:
				$data = false;
		}

		return apply_filters( 'yoghbl_get_style_inline', $data, $handle );
	}

	/**
	 * Add inline style only when enqueued.
	 */
	public static function add_inline_styles() {
		foreach ( self::$styles as $handle ) {
			self::add_inline_style( $handle );
		}
	}

	/**
	 * Add inline style to a YoghBL style once.
	 *
	 * @param string $handle Style handle the inline style will be attached to.
	 */
	private static function add_inline_style( $handle ) {
		if ( ! in_array( $handle, self::$added_inline_style, true ) && wp_style_is( $handle ) ) {
			$data = self::get_extra_style_data( $handle );

			if ( ! $data ) {
				return;
			}

			$name = str_replace( '-', '_', $handle ) . '_data';
			$data = str_replace( "\n", '', apply_filters( $name, $data ) );

			self::$added_inline_style[] = $handle;
			wp_add_inline_style( $handle, $data );
		}
	}
}

YoghBL_Frontend_Scripts::init();
