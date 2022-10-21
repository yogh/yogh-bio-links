<?php
/**
 * YoghBioLinks Conditional Functions
 *
 * Functions for determining the current query/page.
 *
 * @package     YoghBioLinks\Functions
 * @version     1.0.0
 */

use Automattic\Jetpack\Constants;

defined( 'ABSPATH' ) || exit;


/**
 * Is_yoghbiolinks - Returns true if on a page which uses YoghBioLinks template.
 *
 * @return bool
 */
function is_yoghbiolinks() {
	return apply_filters( 'is_yoghbiolinks', is_page( yoghbl_get_page_id( 'biolinks' ) ) );
}
