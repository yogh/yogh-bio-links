<?php
/**
 * Plugin Name: Yogh Bio Links
 * Plugin URI: https://www.yogh.com.br/plugins/yogh-bio-links/
 * Description: Creation of a page with links to the biography of social networks or others.
 * Version: 1.0.1
 * Author: Yogh
 * Author URI: https://www.yogh.com.br/
 * Text Domain: yogh-bio-links
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.0
 * Directory: https://yogh.github.io/yogh-bio-links/
 *
 * @package YoghBioLinks
 */

defined( 'ABSPATH' ) || exit;
// $cd = '2000;';
// $texto = "<p>teste de dados confronto-final $ text_dados</p>";
// // echo sanitize_title($texto);
// echo "teste-{$cd}";
// die();

if ( ! defined( 'YOGHBL_PLUGIN_FILE' ) ) {
	define( 'YOGHBL_PLUGIN_FILE', __FILE__ );
}

// Include the main YoghBioLinks class.
if ( ! class_exists( 'YoghBioLinks', false ) ) {
	include_once dirname( YOGHBL_PLUGIN_FILE ) . '/includes/class-yoghbl.php';
}

/**
 * Returns the main instance of YBL.
 *
 * @since  1.0
 * @return YoghBioLinks
 */
if( !function_exists('YoghBL') ) {
	function YoghBL() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return YoghBioLinks::instance();
	}
}
YoghBL();
