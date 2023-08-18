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
if( !function_exists('yoghbl_get_page_id') ) {
	function yoghbl_get_page_id( $page ) {
		$page = apply_filters( 'yoghbl_get_' . $page . '_page_id', get_option( 'yoghbl_' . $page . '_page_id' ) );

		return $page ? absint( $page ) : -1;
	}
}
/**
 * Retrieve page permalink.
 *
 * @param string      $page page slug.
 * @param string|bool $fallback Fallback URL if page is not set. Defaults to home URL. @since 3.4.0.
 * @return string
 */
if( !function_exists('yoghbl_get_page_permalink') ) {
	function yoghbl_get_page_permalink( $page, $fallback = null ) {
		$page_id   = yoghbl_get_page_id( $page );
		$permalink = 0 < $page_id ? get_permalink( $page_id ) : '';

		if ( ! $permalink ) {
			$permalink = is_null( $fallback ) ? get_home_url() : $fallback;
		}

		return apply_filters( 'yoghbl_get_' . $page . '_page_permalink', $permalink );
	}
}
/**
 * Retrieve edit link.
 *
 * @return string
 */
if( !function_exists('yoghbl_edit_link') ) {
	function yoghbl_edit_link() {
		$request_uri = '';
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}
		$link_edited = add_query_arg(
			array(
				'return' => rawurlencode( remove_query_arg( wp_removable_query_args(), $request_uri ) ),
				array( 'autofocus' => array( 'panel' => 'yoghbl' ) ),
			),
			admin_url( 'customize.php' )
		);

		return esc_url($link_edited);
	}
}
/**
 * Filter to edit link.
 *
 * @param string $link Edit post link.
 * @param int    $post_id Post ID.
 */
if( !function_exists('yoghbl_get_edit_post_link_filter') ) {
	function yoghbl_get_edit_post_link_filter( $link, $post_id ) {
		if ( yoghbl_get_page_id( 'biolinks' ) === $post_id ) {
			$link = yoghbl_edit_link();
		}

		return $link;
	}
	add_filter( 'get_edit_post_link', 'yoghbl_get_edit_post_link_filter', 10, 2 );
}