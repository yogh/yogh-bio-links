<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
if( !function_exists('yoghbl_body_class') ){
	function yoghbl_body_class( $classes ) {
		$classes = (array) $classes;

		if ( yoghbl_is() ) {
			$classes[] = 'yoghbl';
			$classes[] = 'yoghbl-page';
		}

		return array_unique( $classes );
	}
}

if ( ! function_exists( 'yoghbl_logo' ) ) {

	/**
	 * The Yogh Bio Links logo.
	 */
	function yoghbl_logo() {
		$logo_id = intval(get_option( 'yoghbl_logo' ));

		$html = '<div class="yoghbl-logo yoghbl-empty"><!-- wp:image {"align":"center","width":96,"height":96,"sizeSlug":"thumbnail","linkDestination":"none","className":"is-style-rounded"} -->
		<figure class="wp-block-image aligncenter size-thumbnail is-resized is-style-rounded"></figure>
		<!-- /wp:image --></div>';

		if ( ! empty( $logo_id ) ) {
			$logo = wp_get_attachment_image_src( $logo_id );
			$html = '<div class="yoghbl-logo"><!-- wp:image {"align":"center","id":' . esc_attr( $logo_id ) . ',"width":96,"height":96,"sizeSlug":"thumbnail","linkDestination":"none","className":"is-style-rounded"} -->
			<figure class="wp-block-image aligncenter size-thumbnail is-resized is-style-rounded"><img src="' . esc_url( $logo[0] ) . '" alt="" class="wp-image-' . esc_attr( $logo_id ) . '" width="96" height="96"/></figure>
			<!-- /wp:image --></div>';
		}
		echo apply_filters(
			'yoghbl_logo_html',
			$html,
			$logo_id
		);
	}
}

if ( ! function_exists( 'yoghbl_title' ) ) {

	/**
	 * The Yogh Bio Links title.
	 */
	function yoghbl_title() {
		$title = wp_strip_all_tags(get_option( 'yoghbl_title' ));

		if ( empty( $title ) ) {
			$title = wp_strip_all_tags(get_bloginfo( 'name' ));
		}

		echo apply_filters(
			'yoghbl_title_html',
			'<!-- wp:heading {"textAlign":"center","level":1} -->
			<h1 class="has-text-align-center">' . esc_html( $title ) . '</h1>
			<!-- /wp:heading -->',
			$title
		);
	}
}

if ( ! function_exists( 'yoghbl_description' ) ) {

	/**
	 * The Yogh Bio Links description.
	 */
	function yoghbl_description() {
		$description = get_option( 'yoghbl_description' );

		if ( false === $description ) {
			$description = get_bloginfo( 'description' );
		}

		if ( empty( $description ) ) {
			$html = '<!-- wp:paragraph {"textAlign":"center","level":1,"className":"yoghbl-description yoghbl-empty"} -->
			<p class="has-text-align-center yoghbl-description yoghbl-empty"></p>
			<!-- /wp:paragraph -->';
		} else {
			$html = '<!-- wp:paragraph {"textAlign":"center","level":1,"className":"yoghbl-description"} -->
			<p class="has-text-align-center yoghbl-description">' . esc_html( $description ) . '</p>
			<!-- /wp:paragraph -->';
		}
		echo apply_filters(
			'yoghbl_description_html',
			$html,
			$description
		);
	}
}

if ( ! function_exists( 'yoghbl_buttons' ) ) {

	/**
	 * The HTML output to Yogh Bio Links buttons.
	 *
	 * @param array $args Arguments.
	 */
	function yoghbl_links_html( $args = array() ) {
		$args = (object) apply_filters( 'yoghbl_links_html_args', $args );

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

		$html = '<!-- wp:buttons {"className":"yoghbl-links wp-block-button aligncenter has-custom-width wp-block-button__width-100","layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons yoghbl-links wp-block-button aligncenter has-custom-width wp-block-button__width-100">' . implode( '', $links_html ) . '</div>
		<!-- /wp:buttons -->';

		$links_html = apply_filters(
			'yoghbl_links_html',
			$html,
			$links
		);

		$links_html = apply_filters( 'yoghbl_links_html_output', $links_html, $args );

		echo wp_kses_post($links_html);
	}
}

if ( ! function_exists( 'yoghbl_credits' ) ) {

	/**
	 * The Yogh Bio Links credits.
	 */
	function yoghbl_credits() {
		$hide_credits = (int) get_option( 'yoghbl_credits', 0 );

		$html = '<div class="yoghbl-credits yoghbl-empty"></div>';
		if ( 1 !== $hide_credits ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin = get_plugin_data( YOGHBL_PLUGIN_FILE );

			$html = sprintf(
				'<div class="yoghbl-credits"><span><a href="%s" target="_blank">%s</a></span></div>',
				sanitize_url($plugin['PluginURI']),
				/* translators: %s: Plugin Name */
				sprintf( __( 'Created with %s', 'yogh-bio-links' ), wp_strip_all_tags($plugin['Name']) )
			);
		}

		echo apply_filters(
			'yoghbl_credits',
			$html,
			sanitize_url($plugin['PluginURI']),
			wp_strip_all_tags($plugin['Name'])
		);
	}
}
