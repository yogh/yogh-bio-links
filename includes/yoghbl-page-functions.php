<?php
/**
 * YoghBioLinks Page Functions
 *
 * Functions related to pages and menus.
 *
 * @package  YoghBioLinks\Functions
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve page ids - used for biolinks, terms. returns -1 if no page is found.
 *
 * @param string $page Page slug.
 * @return int
 */
function yoghbl_get_page_id( $page ) {
	$page = apply_filters( 'yoghbl_get_' . $page . '_page_id', get_option( 'yoghbl_' . $page . '_page_id' ) );

	return $page ? absint( $page ) : -1;
}

/**
 * Retrieve page permalink.
 *
 * @param string      $page page slug.
 * @param string|bool $fallback Fallback URL if page is not set. Defaults to home URL. @since 3.4.0.
 * @return string
 */
function yoghbl_get_page_permalink( $page, $fallback = null ) {
	$page_id   = yoghbl_get_page_id( $page );
	$permalink = 0 < $page_id ? get_permalink( $page_id ) : '';

	if ( ! $permalink ) {
		$permalink = is_null( $fallback ) ? get_home_url() : $fallback;
	}

	return apply_filters( 'yoghbl_get_' . $page . '_page_permalink', $permalink );
}

function yoghbl_edit_link() {
	return add_query_arg(
		array(
			'return' => urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ),
			array( 'autofocus' => array( 'panel' => 'yoghbiolinks' ) ),
		),
		admin_url( 'customize.php' )
	);
}

function yoghbl_get_edit_post_link_filter( $link, $post_id ) {
	if ( yoghbl_get_page_id( 'biolinks' ) === $post_id ) {
		$link = yoghbl_edit_link();
	}

	return $link;
}
add_filter( 'get_edit_post_link', 'yoghbl_get_edit_post_link_filter', 10, 2 );
