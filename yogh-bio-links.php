<?php
/**
 * Plugin Name: Yogh Bio Links
 * Plugin URI: https://www.yogh.com.br/plugins/yogh-bio-links/
 * Description: Creation of a page with links to the biography of social networks or others.
 * Version: 1.0.0-beta1
 * Author: Yogh
 * Author URI: https://www.yogh.com.br/
 * Text Domain: yogh-bio-links
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.2
 * Directory: https://yogh.github.io/yogh-bio-links/
 *
 * @package YoghBioLinks
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'YOGHBL_PLUGIN_FILE' ) ) {
	define( 'YOGHBL_PLUGIN_FILE', __FILE__ );
}

// Include the main YoghBioLinks class.
if ( ! class_exists( 'YoghBioLinks', false ) ) {
	include_once dirname( YOGHBL_PLUGIN_FILE ) . '/includes/class-yoghbiolinks.php';
}

/**
 * Returns the main instance of YBL.
 *
 * @since  1.0
 * @return YoghBioLinks
 */
function YoghBL() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YoghBioLinks::instance();
}

YoghBL();
