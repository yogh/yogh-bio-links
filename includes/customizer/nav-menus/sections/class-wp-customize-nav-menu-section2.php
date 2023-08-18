<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customize API: yoghbl_customize_Nav_Menu_Section class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize Menu Section Class
 *
 * Custom section only needed in JS.
 *
 * @since 4.3.0
 *
 * @see yoghbl_customize_Section
 */
class yoghbl_customize_Nav_Menu_Section2 extends yoghbl_customize_Section {

	/**
	 * Control type.
	 *
	 * @since 4.3.0
	 * @var string
	 */
	public $type = 'nav_menu2';

	/**
	 * Get section parameters for JS.
	 *
	 * @since 4.3.0
	 * @return array Exported parameters.
	 */
	public function json() {
		$exported            = parent::json();
		$exported['menu_id'] = (int) preg_replace( '/^nav_menu2\[(-?\d+)\]/', '$1', $this->id );

		return $exported;
	}
}
