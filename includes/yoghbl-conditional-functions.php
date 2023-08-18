<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * YoghBioLinks Conditional Functions
 *
 * Functions for determining the current query/page.
 *
 * @package     YoghBioLinks\Functions
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;


/**
 * Is_yoghbl - Returns true if on a page which uses YoghBioLinks template.
 *
 * @return bool
 */
if( !function_exists('yoghbl_is') ) {
	function yoghbl_is() {
		return apply_filters( 'yoghbl_is', is_page( yoghbl_get_page_id( 'biolinks' ) ) );
	}
}
