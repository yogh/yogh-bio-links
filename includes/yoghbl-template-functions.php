<?php
/**
 * YoghBioLinks Template
 *
 * Functions for the templating system.
 *
 * @package  YoghBioLinks\Functions
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add body classes for YoghBioLinks pages.
 *
 * @param  array $classes Body Classes.
 * @return array
 */
function yoghbl_body_class( $classes ) {
	$classes = (array) $classes;

	if ( is_yoghbiolinks() ) {
		$classes[] = 'yoghbiolinks';
		$classes[] = 'yoghbiolinks-page';
	}

	return array_unique( $classes );
}

if ( ! function_exists( 'yoghbiolinks_logo' ) ) {

	/**
	 * The Yogh Bio Links logo.
	 */
	function yoghbiolinks_logo() {
		$logo_id = get_option( 'yoghbiolinks_logo' );

		$html = '<div class="yoghbiolinks-logo yoghbiolinks-empty"><!-- wp:image {"align":"center","width":96,"height":96,"sizeSlug":"thumbnail","linkDestination":"none","className":"is-style-rounded"} -->
		<figure class="wp-block-image aligncenter size-thumbnail is-resized is-style-rounded"></figure>
		<!-- /wp:image --></div>';

		if ( ! empty( $logo_id ) ) {
			$logo = wp_get_attachment_image_src( $logo_id );
			$html = '<div class="yoghbiolinks-logo"><!-- wp:image {"align":"center","id":' . esc_attr( $logo_id ) . ',"width":96,"height":96,"sizeSlug":"thumbnail","linkDestination":"none","className":"is-style-rounded"} -->
			<figure class="wp-block-image aligncenter size-thumbnail is-resized is-style-rounded"><img src="' . esc_url( $logo[0] ) . '" alt="" class="wp-image-' . esc_attr( $logo_id ) . '" width="96" height="96"/></figure>
			<!-- /wp:image --></div>';
		}
		echo apply_filters(
			'yoghbiolinks_logo_html',
			$html,
			$logo_id
		);
	}
}

if ( ! function_exists( 'yoghbiolinks_title' ) ) {

	/**
	 * The Yogh Bio Links title.
	 */
	function yoghbiolinks_title() {
		$title = get_option( 'yoghbiolinks_title' );

		if ( empty( $title ) ) {
			$title = get_bloginfo( 'name' );
		}

		echo apply_filters(
			'yoghbiolinks_title_html',
			'<!-- wp:heading {"textAlign":"center","level":1} -->
			<h1 class="has-text-align-center">' . esc_html( $title ) . '</h1>
			<!-- /wp:heading -->',
			$title
		);
	}
}

if ( ! function_exists( 'yoghbiolinks_description' ) ) {

	/**
	 * The Yogh Bio Links description.
	 */
	function yoghbiolinks_description() {
		$description = get_option( 'yoghbiolinks_description' );

		if ( false === $description ) {
			$description = get_bloginfo( 'description' );
		}

		if ( empty( $description ) ) {
			$html = '<!-- wp:paragraph {"textAlign":"center","level":1,"className":"yoghbiolinks-description yoghbiolinks-empty"} -->
			<p class="has-text-align-center yoghbiolinks-description yoghbiolinks-empty"></p>
			<!-- /wp:paragraph -->';
		} else {
			$html = '<!-- wp:paragraph {"textAlign":"center","level":1,"className":"yoghbiolinks-description"} -->
			<p class="has-text-align-center yoghbiolinks-description">' . esc_html( $description ) . '</p>
			<!-- /wp:paragraph -->';
		}
		echo apply_filters(
			'yoghbiolinks_description_html',
			$html,
			$description
		);
	}
}

if ( ! function_exists( 'yoghbiolinks_buttons' ) ) {

	/**
	 * The HTML output to Yogh Bio Links buttons.
	 *
	 * @param array $args Arguments.
	 */
	function yoghbiolinks_links_html( $args = array() ) {
		$args = (object) apply_filters( 'yoghbiolinks_links_html_args', $args );

		$links = yoghbl_links();

		$links_html = array();
		foreach ( $links as $link ) {
			if ( empty( $link->title ) && empty( $link->url ) ) {
				$link = '';
				continue;
			}
			$links_html[] = '<!-- wp:button {"width":100} -->
		<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $link->url ) . '" target="_blank" rel="noreferrer noopener">' . esc_html( $link->title ) . '</a></div>
		<!-- /wp:button -->';
		}

		$html = '<!-- wp:buttons {"className":"yoghbiolinks-links wp-block-button aligncenter has-custom-width wp-block-button__width-100","layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons yoghbiolinks-links wp-block-button aligncenter has-custom-width wp-block-button__width-100">' . implode( '', $links_html ) . '</div>
		<!-- /wp:buttons -->';

		$links_html = apply_filters(
			'yoghbiolinks_links_html',
			$html,
			$links
		);

		$links_html = apply_filters( 'yoghbiolinks_links_html_output', $links_html, $args );

		echo wp_kses_post($links_html);
	}
}

if ( ! function_exists( 'yoghbiolinks_credits' ) ) {

	/**
	 * The Yogh Bio Links credits.
	 */
	function yoghbiolinks_credits() {
		$hide_credits = (int) get_option( 'yoghbiolinks_credits', 0 );

		$html = '<div class="yoghbiolinks-credits yoghbiolinks-empty"></div>';
		if ( 1 !== $hide_credits ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin = get_plugin_data( YOGHBL_PLUGIN_FILE );

			$html = sprintf(
				'<div class="yoghbiolinks-credits"><span><a href="%s" target="_blank">%s</a></span></div>',
				$plugin['PluginURI'],
				/* translators: %s: Plugin Name */
				sprintf( __( 'Created with %s', 'yogh-bio-links' ), $plugin['Name'] )
			);
		}

		echo apply_filters(
			'yoghbiolinks_credits',
			$html,
			$plugin['PluginURI'],
			$plugin['Name']
		);
	}
}
