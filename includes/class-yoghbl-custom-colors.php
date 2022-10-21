<?php
/**
 * Custom Colors Class
 *
 * @version 1.0.0
 * @package YoghBioLinks
 */

/**
 * This class is in charge of color customization via the Customizer.
 */
class YoghBL_Custom_Colors {

	/**
	 * Instantiate the object.
	 */
	public function __construct() {
		// Enqueue color variables for customizer & frontend.
		add_action( 'wp_enqueue_scripts', array( $this, 'custom_color_variables' ), 11 );

		// Add body-class if needed.
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Determine the luminance of the given color and then return #fff or #000 so that the text is always readable.
	 *
	 * @param string $background_color The background color.
	 * @return string (hex color)
	 */
	public function custom_get_readable_color( $background_color ) {
		return 127 < self::get_relative_luminance_from_hex( $background_color ) ? 'var(--yoghbl--color-dark-gray)' : 'var(--yoghbl--color-light-gray)';
	}

	/**
	 * Generate color variables.
	 *
	 * Adjust the color value of the CSS variables depending on the background color theme mod.
	 * Both text and link colors needs to be updated.
	 * The code below needs to be updated, because the colors are no longer theme mods.
	 *
	 * @param string|null $context Can be "editor" or null.
	 * @return string
	 */
	public function generate_custom_color_variables( $context = null ) {
		$theme_css        = ':root{';
		$background_color = get_option( 'yoghbiolinks_background_color', '#28303D' );

		if ( '#28303d' !== strtolower( $background_color ) ) {
			$theme_css .= '--yoghbl--body-color: ' . $this->custom_get_readable_color( $background_color ) . ';';
			$theme_css .= '--yoghbl--body-bg: ' . $background_color . ';';
		}

		$theme_css .= '}';

		return $theme_css;
	}

	/**
	 * Customizer & frontend custom color variables.
	 *
	 * @return void
	 */
	public function custom_color_variables() {
		if ( '#28303d' !== strtolower( get_option( 'yoghbiolinks_background_color', '#28303D' ) ) ) {
			wp_add_inline_style( 'yoghbiolinks-general', $this->generate_custom_color_variables() );
		}
	}

	/**
	 * Get luminance from a HEX color.
	 *
	 * @static
	 *
	 * @param string $hex The HEX color.
	 * @return int Returns a number (0-255).
	 */
	public static function get_relative_luminance_from_hex( $hex ) {

		// Remove the "#" symbol from the beginning of the color.
		$hex = ltrim( $hex, '#' );

		// Make sure there are 6 digits for the below calculations.
		if ( 3 === strlen( $hex ) ) {
			$hex = substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 2, 1 ) . substr( $hex, 2, 1 );
		}

		// Get red, green, blue.
		$red   = hexdec( substr( $hex, 0, 2 ) );
		$green = hexdec( substr( $hex, 2, 2 ) );
		$blue  = hexdec( substr( $hex, 4, 2 ) );

		// Calculate the luminance.
		$lum = ( 0.2126 * $red ) + ( 0.7152 * $green ) + ( 0.0722 * $blue );
		return (int) round( $lum );
	}

	/**
	 * Adds a class to <body> if the background-color is dark.
	 *
	 * @param array $classes The existing body classes.
	 * @return array
	 */
	public function body_class( $classes ) {
		$background_color = get_option( 'yoghbiolinks_background_color', '#28303D' );
		$luminance        = self::get_relative_luminance_from_hex( $background_color );

		if ( 127 > $luminance ) {
			$classes[] = 'is-dark-theme';
		} else {
			$classes[] = 'is-light-theme';
		}

		if ( 225 <= $luminance ) {
			$classes[] = 'has-background-white';
		}

		return $classes;
	}
}

new YoghBL_Custom_Colors();
