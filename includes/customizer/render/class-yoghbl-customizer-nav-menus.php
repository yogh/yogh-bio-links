<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


require_once YOGHBL_ABSPATH . 'includes/customizer/render/controls/class-yoghbl-customizer-control-nav-menu.php';
require_once YOGHBL_ABSPATH . 'includes/customizer/render/sections/class-yoghbl-customizer-section-nav-menu.php';
require_once YOGHBL_ABSPATH . 'includes/customizer/render/settings/class-yoghbl-customizer-setting-nav-menu.php';
require_once YOGHBL_ABSPATH . 'includes/customizer/render/settings/class-yoghbl-customizer-setting-nav-menu-item.php';
require_once YOGHBL_ABSPATH . 'includes/customizer/render/controls/class-yoghbl-customizer-control-nav-menu-item.php';

final class YoghBL_Customizer_Nav_Menus {

	public $manager;

	public function __construct( $manager ) {
		$this->manager = $manager;

		add_action( 'customize_register', array( $this, 'customize_register' ), 11 );
		add_filter( 'customize_dynamic_setting_args', array( $this, 'filter_dynamic_setting_args' ), 10, 2 );
		add_filter( 'customize_dynamic_setting_class', array( $this, 'filter_dynamic_setting_class' ), 10, 3 );

		add_filter( 'customize_refresh_nonces', array( $this, 'filter_nonces' ) );
		add_action( 'wp_ajax_load-available-menu-items-customizer', array( $this, 'ajax_load_available_items' ) );
		add_action( 'wp_ajax_search-available-menu-items-customizer', array( $this, 'ajax_search_available_items' ) );
		add_action( 'wp_ajax_customize-nav-menus-insert-auto-draft', array( $this, 'ajax_insert_auto_draft_post' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_templates' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'available_items_template' ) );
		add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );

		// Selective Refresh partials.
		add_filter( 'customize_dynamic_partial_args', array( $this, 'customize_dynamic_partial_args' ), 10, 2 );
	}

	/**
	 * Adds a nonce for customizing menus.
	 *
	 * @since 4.5.0
	 *
	 * @param string[] $nonces Array of nonces.
	 * @return string[] Modified array of nonces.
	 */
	public function filter_nonces( $nonces ) {
		$nonces['customize-menus'] = wp_create_nonce( 'customize-menus' );
		return $nonces;
	}

	/**
	 * Ajax handler for loading available menu items.
	 *
	 * @since 4.3.0
	 */
	public function ajax_load_available_items() {
		check_ajax_referer( 'customize-menus', 'customize-menus-nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( -1 );
		}

		$all_items  = array();
		$item_types = array();
		if ( isset( $_POST['item_types'] ) && is_array( $_POST['item_types'] ) ) {
			$item_types = sanitize_text_field(  wp_unslash( $_POST['item_types'] ) ) ;
		} elseif ( isset( $_POST['type'] ) && isset( $_POST['object'] ) ) { // Back compat.
			$item_types[] = array(
				'type'   => sanitize_text_field( wp_unslash( $_POST['type'] ) ),
				'object' => sanitize_text_field( wp_unslash( $_POST['object'] ) ),
				'page'   => empty( $_POST['page'] ) ? 0 : absint( $_POST['page'] ),
			);
		} else {
			wp_send_json_error( 'nav_menus_missing_type_or_object_parameter' );
		}

		foreach ( $item_types as $item_type ) {
			if ( empty( $item_type['type'] ) || empty( $item_type['object'] ) ) {
				wp_send_json_error( 'nav_menus_missing_type_or_object_parameter' );
			}
			$type   = sanitize_key( $item_type['type'] );
			$object = sanitize_key( $item_type['object'] );
			$page   = empty( $item_type['page'] ) ? 0 : absint( $item_type['page'] );
			$items  = $this->load_available_items_query( $type, $object, $page );
			if ( is_wp_error( $items ) ) {
				wp_send_json_error( $items->get_error_code() );
			}
			$all_items[ $item_type['type'] . ':' . $item_type['object'] ] = $items;
		}

		wp_send_json_success( array( 'items' => $all_items ) );
	}

	/**
	 * Performs the post_type and taxonomy queries for loading available menu items.
	 *
	 * @since 4.3.0
	 *
	 * @param string $object_type Optional. Accepts any custom object type and has built-in support for
	 *                            'post_type' and 'taxonomy'. Default is 'post_type'.
	 * @param string $object_name Optional. Accepts any registered taxonomy or post type name. Default is 'page'.
	 * @param int    $page        Optional. The page number used to generate the query offset. Default is '0'.
	 * @return array|WP_Error An array of menu items on success, a WP_Error object on failure.
	 */
	public function load_available_items_query( $object_type = 'post_type', $object_name = 'page', $page = 0 ) {
		$items = array();

		if ( 'post_type' === $object_type ) {
			$post_type = get_post_type_object( $object_name );
			if ( ! $post_type ) {
				return new WP_Error( 'nav_menus_invalid_post_type' );
			}

			/*
			 * If we're dealing with pages, let's prioritize the Front Page,
			 * Posts Page and Privacy Policy Page at the top of the list.
			 */
			$important_pages   = array();
			$suppress_page_ids = array();
			if ( 0 === $page && 'page' === $object_name ) {
				// Insert Front Page or custom "Home" link.
				$front_page = 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : 0;
				if ( ! empty( $front_page ) ) {
					$front_page_obj      = get_post( $front_page );
					$important_pages[]   = $front_page_obj;
					$suppress_page_ids[] = $front_page_obj->ID;
				} else {
					// Add "Home" link. Treat as a page, but switch to custom on add.
					$items[] = array(
						'id'         => 'home',
						'title'      => _x( 'Home', 'nav menu home label' ),
						'type'       => 'custom',
						'type_label' => __( 'Custom Link' ),
						'object'     => '',
						'url'        => home_url(),
					);
				}

				// Insert Posts Page.
				$posts_page = 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_for_posts' ) : 0;
				if ( ! empty( $posts_page ) ) {
					$posts_page_obj      = get_post( $posts_page );
					$important_pages[]   = $posts_page_obj;
					$suppress_page_ids[] = $posts_page_obj->ID;
				}

				// Insert Privacy Policy Page.
				$privacy_policy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
				if ( ! empty( $privacy_policy_page_id ) ) {
					$privacy_policy_page = get_post( $privacy_policy_page_id );
					if ( $privacy_policy_page instanceof WP_Post && 'publish' === $privacy_policy_page->post_status ) {
						$important_pages[]   = $privacy_policy_page;
						$suppress_page_ids[] = $privacy_policy_page->ID;
					}
				}
			} elseif ( 'post' !== $object_name && 0 === $page && $post_type->has_archive ) {
				// Add a post type archive link.
				$items[] = array(
					'id'         => sanitize_title($object_name) . '-archive',
					'title'      => wp_strip_all_tags($post_type->labels->archives),
					'type'       => 'post_type_archive',
					'type_label' => __( 'Post Type Archive' ),
					'object'     => wp_strip_all_tags($object_name),
					'url'        => get_post_type_archive_link( $object_name ),
				);
			}

			// Prepend posts with yoghbl_nav_menus_created_posts on first page.
			$posts = array();
			if ( 0 === $page && $this->manager->get_setting( 'yoghbl_nav_menus_created_posts' ) ) {
				foreach ( $this->manager->get_setting( 'yoghbl_nav_menus_created_posts' )->value() as $post_id ) {
					$auto_draft_post = get_post( $post_id );
					if ( $post_type->name === $auto_draft_post->post_type ) {
						$posts[] = $auto_draft_post;
					}
				}
			}

			$args = array(
				'numberposts' => 10,
				'offset'      => 10 * $page,
				'orderby'     => 'date',
				'order'       => 'DESC',
				'post_type'   => $object_name,
			);

			// Add suppression array to arguments for get_posts.
			if ( ! empty( $suppress_page_ids ) ) {
				$args['post__not_in'] = $suppress_page_ids;
			}

			$posts = array_merge(
				$posts,
				$important_pages,
				get_posts( $args )
			);

			foreach ( $posts as $post ) {
				$post_title = $post->post_title;
				if ( '' === $post_title ) {
					/* translators: %d: ID of a post. */
					$post_title = sprintf( __( '#%d (no title)', 'yogh-bio-links' ), $post->ID );
				}

				$post_type_label = get_post_type_object( $post->post_type )->labels->singular_name;
				$post_states     = get_post_states( $post );
				if ( ! empty( $post_states ) ) {
					$post_type_label = implode( ',', $post_states );
				}

				$items[] = array(
					'id'         => "post-{$post->ID}",
					'title'      => html_entity_decode( $post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
					'type'       => 'post_type',
					'type_label' => wp_strip_all_tags($post_type_label),
					'object'     => wp_strip_all_tags($post->post_type),
					'object_id'  => (int) $post->ID,
					'url'        => get_permalink( (int) $post->ID ),
				);
			}
		} elseif ( 'taxonomy' === $object_type ) {
			$terms = get_terms(
				array(
					'taxonomy'     => $object_name,
					'child_of'     => 0,
					'exclude'      => '',
					'hide_empty'   => false,
					'hierarchical' => 1,
					'include'      => '',
					'number'       => 10,
					'offset'       => 10 * $page,
					'order'        => 'DESC',
					'orderby'      => 'count',
					'pad_counts'   => false,
				)
			);

			if ( is_wp_error( $terms ) ) {
				return $terms;
			}

			foreach ( $terms as $term ) {
				$items[] = array(
					'id'         => "term-{$term->term_id}",
					'title'      => html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
					'type'       => 'taxonomy',
					'type_label' => get_taxonomy( $term->taxonomy )->labels->singular_name,
					'object'     => wp_strip_all_tags($term->taxonomy),
					'object_id'  => (int) $term->term_id,
					'url'        => get_term_link( (int) $term->term_id, $term->taxonomy ),
				);
			}
		}

		/**
		 * Filters the available menu items.
		 *
		 * @since 4.3.0
		 *
		 * @param array  $items       The array of menu items.
		 * @param string $object_type The object type.
		 * @param string $object_name The object name.
		 * @param int    $page        The current page number.
		 */
		$items = apply_filters( 'customize_nav_menu_available_items', $items, $object_type, $object_name, $page );

		return $items;
	}

	/**
	 * Ajax handler for searching available menu items.
	 *
	 * @since 4.3.0
	 */
	public function ajax_search_available_items() {
		check_ajax_referer( 'customize-menus', 'customize-menus-nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( -1 );
		}

		if ( empty( $_POST['search'] ) ) {
			wp_send_json_error( 'nav_menus_missing_search_parameter' );
		}

		$p = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 0;
		if ( $p < 1 ) {
			$p = 1;
		}

		$s     = sanitize_text_field( wp_unslash( $_POST['search'] ) );
		$items = $this->search_available_items_query(
			array(
				'pagenum' => $p,
				's'       => $s,
			)
		);

		if ( empty( $items ) ) {
			wp_send_json_error( array( 'message' => __( 'No results found.', 'yogh-bio-links' ) ) );
		} else {
			wp_send_json_success( array( 'items' => $items ) );
		}
	}

	/**
	 * Performs post queries for available-item searching.
	 *
	 * Based on WP_Editor::wp_link_query().
	 *
	 * @since 4.3.0
	 *
	 * @param array $args Optional. Accepts 'pagenum' and 's' (search) arguments.
	 * @return array Menu items.
	 */
	public function search_available_items_query( $args = array() ) {
		$items = array();

		$post_type_objects = get_post_types( array( 'show_in_nav_menus' => true ), 'objects' );
		$query             = array(
			'post_type'              => array_keys( $post_type_objects ),
			'suppress_filters'       => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'post_status'            => 'publish',
			'posts_per_page'         => 20,
		);

		$args['pagenum'] = isset( $args['pagenum'] ) ? absint( $args['pagenum'] ) : 1;
		$query['offset'] = $args['pagenum'] > 1 ? $query['posts_per_page'] * ( $args['pagenum'] - 1 ) : 0;

		if ( isset( $args['s'] ) ) {
			$query['s'] = $args['s'];
		}

		$posts = array();

		// Prepend list of posts with yoghbl_nav_menus_created_posts search results on first page.
		$yoghbl_nav_menus_created_posts_setting = $this->manager->get_setting( 'yoghbl_nav_menus_created_posts' );
		if ( 1 === $args['pagenum'] && $yoghbl_nav_menus_created_posts_setting && count( $yoghbl_nav_menus_created_posts_setting->value() ) > 0 ) {
			$stub_post_query = new WP_Query(
				array_merge(
					$query,
					array(
						'post_status'    => 'auto-draft',
						'post__in'       => $yoghbl_nav_menus_created_posts_setting->value(),
						'posts_per_page' => -1,
					)
				)
			);
			$posts           = array_merge( $posts, $stub_post_query->posts );
		}

		// Query posts.
		$get_posts = new WP_Query( $query );
		$posts     = array_merge( $posts, $get_posts->posts );

		// Create items for posts.
		foreach ( $posts as $post ) {
			$post_title = wp_strip_all_tags($post->post_title);
			if ( '' === $post_title ) {
				/* translators: %d: ID of a post. */
				$post_title = sprintf( __( '#%d (no title)', 'yogh-bio-links' ), $post->ID );
			}

			$post_type_label = $post_type_objects[ $post->post_type ]->labels->singular_name;
			$post_states     = get_post_states( $post );
			if ( ! empty( $post_states ) ) {
				$post_type_label = implode( ',', $post_states );
			}

			$items[] = array(
				'id'         => "post-{$post->ID}",
				'title'      => html_entity_decode( $post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
				'type'       => 'post_type',
				'type_label' => $post_type_label,
				'object'     => $post->post_type,
				'object_id'  => (int) $post->ID,
				'url'        => get_permalink( (int) $post->ID ),
			);
		}

		// Query taxonomy terms.
		$taxonomies = get_taxonomies( array( 'show_in_nav_menus' => true ), 'names' );
		$terms      = get_terms(
			array(
				'taxonomies' => $taxonomies,
				'name__like' => $args['s'],
				'number'     => 20,
				'hide_empty' => false,
				'offset'     => 20 * ( $args['pagenum'] - 1 ),
			)
		);

		// Check if any taxonomies were found.
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$items[] = array(
					'id'         => "term-{$term->term_id}",
					'title'      => html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
					'type'       => 'taxonomy',
					'type_label' => get_taxonomy( $term->taxonomy )->labels->singular_name,
					'object'     => $term->taxonomy,
					'object_id'  => (int) $term->term_id,
					'url'        => get_term_link( (int) $term->term_id, $term->taxonomy ),
				);
			}
		}

		// Add "Home" link if search term matches. Treat as a page, but switch to custom on add.
		if ( isset( $args['s'] ) ) {
			// Only insert custom "Home" link if there's no Front Page
			$front_page = 'page' === wp_strip_all_tags(get_option( 'show_on_front' )) ? (int) wp_strip_all_tags(get_option( 'page_on_front' )) : 0;
			if ( empty( $front_page ) ) {
				$title   = _x( 'Home', 'nav menu home label' );
				$matches = function_exists( 'mb_stripos' ) ? false !== mb_stripos( $title, $args['s'] ) : false !== stripos( $title, $args['s'] );
				if ( $matches ) {
					$items[] = array(
						'id'         => 'home',
						'title'      => wp_strip_all_tags($title),
						'type'       => 'custom',
						'type_label' => __( 'Custom Link', 'yogh-bio-links'  ),
						'object'     => '',
						'url'        => home_url(),
					);
				}
			}
		}

		/**
		 * Filters the available menu items during a search request.
		 *
		 * @since 4.5.0
		 *
		 * @param array $items The array of menu items.
		 * @param array $args  Includes 'pagenum' and 's' (search) arguments.
		 */
		$items = apply_filters( 'customize_nav_menu_searched_items', $items, $args );

		return $items;
	}

	public function enqueue_scripts() {
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;

		wp_enqueue_style( 'customize-nav-menus' );

		wp_enqueue_script(
			'customize-render-nav-menus',
			YoghBL()->plugin_url() . '/assets/js/customize-render-nav-menus.js',
			array( 'jquery', 'wp-backbone', 'customize-controls', 'accordion', 'wp-sanitize' ),
			$version,
			true
		);

		$temp_nav_menu_item_setting = new YoghBL_Customizer_Setting_Nav_Menu_Item( $this->manager, 'yoghbl_nav_menu_item[-1]' );

		$settings = array(
			'l10n'                     => array(
				'itemAdded'   => __( 'Menu item added', 'yogh-bio-links'  ),
				'itemDeleted' => __( 'Menu item deleted', 'yogh-bio-links'  ),
				'movedUp'     => __( 'Menu item moved up', 'yogh-bio-links'  ),
				'movedDown'   => __( 'Menu item moved down', 'yogh-bio-links'  ),
			),
			'settingTransport'         => 'postMessage',
			'phpIntMax'                => PHP_INT_MAX,
			'defaultSettingValues'     => array(
				'yoghbl_nav_menu_item' => $temp_nav_menu_item_setting->default,
			),
		);

		$data = sprintf( 'var _yoghblCustomizerSettingsNavMenus = %s;', wp_json_encode( $settings ) );
		wp_scripts()->add_data( 'customize-render-nav-menus', 'data', $data );

		wp_enqueue_style(
			'yoghbl-customize-nav-menus',
			YoghBL()->plugin_url() . '/assets/css/customize-nav-menus.css',
			array( 'wp-admin', 'colors' ),
			$version
		);
	}

	/**
	 * Filters a dynamic setting's constructor args.
	 *
	 * For a dynamic setting to be registered, this filter must be employed
	 * to override the default false value with an array of args to pass to
	 * the yoghbl_customize_Setting constructor.
	 *
	 * @since 4.3.0
	 *
	 * @param false|array $setting_args The arguments to the yoghbl_customize_Setting constructor.
	 * @param string      $setting_id   ID for dynamic setting, usually coming from `$_POST['customized']`.
	 * @return array|false
	 */
	public function filter_dynamic_setting_args( $setting_args, $setting_id ) {
		if ( preg_match( YoghBL_Customizer_Setting_Nav_Menu::ID_PATTERN, $setting_id ) ) {
			$setting_args = array(
				'type'      => YoghBL_Customizer_Setting_Nav_Menu::TYPE,
				'transport' => 'postMessage',
			);
		} elseif ( preg_match( YoghBL_Customizer_Setting_Nav_Menu_Item::ID_PATTERN, $setting_id ) ) {
			$setting_args = array(
				'type'      => YoghBL_Customizer_Setting_Nav_Menu_Item::TYPE,
				'transport' => 'postMessage',
			);
		}
		return $setting_args;
	}

	/**
	 * Allows non-statically created settings to be constructed with custom yoghbl_customize_Setting subclass.
	 *
	 * @since 4.3.0
	 *
	 * @param string $setting_class yoghbl_customize_Setting or a subclass.
	 * @param string $setting_id    ID for dynamic setting, usually coming from `$_POST['customized']`.
	 * @param array  $setting_args  yoghbl_customize_Setting or a subclass.
	 * @return string
	 */
	public function filter_dynamic_setting_class( $setting_class, $setting_id, $setting_args ) {
		unset( $setting_id );

		if ( ! empty( $setting_args['type'] ) && YoghBL_Customizer_Setting_Nav_Menu_Item::TYPE === $setting_args['type'] ) {
			$setting_class = 'YoghBL_Customizer_Setting_Nav_Menu_Item';
		}
		return $setting_class;
	}

	public function customize_register() {
		$changeset = $this->manager->unsanitized_post_values();

		// Preview settings for nav menus early so that the sections and controls will be added properly.
		$nav_menus_setting_ids = array();
		foreach ( array_keys( $changeset ) as $setting_id ) {
			if ( preg_match( '/^(yoghbl_nav_menu_item)\[/', $setting_id ) ) {
				$nav_menus_setting_ids[] = $setting_id;
			}
		}
		$settings = $this->manager->add_dynamic_settings( $nav_menus_setting_ids );
		if ( $this->manager->settings_previewed() ) {
			foreach ( $settings as $setting ) {
				$setting->preview();
			}
		}

		// Require JS-rendered control types.
		$this->manager->register_control_type( 'YoghBL_Customizer_Control_Nav_Menu' );
		$this->manager->register_control_type( 'YoghBL_Customizer_Control_Nav_Menu_Item' );

		$this->manager->add_section(
			'yoghbl_links',
			array(
				'title'    => __( 'Links', 'yogh-bio-links' ),
				'priority' => 20,
				'panel'    => 'yoghbl',
			)
		);

		$this->manager->add_section(
			new YoghBL_Customizer_Section_Nav_Menu(
				$this->manager,
				'yoghbl_links',
				array(
					'title'    => __( 'Links', 'yogh-bio-links' ),
					'priority' => 10,
					'panel'    => 'yoghbl',
				)
			)
		);

		$this->manager->add_setting(
			new YoghBL_Customizer_Setting_Nav_Menu(
				$this->manager,
				'yoghbl_links',
				array(
					'transport' => 'postMessage',
				)
			)
		);

		$links = (array) yoghbl_links();
		foreach ( array_values( $links ) as $i => $link ) {
			$link_setting_id = 'yoghbl_nav_menu_item[' . $link->hash . ']';

			$value = (array) $link;

			$this->manager->add_setting(
				new YoghBL_Customizer_Setting_Nav_Menu_Item(
					$this->manager,
					$link_setting_id,
					array(
						'value'     => $value,
						'transport' => 'postMessage',
					)
				)
			);

			$this->manager->add_control(
				new YoghBL_Customizer_Control_Nav_Menu_Item(
					$this->manager,
					$link_setting_id,
					array(
						'label'    => $link->title,
						'section'  => 'yoghbl_links',
						'priority' => 10 + $i,
					)
				)
			);

		}
	}

	/**
	 * Gets the base10 intval.
	 *
	 * This is used as a setting's sanitize_callback; we can't use just plain
	 * intval because the second argument is not what intval() expects.
	 *
	 * @since 4.3.0
	 *
	 * @param mixed $value Number to convert.
	 * @return int Integer.
	 */
	public function intval_base10( $value ) {
		return intval( $value, 10 );
	}

	/**
	 * Returns an array of all the available item types.
	 *
	 * @since 4.3.0
	 * @since 4.7.0  Each array item now includes a `$type_label` in addition to `$title`, `$type`, and `$object`.
	 *
	 * @return array The available menu item types.
	 */
	public function available_item_types() {
		$item_types = array();

		$post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'objects' );
		if ( $post_types ) {
			foreach ( $post_types as $slug => $post_type ) {
				$item_types[] = array(
					'title'      => wp_strip_all_tags($post_type->labels->name),
					'type_label' => wp_strip_all_tags($post_type->labels->singular_name),
					'type'       => 'post_type',
					'object'     => wp_strip_all_tags($post_type->name),
				);
			}
		}

		$taxonomies = get_taxonomies( array( 'show_in_nav_menus' => true ), 'objects' );
		if ( $taxonomies ) {
			foreach ( $taxonomies as $slug => $taxonomy ) {
				if ( 'post_format' === $taxonomy && ! current_theme_supports( 'post-formats' ) ) {
					continue;
				}
				$item_types[] = array(
					'title'      => wp_strip_all_tags($taxonomy->labels->name),
					'type_label' => wp_strip_all_tags($taxonomy->labels->singular_name),
					'type'       => 'taxonomy',
					'object'     => wp_strip_all_tags($taxonomy->name),
				);
			}
		}

		/**
		 * Filters the available menu item types.
		 *
		 * @since 4.3.0
		 * @since 4.7.0  Each array item now includes a `$type_label` in addition to `$title`, `$type`, and `$object`.
		 *
		 * @param array $item_types Navigation menu item types.
		 */
		$item_types = apply_filters( 'customize_nav_menu_available_item_types', $item_types );

		return $item_types;
	}

	/**
	 * Adds a new `auto-draft` post.
	 *
	 * @since 4.7.0
	 *
	 * @param array $postarr {
	 *     Post array. Note that post_status is overridden to be `auto-draft`.
	 *
	 * @var string $post_title   Post title. Required.
	 * @var string $post_type    Post type. Required.
	 * @var string $post_name    Post name.
	 * @var string $post_content Post content.
	 * }
	 * @return WP_Post|WP_Error Inserted auto-draft post object or error.
	 */
	public function insert_auto_draft_post( $postarr ) {
		if ( ! isset( $postarr['post_type'] ) ) {
			return new WP_Error( 'unknown_post_type', __( 'Invalid post type.', 'yogh-bio-links' ) );
		}
		if ( empty( $postarr['post_title'] ) ) {
			return new WP_Error( 'empty_title', __( 'Empty title.', 'yogh-bio-links' ) );
		}
		if ( ! empty( $postarr['post_status'] ) ) {
			return new WP_Error( 'status_forbidden', __( 'Status is forbidden.', 'yogh-bio-links' ) );
		}

		/*
		 * If the changeset is a draft, this will change to draft the next time the changeset
		 * is updated; otherwise, auto-draft will persist in autosave revisions, until save.
		 */
		$postarr['post_status'] = 'auto-draft';

		// Auto-drafts are allowed to have empty post_names, so it has to be explicitly set.
		if ( empty( $postarr['post_name'] ) ) {
			$postarr['post_name'] = sanitize_title( $postarr['post_title'] );
		}
		if ( ! isset( $postarr['meta_input'] ) ) {
			$postarr['meta_input'] = array();
		}
		$postarr['meta_input']['_customize_draft_post_name'] = $postarr['post_name'];
		$postarr['meta_input']['_customize_changeset_uuid']  = $this->manager->changeset_uuid();
		unset( $postarr['post_name'] );

		add_filter( 'wp_insert_post_empty_content', '__return_false', 1000 );
		$r = wp_insert_post( wp_slash( $postarr ), true );
		remove_filter( 'wp_insert_post_empty_content', '__return_false', 1000 );

		if ( is_wp_error( $r ) ) {
			return $r;
		} else {
			return get_post( $r );
		}
	}

	/**
	 * Ajax handler for adding a new auto-draft post.
	 *
	 * @since 4.7.0
	 */
	public function ajax_insert_auto_draft_post() {
		if ( ! check_ajax_referer( 'customize-menus', 'customize-menus-nonce', false ) ) {
			wp_send_json_error( 'bad_nonce', 400 );
		}

		if ( ! current_user_can( 'customize' ) ) {
			wp_send_json_error( 'customize_not_allowed', 403 );
		}

		if ( empty( $_POST['params'] ) || ! is_array( $_POST['params'] ) ) {
			wp_send_json_error( 'missing_params', 400 );
		}

		$params         = sanitize_text_field( wp_unslash( $_POST['params'] ) );
		$illegal_params = array_diff( array_keys( $params ), array( 'post_type', 'post_title' ) );
		if ( ! empty( $illegal_params ) ) {
			wp_send_json_error( 'illegal_params', 400 );
		}

		$params = array_merge(
			array(
				'post_type'  => '',
				'post_title' => '',
			),
			$params
		);

		if ( empty( $params['post_type'] ) || ! post_type_exists( $params['post_type'] ) ) {
			status_header( 400 );
			wp_send_json_error( 'missing_post_type_param' );
		}

		$post_type_object = get_post_type_object( $params['post_type'] );
		if ( ! current_user_can( $post_type_object->cap->create_posts ) || ! current_user_can( $post_type_object->cap->publish_posts ) ) {
			status_header( 403 );
			wp_send_json_error( 'insufficient_post_permissions' );
		}

		$params['post_title'] = trim( $params['post_title'] );
		if ( '' === $params['post_title'] ) {
			status_header( 400 );
			wp_send_json_error( 'missing_post_title' );
		}

		$r = $this->insert_auto_draft_post( $params );
		if ( is_wp_error( $r ) ) {
			$error = $r;
			if ( ! empty( $post_type_object->labels->singular_name ) ) {
				$singular_name = $post_type_object->labels->singular_name;
			} else {
				$singular_name = __( 'Post', 'yogh-bio-links' );
			}

			$data = array(
				/* translators: 1: Post type name, 2: Error message. */
				'message' => sprintf( __( '%1$s could not be created: %2$s', 'yogh-bio-links' ), $singular_name, $error->get_error_message() ),
			);
			wp_send_json_error( $data );
		} else {
			$post = $r;
			$data = array(
				'post_id' => $post->ID,
				'url'     => get_permalink( $post->ID ),
			);
			wp_send_json_success( $data );
		}
	}

	/**
	 * Prints the JavaScript templates used to render Menu Customizer components.
	 *
	 * Templates are imported into the JS use wp.template.
	 *
	 * @since 4.3.0
	 */
	public function print_templates() {
		?>
		<script type="text/html" id="tmpl-available-yoghbl-menu-item">
			<li id="yoghbl-menu-item-tpl-{{ data.id }}" class="menu-item-tpl" data-menu-item-id="{{ data.id }}">
				<div class="menu-item-bar">
					<div class="menu-item-handle">
						<span class="item-type" aria-hidden="true">{{ data.type_label }}</span>
						<span class="item-title" aria-hidden="true">
							<span class="menu-item-title<# if ( ! data.title ) { #> no-title<# } #>">{{ data.title || wp.customize.Menus.data.l10n.untitled }}</span>
						</span>
						<button type="button" class="button-link item-add">
							<span class="screen-reader-text">
							<?php
								/* translators: 1: Title of a menu item, 2: Type of a menu item. */
								printf( __( 'Add to menu: %1$s (%2$s)' ), '{{ data.title || wp.customize.Menus.data.l10n.untitled }}', '{{ data.type_label }}' );
							?>
							</span>
						</button>
					</div>
				</div>
			</li>
		</script>

		<script type="text/html" id="tmpl-yoghbl-menu-item-reorder-nav">
			<div class="menu-item-reorder-nav">
				<?php
				printf(
					'<button type="button" class="menus-move-up">%1$s</button><button type="button" class="menus-move-down">%2$s</button>',
					__( 'Move up' ),
					__( 'Move down' )
				);
				?>
			</div>
		</script>

		<script type="text/html" id="tmpl-nav-menu-delete-button">
			<div class="menu-delete-item">
				<button type="button" class="button-link button-link-delete">
					<?php _e( 'Delete Menu', 'yogh-bio-links' ); ?>
				</button>
			</div>
		</script>

		<script type="text/html" id="tmpl-nav-menu-submit-new-button">
			<p id="customize-new-menu-submit-description"><?php _e( 'Click &#8220;Next&#8221; to start adding links to your new menu.' ); ?></p>
			<button id="customize-new-menu-submit" type="button" class="button" aria-describedby="customize-new-menu-submit-description"><?php _e( 'Next', 'yogh-bio-links' ); ?></button>
		</script>

		<script type="text/html" id="tmpl-nav-menu-locations-header">
			<span class="customize-control-title customize-section-title-menu_locations-heading">{{ data.l10n.locationsTitle }}</span>
			<p class="customize-control-description customize-section-title-menu_locations-description">{{ data.l10n.locationsDescription }}</p>
		</script>

		<script type="text/html" id="tmpl-nav-menu-create-menu-section-title">
			<p class="add-new-menu-notice">
				<?php _e( 'It does not look like your site has any menus yet. Want to build one? Click the button to start.' ); ?>
			</p>
			<p class="add-new-menu-notice">
				<?php _e( 'You&#8217;ll create a menu, assign it a location, and add menu items like links to pages and categories. If your theme has multiple menu areas, you might need to create more than one.' ); ?>
			</p>
			<h3>
				<button type="button" class="button customize-add-menu-button">
					<?php _e( 'Create New Menu' ); ?>
				</button>
			</h3>
		</script>
		<?php
	}

	public function available_items_template() {
		?>
		<div id="available-yoghbl-menu-items" class="accordion-container">
			<div class="customize-section-title">
				<button type="button" class="customize-section-back" tabindex="-1">
					<span class="screen-reader-text"><?php _e( 'Back', 'yogh-bio-links' ); ?></span>
				</button>
				<h3>
					<span class="customize-action">
						<?php
							/* translators: &#9656; is the unicode right-pointing triangle. %s: Section title in the Customizer. */
							printf( __( 'Customizing &#9656; %s' ), esc_html( $this->manager->get_panel( 'nav_menus' )->title ) );
						?>
					</span>
					<?php _e( 'Add Menu Items' ); ?>
				</h3>
			</div>
			<?php
			$this->print_custom_links_available_menu_item();
			?>
		</div><!-- #available-menu-items -->
		<?php
	}

	/**
	 * Prints the markup for new menu items.
	 *
	 * To be used in the template #available-menu-items.
	 *
	 * @since 4.7.0
	 *
	 * @param array $available_item_type Menu item data to output, including title, type, and label.
	 */
	protected function print_post_type_container( $available_item_type ) {
		$id = sprintf( 'available-menu-items-%s-%s', $available_item_type['type'], $available_item_type['object'] );
		?>
		<div id="<?php echo esc_attr( $id ); ?>" class="accordion-section">
			<h4 class="accordion-section-title" role="presentation">
				<?php echo esc_html( $available_item_type['title'] ); ?>
				<span class="spinner"></span>
				<span class="no-items"><?php _e( 'No items' ); ?></span>
				<button type="button" class="button-link" aria-expanded="false">
					<span class="screen-reader-text">
					<?php
						/* translators: %s: Title of a section with menu items. */
						printf( __( 'Toggle section: %s', 'yogh-bio-links' ), esc_html( $available_item_type['title'] ) );
					?>
						</span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
			</h4>
			<div class="accordion-section-content">
				<?php if ( 'post_type' === $available_item_type['type'] ) : ?>
					<?php $post_type_obj = get_post_type_object( $available_item_type['object'] ); ?>
					<?php if ( current_user_can( $post_type_obj->cap->create_posts ) && current_user_can( $post_type_obj->cap->publish_posts ) ) : ?>
						<div class="new-content-item">
							<label for="<?php echo esc_attr( 'create-item-input-' . $available_item_type['object'] ); ?>" class="screen-reader-text"><?php echo esc_html( $post_type_obj->labels->add_new_item ); ?></label>
							<input type="text" id="<?php echo esc_attr( 'create-item-input-' . $available_item_type['object'] ); ?>" class="create-item-input" placeholder="<?php echo esc_attr( $post_type_obj->labels->add_new_item ); ?>">
							<button type="button" class="button add-content"><?php _e( 'Add' ); ?></button>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				<ul class="available-menu-items-list" data-type="<?php echo esc_attr( $available_item_type['type'] ); ?>" data-object="<?php echo esc_attr( $available_item_type['object'] ); ?>" data-type_label="<?php echo esc_attr( isset( $available_item_type['type_label'] ) ? $available_item_type['type_label'] : $available_item_type['type'] ); ?>"></ul>
			</div>
		</div>
		<?php
	}

	protected function print_custom_links_available_menu_item() {
		?>
		<div id="new-custom-menu-item" class="accordion-section open">
			<div class="accordion-section-content customlinkdiv">
				<p id="menu-item-url-wrap" class="wp-clearfix">
					<label class="howto" for="custom-yoghbl-menu-item-url"><?php _e( 'URL', 'yogh-bio-links' ); ?></label>
					<input id="custom-yoghbl-menu-item-url" name="yobhbl-menu-item[-1][url]" type="text" class="code menu-item-textbox" placeholder="https://">
				</p>
				<p id="menu-item-name-wrap" class="wp-clearfix">
					<label class="howto" for="custom-yoghbl-menu-item-name"><?php _e( 'Link Text', 'yogh-bio-links' ); ?></label>
					<input id="custom-yoghbl-menu-item-name" name="yobhbl-menu-item[-1][title]" type="text" class="regular-text menu-item-textbox">
				</p>
				<p class="button-controls">
					<span class="add-to-menu">
						<input type="submit" class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add', 'yogh-bio-links' ); ?>" name="add-custom-menu-item" id="custom-yoghbl-menu-item-submit">
						<span class="spinner"></span>
					</span>
				</p>
			</div>
		</div>
		<?php
	}

	//
	// Start functionality specific to partial-refresh of menu changes in Customizer preview.
	//

	/**
	 * Nav menu args used for each instance, keyed by the args HMAC.
	 *
	 * @since 4.3.0
	 * @var array
	 */
	public $preview_nav_menu_instance_args = array();

	/**
	 * Filters arguments for dynamic nav_menu selective refresh partials.
	 *
	 * @since 4.5.0
	 *
	 * @param array|false $partial_args Partial args.
	 * @param string      $partial_id   Partial ID.
	 * @return array Partial args.
	 */
	public function customize_dynamic_partial_args( $partial_args, $partial_id ) {

		if ( preg_match( '/^yoghbl_links$/', $partial_id ) ) {
			if ( false === $partial_args ) {
				$partial_args = array();
			}
			$partial_args = array_merge(
				$partial_args,
				array(
					'type'                => 'yoghbl_links',
					'render_callback'     => array( $this, 'render_nav_menu_partial' ),
					'container_inclusive' => true,
					'settings'            => array(), // Empty because the nav menu instance may relate to a menu or a location.
				)
			);
		}

		return $partial_args;
	}

	/**
	 * Adds hooks for the Customizer preview.
	 *
	 * @since 4.3.0
	 */
	public function customize_preview_init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'customize_preview_enqueue_deps' ) );
		add_filter( 'yoghbl_links_html_args', array( $this, 'filter_wp_nav_menu_args' ), 1000 );
		add_filter( 'yoghbl_links_html_output', array( $this, 'filter_wp_nav_menu' ), 10, 2 );
		add_filter( 'wp_footer', array( $this, 'export_preview_data' ), 1 );
		add_filter( 'customize_render_partials_response', array( $this, 'export_partial_rendered_nav_menu_instances' ) );
	}

	/**
	 * Keeps track of the arguments that are being passed to wp_nav_menu().
	 *
	 * @since 4.3.0
	 *
	 * @see wp_nav_menu()
	 * @see yoghbl_customize_Widgets::filter_dynamic_sidebar_params()
	 *
	 * @param array $args An array containing wp_nav_menu() arguments.
	 * @return array Arguments.
	 */
	public function filter_wp_nav_menu_args( $args ) {
		$can_partial_refresh = true;

		$args['can_partial_refresh'] = $can_partial_refresh;

		$exported_args = $args;

		// Empty out args which may not be JSON-serializable.
		if ( ! $can_partial_refresh ) {
			$exported_args['fallback_cb'] = '';
			$exported_args['walker']      = '';
		}

		/*
		 * Replace object menu arg with a term_id menu arg, as this exports better
		 * to JS and is easier to compare hashes.
		 */
		if ( ! empty( $exported_args['menu'] ) && is_object( $exported_args['menu'] ) ) {
			$exported_args['menu'] = $exported_args['menu']->term_id;
		}

		ksort( $exported_args );
		$exported_args['args_hmac'] = $this->hash_nav_menu_args( $exported_args );

		$args['customize_preview_nav_menus_args']                            = $exported_args;
		$this->preview_nav_menu_instance_args[ $exported_args['args_hmac'] ] = $exported_args;
		return $args;
	}

	/**
	 * Prepares wp_nav_menu() calls for partial refresh.
	 *
	 * Injects attributes into container element.
	 *
	 * @since 4.3.0
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string $nav_menu_content The HTML content for the navigation menu.
	 * @param object $args             An object containing wp_nav_menu() arguments.
	 * @return string Nav menu HTML with selective refresh attributes added if partial can be refreshed.
	 */
	public function filter_wp_nav_menu( $nav_menu_content, $args ) {
		if ( isset( $args->customize_preview_nav_menus_args['can_partial_refresh'] ) && $args->customize_preview_nav_menus_args['can_partial_refresh'] ) {
			$attributes       = sprintf( ' data-customize-partial-id="%s"', 'yoghbl_links' );
			$attributes      .= ' data-customize-partial-type="yoghbl_links"';
			$attributes      .= sprintf( ' data-customize-partial-placement-context="%s"', esc_attr( wp_json_encode( $args->customize_preview_nav_menus_args ) ) );
			$nav_menu_content = preg_replace( '#^(<\w+)#', '$1 ' . str_replace( '\\', '\\\\', $attributes ), $nav_menu_content, 1 );
		}
		return $nav_menu_content;
	}

	/**
	 * Hashes (hmac) the nav menu arguments to ensure they are not tampered with when
	 * submitted in the Ajax request.
	 *
	 * Note that the array is expected to be pre-sorted.
	 *
	 * @since 4.3.0
	 *
	 * @param array $args The arguments to hash.
	 * @return string Hashed nav menu arguments.
	 */
	public function hash_nav_menu_args( $args ) {
		return wp_hash( serialize( $args ) );
	}

	/**
	 * Enqueues scripts for the Customizer preview.
	 *
	 * @since 4.3.0
	 */
	public function customize_preview_enqueue_deps() {
		wp_enqueue_script( 'customize-preview-nav-menus' ); // Note that we have overridden this.
	}

	/**
	 * Exports data from PHP to JS.
	 *
	 * @since 4.3.0
	 */
	public function export_preview_data() {

		// Why not wp_localize_script? Because we're not localizing, and it forces values into strings.
		$exports = array(
			'navMenuInstanceArgs' => $this->preview_nav_menu_instance_args,
		);
		printf( '<script>var _wpCustomizePreviewNavMenusExports = %s;</script>', wp_json_encode( $exports ) );
	}

	/**
	 * Exports any wp_nav_menu() calls during the rendering of any partials.
	 *
	 * @since 4.5.0
	 *
	 * @param array $response Response.
	 * @return array Response.
	 */
	public function export_partial_rendered_nav_menu_instances( $response ) {
		$response['nav_menu_instance_args'] = $this->preview_nav_menu_instance_args;
		return $response;
	}

	/**
	 * Renders a specific menu via wp_nav_menu() using the supplied arguments.
	 *
	 * @since 4.3.0
	 *
	 * @see wp_nav_menu()
	 *
	 * @param yoghbl_customize_Partial $partial       Partial.
	 * @param array                $nav_menu_args Nav menu args supplied as container context.
	 * @return string|false
	 */
	public function render_nav_menu_partial( $partial, $nav_menu_args ) {
		unset( $partial );

		ob_start();
		yoghbl_links_html( $nav_menu_args );
		$content = ob_get_clean();

		return $content;
	}
}
