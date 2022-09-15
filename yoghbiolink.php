<?php
/**
 * Plugin Name: Yogh Bio Link Generator
 * Description: Creation of a page with links to the biography of social networks or others.
 * Version: 1.0.0
 * Author: Yogh
 * Author URI: https://www.yogh.com.br/
 * Text Domain: yoghbiolink
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.2
 *
 * @package YoghBioLink
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'YBL_PLUGIN_FILE' ) ) {
	define( 'YBL_PLUGIN_FILE', __FILE__ );
}

// Include the main YoghBioLink class.
if ( ! class_exists( 'YoghBioLink', false ) ) {
	include_once dirname( YBL_PLUGIN_FILE ) . '/includes/class-yoghbiolink.php';
}

/**
 * Returns the main instance of YBL.
 *
 * @since  1.0
 * @return YoghBioLink
 */
function YBL() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YoghBioLink::instance();
}

YBL();
