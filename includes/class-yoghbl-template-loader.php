<?php
/**
 * Template Loader
 *
 * @package YoghBioLink\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template loader class.
 */
class YoghBL_Template_Loader {

	/**
	 * Store the biolinks page ID.
	 *
	 * @var integer
	 */
	private static $biolinks_page_id = 0;

	/**
	 * Hook in methods.
	 */
	public static function init() {
		self::$biolinks_page_id = yoghbl_get_page_id( 'biolinks' );

		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );
	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the theme's.
	 *
	 * Templates are in the 'templates' folder. YoghBioLinks looks for theme
	 * overrides in /theme/yoghbiolinks/ by default.
	 *
	 * For beginners, it also looks for a yoghbiolinks.php template first. If the user adds
	 * this to the theme (containing a yoghbiolinks() inside) this will be used for all
	 * YoghBioLinks templates.
	 *
	 * @param string $template Template to load.
	 * @return string
	 */
	public static function template_loader( $template ) {
		if ( is_embed() ) {
			return $template;
		}

		$default_file = self::get_template_loader_default_file();

		if ( $default_file ) {
			/**
			 * Filter hook to choose which files to find before YoghBioLinks does it's own logic.
			 *
			 * @var array
			 */
			$search_files = self::get_template_loader_files( $default_file );
			$template     = locate_template( $search_files );

			if ( ! $template || YOGHBL_TEMPLATE_DEBUG_MODE ) {
				$template = YoghBL()->plugin_path() . '/templates/' . $default_file;
			}
		}

		return $template;
	}

	/**
	 * Get the default filename for a template except if a block template with
	 * the same name exists.
	 *
	 * @return string
	 */
	private static function get_template_loader_default_file() {
		if ( is_yoghbiolinks() ) {
			$default_file = 'yoghbiolinks.php';
		} else {
			$default_file = '';
		}
		return $default_file;
	}

	/**
	 * Get an array of filenames to search for a given template.
	 *
	 * @param  string $default_file The default file name.
	 * @return string[]
	 */
	private static function get_template_loader_files( $default_file ) {
		$templates   = apply_filters( 'yoghbiolinks_template_loader_files', array(), $default_file );
		$templates[] = 'yoghbiolinks.php';

		$templates[] = $default_file;
		$templates[] = YoghBL()->template_path() . $default_file;

		return array_unique( $templates );
	}
}

add_action( 'init', array( 'YoghBL_Template_Loader', 'init' ) );
