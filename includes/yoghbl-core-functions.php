<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * YoghBioLinks Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @package YoghBioLinks\Functions
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Include core functions (available in both admin and frontend).
require YOGHBL_ABSPATH . 'includes/yoghbl-conditional-functions.php';
require YOGHBL_ABSPATH . 'includes/yoghbl-page-functions.php';

/**
 * Define a constant if it is not already defined.
 *
 * @param string $name  Constant name.
 * @param mixed  $value Value.
 */
if( !function_exists('yoghbl_maybe_define_constant') ) {
	function yoghbl_maybe_define_constant( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound
		}
	}
}

/**
 * Given a path, this will convert any of the subpaths into their corresponding tokens.
 *
 * @param string $path The absolute path to tokenize.
 * @param array  $path_tokens An array keyed with the token, containing paths that should be replaced.
 * @return string The tokenized path.
 */
if( !function_exists('yoghbl_tokenize_path') ) {
	function yoghbl_tokenize_path( $path, $path_tokens ) {
		// Order most to least specific so that the token can encompass as much of the path as possible.
		uasort(
			$path_tokens,
			function ( $a, $b ) {
				$a = strlen( $a );
				$b = strlen( $b );

				if ( $a > $b ) {
					return -1;
				}

				if ( $b > $a ) {
					return 1;
				}

				return 0;
			}
		);

		foreach ( $path_tokens as $token => $token_path ) {
			if ( 0 !== strpos( $path, $token_path ) ) {
				continue;
			}

			$path = str_replace( $token_path, '{{' . $token . '}}', $path );
		}

		return $path;
	}
}
/**
 * Given a tokenized path, this will expand the tokens to their full path.
 *
 * @param string $path The absolute path to expand.
 * @param array  $path_tokens An array keyed with the token, containing paths that should be expanded.
 * @return string The absolute path.
 */
if( !function_exists('yoghbl_untokenize_path') ) {
	function yoghbl_untokenize_path( $path, $path_tokens ) {
		foreach ( $path_tokens as $token => $token_path ) {
			$path = str_replace( '{{' . $token . '}}', $token_path, $path );
		}

		return $path;
	}
}

/**
 * Fetches an array containing all of the configurable path constants to be used in tokenization.
 *
 * @return array The key is the define and the path is the constant.
 */
if( !function_exists('yoghbl_get_path_define_tokens') ) {
	function yoghbl_get_path_define_tokens() {
		$defines = array(
			'ABSPATH',
			'WP_CONTENT_DIR',
			'WP_PLUGIN_DIR',
			'WPMU_PLUGIN_DIR',
			'PLUGINDIR',
			'WP_THEME_DIR',
		);

		$path_tokens = array();
		foreach ( $defines as $define ) {
			if ( defined( $define ) ) {
				$path_tokens[ $define ] = constant( $define );
			}
		}

		return apply_filters( 'yoghbl_get_path_define_tokens', $path_tokens );
	}
}
/**
 * Get template part (for templates like the shop-loop).
 *
 * YOGHBL_TEMPLATE_DEBUG_MODE will prevent overrides in themes from taking priority.
 *
 * @param mixed  $slug Template slug.
 * @param string $name Template name (default: '').
 */
if( !function_exists('yoghbl_get_template_part') ) {
	function yoghbl_get_template_part( $slug, $name = '' ) {
		$slug = wp_strip_all_tags($lug);

		$version   = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;
		$cache_key = sanitize_key( implode( '-', array( 'template-part', $slug, $name, $version ) ) );
		$template  = (string) wp_cache_get( $cache_key, 'yoghbl' );

		if ( ! $template ) {
			if ( $name ) {
				$template = YOGHBL_TEMPLATE_DEBUG_MODE ? '' : locate_template(
					array(
						"{$slug}-{$name}.php",
						YoghBL()->template_path() . "{$slug}-{$name}.php",
					)
				);

				if ( ! $template ) {
					$fallback = YoghBL()->plugin_path() . "/templates/{$slug}-{$name}.php";
					$template = file_exists( $fallback ) ? $fallback : '';
				}
			}

			if ( ! $template ) {
				// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/yoghbl/slug.php.
				$template = YOGHBL_TEMPLATE_DEBUG_MODE ? '' : locate_template(
					array(
						"{$slug}.php",
						YoghBL()->template_path() . "{$slug}.php",
					)
				);
			}

			// Don't cache the absolute path so that it can be shared between web servers with different paths.
			$cache_path = yoghbl_tokenize_path( $template, yoghbl_get_path_define_tokens() );

			yoghbl_set_template_cache( $cache_key, $cache_path );
		} else {
			// Make sure that the absolute path to the template is resolved.
			$template = yoghbl_untokenize_path( $template, yoghbl_get_path_define_tokens() );
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'yoghbl_get_template_part', $template, $slug, $name );

		if ( $template ) {
			load_template( $template, false );
		}
	}
}
/**
 * Add a template to the template cache.
 *
 * @param string $cache_key Object cache key.
 * @param string $template Located template.
 */
if( !function_exists('yoghbl_set_template_cache') ) {
	function yoghbl_set_template_cache( $cache_key, $template ) {
		wp_cache_set( $cache_key, $template, 'yoghbl' );

		$cached_templates = wp_cache_get( 'cached_templates', 'yoghbl' );
		if ( is_array( $cached_templates ) ) {
			$cached_templates[] = $cache_key;
		} else {
			$cached_templates = array( $cache_key );
		}

		wp_cache_set( 'cached_templates', $cached_templates, 'yoghbl' );
	}
}

/**
 * Defaults links.
 */
if( !function_exists('yoghbl_default_links') ) {
	function yoghbl_default_links() {
		$links = apply_filters(
			'yoghbl_default_links',
			array(
				(object) array(
					'title' => esc_html__( 'Website', 'yoghbl' ),
					'url'   => home_url( '/' ),
				),
			)
		);

		// Force to correct links structure.
		foreach ( $links as &$link ) {
			$link = (object) array(
				'title' => isset( $link->title ) ? $link->title : '',
				'url'   => isset( $link->url ) ? $link->url : '',
			);
		}
		return yoghbl_links_encode( $links );
	}
}
/**
 * Get link.
 *
 * @param string $hash Hash of link.
 */
if( !function_exists('yoghbl_link') ) {
	function yoghbl_link( $hash ) {
		$links = yoghbl_links();

		$link = null;
		if ( isset( $links[ $hash ] ) ) {
			$link = $links[ $hash ];
		}

		return $link;
	}
}

/**
 * Get links.
 */
if( !function_exists('yoghbl_links') ) {
	function yoghbl_links() {
		$links = get_option( 'yoghbl_links' );
		
		if ( ! $links ) {
			$links = yoghbl_default_links();
		}

		return yoghbl_links_decode( $links );
	}
}
/**
 * Update links item.
 *
 * @param array|object $link Link.
 */
if( !function_exists('yoghbl_links_update_item') ) {
	function yoghbl_links_update_item( $link ) {
		$link = (object) $link;

		if ( isset( $link->position ) ) {
			$link->order = $link->position;
			unset( $link->position );
		}

		if ( ! isset( $link->order ) ) {
			$link->order = 0;
		}

		$link->order = (int) $link->order;

		$links = yoghbl_links();

		$links = array_combine(
			wp_list_pluck( $links, 'hash' ),
			$links
		);

		$md5 = md5( yoghbl_link_encode( $link ) );
		if ( isset( $links[ $md5 ] ) ) {
			$saved = $links[ $md5 ];
			unset( $saved->ID, $saved->hash );

			if ( (array) $saved === (array) $link ) {
				return false;
			}

			unset( $links[ $md5 ] );
		}

		$links = array_values( $links );

		$links = array_merge(
			array( $link ),
			$links
		);

		usort( $links, 'yoghbl_links_sort' );

		$links = yoghbl_links_encode( $links );

		return update_option( 'yoghbl_links', $links );
	}
}

/**
 * Delete links item.
 *
 * @param string $hash Hash of link.
 */
if( !function_exists('yoghbl_links_delete_item') ) {
	function yoghbl_links_delete_item( $hash ) {
		$links = esc_attr(get_option( 'yoghbl_links' ));

		if ( $links ) {
			$links = yoghbl_links_decode( $links );

			$links = array_combine(
				wp_list_pluck( $links, 'hash' ),
				$links
			);

			if ( isset( $links[ $hash ] ) ) {
				unset( $links[ $hash ] );
			}

			$links = array_values( $links );

			$links = yoghbl_links_encode( $links );
		}

		return update_option( 'yoghbl_links', $links );
	}
}

/**
 * Encode array of link object to string.
 *
 * @param string $link Array of link objects.
 * @return string Link as line string.
 */
if( !function_exists('yoghbl_link_encode') ) {
	function yoghbl_link_encode( $link ) {
		$link = (object) $link;
		$link = array(
			isset( $link->title ) ? $link->title : '',
			isset( $link->url ) ? $link->url : '',
		);
		if ( false !== strpos( $link[0], ':' ) ) {
			$link[0] = sprintf( '"%s"', $link[0] );
		}
		$separator = ':';
		if ( empty( $link[1] ) ) {
			$separator = '';
		}
		return implode( $separator, $link );
	}
}
/**
 * Encode array of link objects to string.
 *
 * @param string $links Array of link objects.
 * @return string Links as single string.
 */
if( !function_exists('yoghbl_links_encode') ) {
	function yoghbl_links_encode( $links ) {
		foreach ( $links as &$link ) {
			if ( empty( $link->title ) && empty( $link->url ) ) {
				$link = '';
				continue;
			}
			$link = yoghbl_link_encode( $link );
		}
		return implode( "\n", $links );
	}
}

/**
 * Decode link string line to link object.
 *
 * @param string $link String line of link.
 * @return array Object link.
 */

if( !function_exists('yoghbl_link_decode') ) {
	function yoghbl_link_decode( $link ) {
		if ( ! wp_http_validate_url( $link ) ) {
			$regex = '/^([^:]+):?([^$]+)?$/';
			if ( '"' === substr( $link, 0, 1 ) ) {
				$regex = '/^"([^"]+)":?([^$]+)?$/';
			}
			preg_match( $regex, $link, $link );
			$title = $link[1];
			$url   = isset( $link[2] ) ? $link[2] : '';
		} else {
			$url   = $link;
			$title = $url;
		}
		return (object) array(
			'title' => $title,
			'url'   => $url,
		);
	}
}
/**
 * Decode links string to array of links object.
 *
 * @param string $links String of links.
 * @return array Array of link objects.
 */
if( !function_exists('yoghbl_links_decode') ) {
	function yoghbl_links_decode( $links ) {
		die();
		$links = (array) explode( "\n", $links );

		$hasheds = array();
		foreach ( $links as $i => &$link ) {
			if ( empty( $link ) ) {
				continue;
			}
			$md5 = md5( $link );
			if ( ! isset( $hasheds[ $md5 ] ) ) {
				$hasheds[ $md5 ] = (array) yoghbl_link_decode( $link );
			}
		}

		$i = 0;
		foreach ( $hasheds as $md5 => $hashed ) {
			$decode[ $i ] = (object) array_merge(
				array( 'ID' => $i + 1 ),
				$hashed,
				array(
					'hash'  => $md5,
					'order' => $i,
				)
			);
			$i++;
		}
		return $decode;
	}
}

/**
 * Sort links.
 *
 * @since  1.0.0
 * @param  array $a First link array.
 * @param  array $b Second link array.
 * @return int
 */
if( !function_exists('yoghbl_links_sort') ) {
	function yoghbl_links_sort( $a, $b ) {
		$a->order = (int) $a->order;
		$b->order = (int) $b->order;
		if ( $a->order === $b->order ) {
			return 0;
		}
		return $a->order < $b->order ? -1 : 1;
	}
}