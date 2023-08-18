<?php

/**
 * Adds options to the customizer for YoghBioLinks.
 *
 * @version 1.0.0
 * @package YoghBioLinks
 */

defined( 'ABSPATH' ) || exit;

/**
 * YoghBL_Customizer class.
 */
class YoghBL_Customizer {

	/**
	 * yoghbl_customize_Manager instance.
	 *
	 * @var yoghbl_customize_Manager
	 */
	public $manager;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array($this, 'register_scripts'));
		add_action( 'customize_register', array( $this, 'add_sections' ) );
		add_action( 'customize_controls_print_styles', array( $this, 'add_styles' ) );
		//add_action( 'customize_controls_print_scripts', array( $this, 'add_scripts' ), 30 );
		add_action( 'customize_preview_init', array( $this, 'preview_init' ) );
		add_action( 'customize_save_yoghbl_url_slug', array( $this, 'save_slug' ) );
	}

	/**
	 * Enqueue scripts for the customizer preview.
	 *
	 * @return void
	 */
	public function preview_init() {
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;

		wp_enqueue_script(
			'yoghbl-customize-helpers',
			YoghBL()->plugin_url() . '/assets/js/customize-helpers.js',
			array(),
			$version,
			true
		);

		wp_enqueue_script(
			'yoghbl-customize-preview',
			YoghBL()->plugin_url() . '/assets/js/customize-preview.js',
			array( 'customize-preview', 'customize-selective-refresh', 'jquery', 'yoghbl-customize-helpers' ),
			$version,
			true
		);
	}

	/**
	 * Add settings to the customizer.
	 *
	 * @param yoghbl_customize_Manager $yoghbl_customize Theme Customizer object.
	 */
	public function add_sections( $yoghbl_customize ) {
		$yoghbl_customize->add_panel(
			'yoghbl',
			array(
				'priority'   => 190,
				'capability' => 'edit_pages',
				'title'      => __( 'Yogh Bio Links', 'yogh-bio-links' ),
			)
		);

		$this->add_header_section( $yoghbl_customize );
		$this->add_links_section_render( $yoghbl_customize );
		$this->add_colors_section( $yoghbl_customize );
		if ( 'yes' !== get_option( 'yoghbl_only_color_bg', 'yes' ) ) {
			$this->add_button_colors_section( $yoghbl_customize );
		}
		$this->add_footer_section( $yoghbl_customize );
	}

	/**
	 * OLD::tasks/37624335
	 * CSS styles to improve our form.
	 * @description style in "/assets/css/customizer-yoghbl.css"
	 */
	public function add_styles() {
		// CSS que está aqui, está no arquivo: /assets/css/customizer-yoghbl.css", era um <style ...

	}
	/**
	 * NEW::tasks/37624335
	 * CSS styles to improve our form, replaced to method 'add_styles'
	 * @description style in "/assets/css/customizer-yoghbl.css"
	 */
	public function register_scripts() {
		// Register CSS
		wp_enqueue_style('yoghbl', plugin_dir_url(__FILE__) . 'assets/css/customizer-yoghbl.css');

		// Register JavaScript
		wp_enqueue_script('yoghbl', plugin_dir_url(__FILE__) . 'assets/js/customizer-yoghbl.js', array('jquery'), '1.0', true);

	}

	/**
	 * Scripts to improve our form.
	 */
	public function add_scripts() {
		//
	}

	/**
	 * Header section.
	 *
	 * @param yoghbl_customize_Manager $yoghbl_customize Theme Customizer object.
	 */
	private function add_header_section( $yoghbl_customize ) {
		$yoghbl_customize->add_section(
			'yoghbl_header',
			array(
				'title'    => __( 'Header', 'yogh-bio-links' ),
				'priority' => 10,
				'panel'    => 'yoghbl',
			)
		);

		$yoghbl_customize->add_setting(
			'yoghbl_logo',
			array(
				'type'      => 'option',
				'transport' => 'postMessage',
			)
		);

		$width  = 96;
		$height = 96;

		$size = (array) apply_filters( 'yoghbl_logo_size', array( $width, $height ) );

		if ( ! isset( $size[0] ) || 0 === (int) $size[0] ) {
			$size[0] = $width;
		}

		if ( ! isset( $size[1] ) || 0 === (int) $size[1] ) {
			$size[1] = $height;
		}

		$size = array_map( 'intval', $size );

		$size_string = sprintf( '%sx%d', $size[0], $size[1] );

		// phpcs:disable WordPress.WP.I18n.MissingArgDomain
		$yoghbl_customize->add_control(
			new yoghbl_customize_Cropped_Image_Control(
				$yoghbl_customize,
				'yoghbl_logo',
				array(
					'label'         => __( 'Logo', 'yogh-bio-links' ),
					'description'   => sprintf(
						/* translators: 1: Suggested width number, 2: Suggested height number. */
						__( 'Suggested image dimensions: %1$s by %2$s pixels.', 'yogh-bio-links' ),
						$size[0],
						$size[1]
					),
					'section'       => 'yoghbl_header',
					'priority'      => 8,
					'height'        => $size[0],
					'width'         => $size[1],
					'flex_height'   => $size[0],
					'flex_width'    => $size[1],
					'button_labels' => array(
						'select'       => __( 'Select logo', 'yogh-bio-links' ),
						'change'       => __( 'Change logo', 'yogh-bio-links' ),
						'remove'       => __( 'Remove', 'yogh-bio-links' ),
						'default'      => __( 'Default', 'yogh-bio-links' ),
						'placeholder'  => __( 'No logo selected', 'yogh-bio-links' ),
						'frame_title'  => __( 'Select logo', 'yogh-bio-links' ),
						'frame_button' => __( 'Choose logo', 'yogh-bio-links' ),
					),
				)
			)
		);
		// phpcs:enable WordPress.WP.I18n.MissingArgDomain

		$yoghbl_customize->add_setting(
			'yoghbl_title',
			array(
				'default'           => get_bloginfo( 'name' ),
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'esc_html',
				'transport'         => 'postMessage',
			)
		);

		$yoghbl_customize->add_control(
			'yoghbl_title',
			array(
				'label'    => __( 'Title', 'yogh-bio-links' ),
				'section'  => 'yoghbl_header',
				'settings' => 'yoghbl_title',
				'type'     => 'text',
			)
		);

		$yoghbl_customize->add_setting(
			'yoghbl_description',
			array(
				'default'           => esc_html(get_bloginfo( 'description' )),
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'wp_kses_post',
				'transport'         => 'postMessage',
			)
		);

		$yoghbl_customize->add_control(
			'yoghbl_description',
			array(
				'label'    => __( 'Description', 'yogh-bio-links' ),
				'section'  => 'yoghbl_header',
				'settings' => 'yoghbl_description',
				'type'     => 'textarea',
			)
		);

		include_once YOGHBL_ABSPATH . 'includes/customizer/class-yoghbl-customizer-control-slug.php';

		$yoghbl_customize->add_setting(
			'yoghbl_url_slug',
			array(
				'default'           => get_post( yoghbl_get_page_id( 'biolinks' ) )->post_name,
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'sanitize_title',
				'transport'         => 'postMessage',
			)
		);

		$yoghbl_customize->add_control(
			new YoghBL_Customizer_Control_Slug(
				$yoghbl_customize,
				'yoghbl_url_slug',
				array(
					'label'       => __( 'Permalink', 'yogh-bio-links' ),
					'desctiption' => __( 'URL Slug', 'yogh-bio-links' ),
					'section'     => 'yoghbl_header',
					'settings'    => 'yoghbl_url_slug',
					'type'        => 'text',
				)
			)
		);

		if ( isset( $yoghbl_customize->selective_refresh ) ) {
			$yoghbl_customize->selective_refresh->add_partial(
				'yoghbl_logo',
				array(
					'selector'            => '.yoghbl-logo',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbl_logo',
				)
			);

			$yoghbl_customize->selective_refresh->add_partial(
				'yoghbl_title',
				array(
					'selector'            => 'h1',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbl_title',
				)
			);

			$yoghbl_customize->selective_refresh->add_partial(
				'yoghbl_description',
				array(
					'selector'            => '.yoghbl-description',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbl_description',
				)
			);

			$yoghbl_customize->selective_refresh->add_partial(
				'yoghbl_url_slug',
				array()
			);
		}
	}

	private function add_links_section_render( $yoghbl_customize ) {
		include_once YOGHBL_ABSPATH . 'includes/customizer/render/class-yoghbl-customizer-nav-menus.php';

		$yoghbl_customize->yoghbl_nav_menus = new YoghBL_Customizer_Nav_Menus( $yoghbl_customize );
	}

	/**
	 * Links section.
	 *
	 * @param yoghbl_customize_Manager $yoghbl_customize Theme Customizer object.
	 */
	private function add_links_section( $yoghbl_customize ) {
		include_once YOGHBL_ABSPATH . 'includes/customizer/links/class-yoghbl-customizer-links.php';

		$yoghbl_customize->yoghbl_links = new YoghBL_Customizer_Links( $yoghbl_customize );
	}

	/**
	 * Links section.
	 *
	 * @param yoghbl_customize_Manager $yoghbl_customize Theme Customizer object.
	 */
	private function add_links_section_nav_menu( $yoghbl_customize ) {
		include_once YOGHBL_ABSPATH . 'includes/customizer/nav-menus/class-wp-customize-nav-menus2.php';

		// require_once ABSPATH . WPINC . '/class-wp-customize-nav-menus.php';
		$yoghbl_customize->nav_menus2 = new yoghbl_customize_Nav_Menus2( $yoghbl_customize );
	}

	/**
	 * Links section.
	 *
	 * @param yoghbl_customize_Manager $yoghbl_customize Theme Customizer object.
	 */
	private function add_links_section_legacy( $yoghbl_customize ) {
		$this->manager = $yoghbl_customize;

		$changeset = $this->manager->unsanitized_post_values();
		// Preview settings for nav menus early so that the sections and controls will be added properly.
		// $nav_menus_setting_ids = array();
		// foreach ( array_keys( $changeset ) as $setting_id ) {
		// 	if ( preg_match( '/^(yoghbl_links|yoghbl_link)\[/', $setting_id ) ) {
		// 		$nav_menus_setting_ids[] = $setting_id;
		// 	}
		// }
		// $settings = $this->manager->add_dynamic_settings( $nav_menus_setting_ids );
		// if ( $this->manager->settings_previewed() ) {
		// 	foreach ( $settings as $setting ) {
		// 		$setting->preview();
		// 	}
		// }

		// Require JS-rendered control types.
		$this->manager->register_control_type( 'YoghBL_Customizer_Control_Links' );

		$yoghbl_customize->add_section(
			'yoghbl_links',
			array(
				'title'    => __( 'Links', 'yogh-bio-links' ),
				'priority' => 20,
				'panel'    => 'yoghbl',
			)
		);

		include_once YOGHBL_ABSPATH . 'includes/customizer/links-legacy/class-yoghbl-customizer-control-links.php';

		$yoghbl_customize->add_setting(
			'yoghbl_links',
			array(
				'default'           => yoghbl_default_links(),
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => array( $this, 'sanitize_links' ),
				'transport'         => 'postMessage',
			)
		);

		$yoghbl_customize->add_control(
			new YoghBL_Customizer_Control_Links(
				$yoghbl_customize,
				'yoghbl_links',
				array(
					'label'    => __( 'Links', 'yogh-bio-links' ),
					'description' => __("One link per line separating the title of the link by a colon.\n\nExample:\nYogh:https://www.yogh.com.br/", 'yogh-bio-links'),
					'section'  => 'yoghbl_links',
					'settings' => 'yoghbl_links',
					'type'     => 'textarea',
				)
			)
		);

		$links = yoghbl_links();
		if ( $links ) {
			include_once YOGHBL_ABSPATH . 'includes/customizer/links-legacy/class-yoghbl-customizer-setting-link.php';
			include_once YOGHBL_ABSPATH . 'includes/customizer/links-legacy/class-yoghbl-customizer-control-link.php';
		}
		foreach ( array_values( $links ) as $i => $link ) {

			$link_setting_id = 'yoghbl_link[' . $link->hash . ']';

			$value = (array) $link;

			$yoghbl_customize->add_setting(
				new YoghBL_Customizer_Setting_Link(
					$yoghbl_customize,
					$link_setting_id,
					array(
						'value'     => $value,
						'transport' => 'postMessage',
					)
				)
			);

			// Create a control for each menu item.
			$yoghbl_customize->add_control(
				new YoghBL_Customizer_Control_Link(
					$yoghbl_customize,
					$link_setting_id,
					array(
						'label'    => wp_strip_all_tags($link->title),
						'section'  => 'yoghbl_links',
						'priority' => 10 + $i,
					)
				)
			);
		}

		if ( isset( $yoghbl_customize->selective_refresh ) ) {
			$yoghbl_customize->selective_refresh->add_partial(
				'yoghbl_links',
				array(
					'selector'            => '.yoghbl-links',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbl_links_html',
				)
			);
		}
	}

	/**
	 * Colors section.
	 *
	 * @param yoghbl_customize_Manager $yoghbl_customize Theme Customizer object.
	 */
	private function add_colors_section( $yoghbl_customize ) {
		$title = __( 'Colors', 'yogh-bio-links' );
		if ( 'yes' !== sanitize_text_field(get_option( 'yoghbl_only_color_bg', 'yes' )) ) {
			$title = __( 'Page Colors', 'yogh-bio-links' );
		}
		$yoghbl_customize->add_section(
			'yoghbl_colors',
			array(
				'title'    => $title,
				'priority' => 30,
				'panel'    => 'yoghbl',
			)
		);

		// Page Background Color.
		$yoghbl_customize->add_setting(
			'yoghbl_background_color',
			array(
				'default'           => '#28303D',
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$yoghbl_customize->add_control(
			new yoghbl_customize_Color_Control(
				$yoghbl_customize,
				'yoghbl_background_color',
				array(
					'label'       => __( 'Background color', 'yogh-bio-links' ),
					'description' => __( 'Dark or bright color combination will be used to make easy for people to read.', 'yogh-bio-links' ),
					'section'     => 'yoghbl_colors',
				)
			)
		);

		if ( 'yes' !== get_option( 'yoghbl_only_color_bg', 'yes' ) ) {
			// Page Text Background Color.
			$yoghbl_customize->add_setting(
				'yoghbl_color',
				array(
					'default'           => '#F0F0F0',
					'type'              => 'option',
					'capability'        => 'edit_pages',
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => 'postMessage',
				)
			);

			$yoghbl_customize->add_control(
				new yoghbl_customize_Color_Control(
					$yoghbl_customize,
					'yoghbl_color',
					array(
						'label'   => __( 'Text color', 'yogh-bio-links' ),
						'section' => 'yoghbl_colors',
					)
				)
			);
		}
	}

	/**
	 * Button colors section.
	 *
	 * @param yoghbl_customize_Manager $yoghbl_customize Theme Customizer object.
	 */
	private function add_button_colors_section( $yoghbl_customize ) {
		$yoghbl_customize->add_section(
			'yoghbl_button_colors',
			array(
				'title'    => __( 'Button Colors', 'yogh-bio-links' ),
				'priority' => 40,
				'panel'    => 'yoghbl',
			)
		);

		// Button Background Color.
		$yoghbl_customize->add_setting(
			'yoghbl_button_background_color',
			array(
				'default'           => '#F0F0F0',
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$yoghbl_customize->add_control(
			new yoghbl_customize_Color_Control(
				$yoghbl_customize,
				'yoghbl_button_background_color',
				array(
					'label'   => __( 'Background color', 'yogh-bio-links' ),
					'section' => 'yoghbl_button_colors',
				)
			)
		);

		// Button Text Background Color.
		$yoghbl_customize->add_setting(
			'yoghbl_button_color',
			array(
				'default'           => '#28303D',
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$yoghbl_customize->add_control(
			new yoghbl_customize_Color_Control(
				$yoghbl_customize,
				'yoghbl_button_color',
				array(
					'label'   => __( 'Text color', 'yogh-bio-links' ),
					'section' => 'yoghbl_button_colors',
				)
			)
		);
	}

	/**
	 * Footer section.
	 *
	 * @param yoghbl_customize_Manager $yoghbl_customize Theme Customizer object.
	 */
	private function add_footer_section( $yoghbl_customize ) {
		$yoghbl_customize->add_section(
			'yoghbl_footer',
			array(
				'title'    => __( 'Footer', 'yogh-bio-links' ),
				'priority' => 50,
				'panel'    => 'yoghbl',
			)
		);

		if ( 'yes' === get_option( 'yoghbl_credit_text', 'no' ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin = get_plugin_data( YOGHBL_PLUGIN_FILE );

			$yoghbl_customize->add_setting(
				'yoghbl_credits_text',
				array(
					'default'    => sprintf(
						'<a href="%s" target="_blank">%s</a>',
						$plugin['PluginURI'],
						/* translators: %s: Plugin Name */
						sprintf( __( 'Created with %s', 'yogh-bio-links' ), $plugin['Name'] )
					),
					'type'       => 'option',
					'capability' => 'edit_pages',
					'transport'  => 'postMessage',
				)
			);

			$yoghbl_customize->add_control(
				'yoghbl_credits_text',
				array(
					'label'    => __( 'Credits text', 'yogh-bio-links' ),
					'section'  => 'yoghbl_footer',
					'settings' => 'yoghbl_credits',
					'type'     => 'textarea',
				)
			);
		} else {
			$yoghbl_customize->add_setting(
				'yoghbl_credits',
				array(
					'default'           => false,
					'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
					'type'              => 'option',
					'capability'        => 'edit_pages',
					'transport'         => 'postMessage',
				)
			);

			$yoghbl_customize->add_control(
				'yoghbl_credits',
				array(
					'label'    => __( 'Hide credits', 'yogh-bio-links' ),
					'section'  => 'yoghbl_footer',
					'settings' => 'yoghbl_credits',
					'type'     => 'checkbox',
				)
			);
		}

		if ( isset( $yoghbl_customize->selective_refresh ) ) {
			$yoghbl_customize->selective_refresh->add_partial(
				'yoghbl_credits',
				array(
					'selector'            => '.yoghbl-credits',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbl_credits',
				)
			);
		}
	}

	/**
	 * Sanitize boolean for checkbox.
	 *
	 * @param bool $checked Whether or not a box is checked.
	 * @return bool
	 */
	public static function sanitize_checkbox( $checked = null ) {
		return (bool) isset( $checked ) && true === $checked;
	}

	/**
	 * Sanitize links string.
	 *
	 * @param bool $value Links string value.
	 * @return bool
	 */
	public static function sanitize_links( $value = null ) {
		return yoghbl_links_encode( yoghbl_links_decode( $value ) );
	}

	public function save_slug( $setting ) {
		$value = $setting->post_value();

		$page_id = yoghbl_get_page_id( 'biolinks' );

		wp_update_post(
			array(
				'ID'        => $page_id,
				'post_name' => sanitize_title($value),
			)
		);
	}
}

global $pagenow;
if ( 'customize.php' === $pagenow || isset( $_REQUEST['customize_theme'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	new YoghBL_Customizer();
}
