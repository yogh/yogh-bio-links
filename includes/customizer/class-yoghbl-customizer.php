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
	 * WP_Customize_Manager instance.
	 *
	 * @var WP_Customize_Manager
	 */
	public $manager;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'customize_register', array( $this, 'add_sections' ) );
		add_action( 'customize_controls_print_styles', array( $this, 'add_styles' ) );
		add_action( 'customize_controls_print_scripts', array( $this, 'add_scripts' ), 30 );
		add_action( 'customize_preview_init', array( $this, 'preview_init' ) );
		add_action( 'customize_save_yoghbiolinks_url_slug', array( $this, 'save_slug' ) );
	}

	/**
	 * Enqueue scripts for the customizer preview.
	 *
	 * @return void
	 */
	public function preview_init() {
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;

		wp_enqueue_script(
			'yoghbiolinks-customize-helpers',
			YoghBL()->plugin_url() . '/assets/js/customize-helpers.js',
			array(),
			$version,
			true
		);

		wp_enqueue_script(
			'yoghbiolinks-customize-preview',
			YoghBL()->plugin_url() . '/assets/js/customize-preview.js',
			array( 'customize-preview', 'customize-selective-refresh', 'jquery', 'yoghbiolinks-customize-helpers' ),
			$version,
			true
		);
	}

	/**
	 * Add settings to the customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function add_sections( $wp_customize ) {
		$wp_customize->add_panel(
			'yoghbiolinks',
			array(
				'priority'   => 190,
				'capability' => 'edit_pages',
				'title'      => __( 'Yogh Bio Links', 'yogh-bio-links' ),
			)
		);

		$this->add_header_section( $wp_customize );
		$this->add_links_section_render( $wp_customize );
		$this->add_colors_section( $wp_customize );
		if ( 'yes' !== get_option( 'yoghbiolinks_only_color_bg', 'yes' ) ) {
			$this->add_button_colors_section( $wp_customize );
		}
		$this->add_footer_section( $wp_customize );
	}

	/**
	 * CSS styles to improve our form.
	 */
	public function add_styles() {
		?>

		<style type="text/css">
			.customize-control .yoghbiolinks-slug-control input[type=text] {
				margin-bottom: 8px;
			}
			.edit-post-post-link__link-post-name {
				font-weight: 600;
			}
			.css-rvs7bx {
				width: 1em;
				height: 1em;
				margin: 0px;
				vertical-align: middle;
				fill: currentcolor;
			}
			.js .customize-control-yoghbiolinks_link .menu-item-handle {
				cursor: default;
			}
			.preview-yoghbiolinks.preview-mobile .wp-full-overlay-main {
				width: 360px;
				height: 640px;
			}
			.preview-yoghbiolinks.preview-mobile .wp-full-overlay-main::-webkit-scrollbar {
				width: 0;
			}
			#customize-theme-controls .add-new-yoghbl-menu-item {
				cursor: pointer;
				float: right;
				margin: 0 0 0 10px;
				transition: all 0.2s;
				-webkit-user-select: none;
				user-select: none;
				outline: none;
			}
			.add-new-yoghbl-menu-item:before {
				content: "\f132";
				display: inline-block;
				position: relative;
				left: -2px;
				top: 0;
				font: normal 20px/1 dashicons;
				vertical-align: middle;
				transition: all 0.2s;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
			}
			#available-yoghbl-menu-items {
				position: absolute;
				top: 0;
				bottom: 0;
				left: -301px;
				visibility: hidden;
				overflow-x: hidden;
				overflow-y: auto;
				width: 300px;
				margin: 0;
				z-index: 4;
				background: #f0f0f1;
				transition: left .18s;
				border-right: 1px solid #dcdcde;
			}
			body.adding-yoghbl-menu-items #available-yoghbl-menu-items {
				left: 0;
				visibility: visible;
			}
			#available-yoghbl-menu-items .accordion-section-content {
				max-height: 290px;
				margin: 0;
				padding: 0;
				position: relative;
				background: transparent;
			}
			#available-yoghbl-menu-items #new-custom-menu-item .accordion-section-content {
				width: 100%;
				box-sizing: border-box;
				padding: 15px;
			}
			#available-yoghbl-menu-items .customize-section-title {
				display: none;
			}
			.control-section-yoghbl_nav_menu .customize-section-description-container {
				margin-bottom: 15px;
			}
			.adding-yoghbl-menu-items .menu-item-bar .item-edit {
				display: none;
			}
			@media screen and (max-width: 640px) {
				#available-yoghbl-menu-items .customize-section-title {
					display: block;
					margin: 0;
				}
				#available-yoghbl-menu-items .customize-section-back {
					height: 69px;
				}
				#available-yoghbl-menu-items .customize-section-title h3 {
					font-size: 20px;
					font-weight: 200;
					padding: 9px 10px 12px 14px;
					margin: 0;
					line-height: 24px;
					color: #50575e;
					display: block;
					overflow: hidden;
					white-space: nowrap;
					text-overflow: ellipsis;
				}
				#available-yoghbl-menu-items .customize-section-title .customize-action {
					font-size: 13px;
					display: block;
					font-weight: 400;
					overflow: hidden;
					white-space: nowrap;
					text-overflow: ellipsis;
				}
				body.adding-yoghbl-menu-items div#available-yoghbl-menu-items {
					width: 100%;
				}
			}
			@media screen and (max-width: 600px) {
				body.adding-yoghbl-menu-items div#available-yoghbl-menu-items {
					top: 46px;
					z-index: 10;
				}
			}
		</style>

		<?php
	}

	/**
	 * Scripts to improve our form.
	 */
	public function add_scripts() {
		?>

		<script type="text/javascript">
			jQuery( function( $ ) {
				wp.customize.panel( 'yoghbiolinks', function( section ) {
					var overlay = $( '.wp-full-overlay' );
					section.expanded.bind( function( isExpanded ) {
						if ( isExpanded ) {
							sessionStorage.setItem( 'currentPreviewUrl', wp.customize.previewer.previewUrl.get() );
							sessionStorage.setItem( 'currentPreviewedDevice', wp.customize.previewedDevice.get() );
							wp.customize.previewer.previewUrl.set( '<?php echo esc_js( yoghbl_get_page_permalink( 'biolinks' ) ); ?>' );
							wp.customize.previewedDevice.set( 'mobile' );
						} else {
							wp.customize.previewer.previewUrl.set( sessionStorage.getItem( 'currentPreviewUrl' ) );
							wp.customize.previewedDevice.set( sessionStorage.getItem( 'currentPreviewedDevice' ) );
						}
						overlay.toggleClass( 'preview-yoghbiolinks' );
					} );
				} );
			} );
		</script>

		<?php
	}

	/**
	 * Header section.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_header_section( $wp_customize ) {
		$wp_customize->add_section(
			'yoghbiolinks_header',
			array(
				'title'    => __( 'Header', 'yogh-bio-links' ),
				'priority' => 10,
				'panel'    => 'yoghbiolinks',
			)
		);

		$wp_customize->add_setting(
			'yoghbiolinks_logo',
			array(
				'type'      => 'option',
				'transport' => 'postMessage',
			)
		);

		$width  = 96;
		$height = 96;

		$size = (array) apply_filters( 'yoghbiolinks_logo_size', array( $width, $height ) );

		if ( ! isset( $size[0] ) || 0 === (int) $size[0] ) {
			$size[0] = $width;
		}

		if ( ! isset( $size[1] ) || 0 === (int) $size[1] ) {
			$size[1] = $height;
		}

		$size = array_map( 'intval', $size );

		$size_string = sprintf( '%sx%d', $size[0], $size[1] );

		// phpcs:disable WordPress.WP.I18n.MissingArgDomain
		$wp_customize->add_control(
			new WP_Customize_Cropped_Image_Control(
				$wp_customize,
				'yoghbiolinks_logo',
				array(
					'label'         => __( 'Logo' ),
					'description'   => sprintf(
						/* translators: 1: Suggested width number, 2: Suggested height number. */
						__( 'Suggested image dimensions: %1$s by %2$s pixels.' ),
						$size[0],
						$size[1]
					),
					'section'       => 'yoghbiolinks_header',
					'priority'      => 8,
					'height'        => $size[0],
					'width'         => $size[1],
					'flex_height'   => $size[0],
					'flex_width'    => $size[1],
					'button_labels' => array(
						'select'       => __( 'Select logo' ),
						'change'       => __( 'Change logo' ),
						'remove'       => __( 'Remove' ),
						'default'      => __( 'Default' ),
						'placeholder'  => __( 'No logo selected' ),
						'frame_title'  => __( 'Select logo' ),
						'frame_button' => __( 'Choose logo' ),
					),
				)
			)
		);
		// phpcs:enable WordPress.WP.I18n.MissingArgDomain

		$wp_customize->add_setting(
			'yoghbiolinks_title',
			array(
				'default'           => get_bloginfo( 'name' ),
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'esc_html',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'yoghbiolinks_title',
			array(
				'label'    => __( 'Title', 'yogh-bio-links' ),
				'section'  => 'yoghbiolinks_header',
				'settings' => 'yoghbiolinks_title',
				'type'     => 'text',
			)
		);

		$wp_customize->add_setting(
			'yoghbiolinks_description',
			array(
				'default'           => get_bloginfo( 'description' ),
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'wp_kses_post',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'yoghbiolinks_description',
			array(
				'label'    => __( 'Description', 'yogh-bio-links' ),
				'section'  => 'yoghbiolinks_header',
				'settings' => 'yoghbiolinks_description',
				'type'     => 'textarea',
			)
		);

		include_once YOGHBL_ABSPATH . 'includes/customizer/class-yoghbl-customizer-control-slug.php';

		$wp_customize->add_setting(
			'yoghbiolinks_url_slug',
			array(
				'default'           => get_post( yoghbl_get_page_id( 'biolinks' ) )->post_name,
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'sanitize_title',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new YoghBL_Customizer_Control_Slug(
				$wp_customize,
				'yoghbiolinks_url_slug',
				array(
					'label'       => __( 'Permalink', 'yogh-bio-links' ),
					'desctiption' => __( 'URL Slug', 'yogh-bio-links' ),
					'section'     => 'yoghbiolinks_header',
					'settings'    => 'yoghbiolinks_url_slug',
					'type'        => 'text',
				)
			)
		);

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'yoghbiolinks_logo',
				array(
					'selector'            => '.yoghbiolinks-logo',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbiolinks_logo',
				)
			);

			$wp_customize->selective_refresh->add_partial(
				'yoghbiolinks_title',
				array(
					'selector'            => 'h1',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbiolinks_title',
				)
			);

			$wp_customize->selective_refresh->add_partial(
				'yoghbiolinks_description',
				array(
					'selector'            => '.yoghbiolinks-description',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbiolinks_description',
				)
			);

			$wp_customize->selective_refresh->add_partial(
				'yoghbiolinks_url_slug',
				array()
			);
		}
	}

	private function add_links_section_render( $wp_customize ) {
		include_once YOGHBL_ABSPATH . 'includes/customizer/render/class-yoghbl-customizer-nav-menus.php';

		$wp_customize->yoghbl_nav_menus = new YoghBL_Customizer_Nav_Menus( $wp_customize );
	}

	/**
	 * Links section.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_links_section( $wp_customize ) {
		include_once YOGHBL_ABSPATH . 'includes/customizer/links/class-yoghbl-customizer-links.php';

		$wp_customize->yoghbiolinks_links = new YoghBL_Customizer_Links( $wp_customize );
	}

	/**
	 * Links section.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_links_section_nav_menu( $wp_customize ) {
		include_once YOGHBL_ABSPATH . 'includes/customizer/nav-menus/class-wp-customize-nav-menus2.php';

		// require_once ABSPATH . WPINC . '/class-wp-customize-nav-menus.php';
		$wp_customize->nav_menus2 = new WP_Customize_Nav_Menus2( $wp_customize );
	}

	/**
	 * Links section.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_links_section_legacy( $wp_customize ) {
		$this->manager = $wp_customize;

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

		$wp_customize->add_section(
			'yoghbiolinks_links',
			array(
				'title'    => __( 'Links', 'yogh-bio-links' ),
				'priority' => 20,
				'panel'    => 'yoghbiolinks',
			)
		);

		include_once YOGHBL_ABSPATH . 'includes/customizer/links-legacy/class-yoghbl-customizer-control-links.php';

		$wp_customize->add_setting(
			'yoghbiolinks_links',
			array(
				'default'           => yoghbl_default_links(),
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => array( $this, 'sanitize_links' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new YoghBL_Customizer_Control_Links(
				$wp_customize,
				'yoghbiolinks_links',
				array(
					'label'    => __( 'Links', 'yogh-bio-links' ),
					'description' => "Um link por linha separando título do link por dois pontos.\n\nExemplo:\nYogh:https://www.yogh.com.br/",
					'section'  => 'yoghbiolinks_links',
					'settings' => 'yoghbiolinks_links',
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

			$wp_customize->add_setting(
				new YoghBL_Customizer_Setting_Link(
					$wp_customize,
					$link_setting_id,
					array(
						'value'     => $value,
						'transport' => 'postMessage',
					)
				)
			);

			// Create a control for each menu item.
			$wp_customize->add_control(
				new YoghBL_Customizer_Control_Link(
					$wp_customize,
					$link_setting_id,
					array(
						'label'    => $link->title,
						'section'  => 'yoghbiolinks_links',
						'priority' => 10 + $i,
					)
				)
			);
		}

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'yoghbiolinks_links',
				array(
					'selector'            => '.yoghbiolinks-links',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbiolinks_links_html',
				)
			);
		}
	}

	/**
	 * Colors section.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_colors_section( $wp_customize ) {
		$title = __( 'Colors', 'yogh-bio-links' );
		if ( 'yes' !== get_option( 'yoghbiolinks_only_color_bg', 'yes' ) ) {
			$title = __( 'Page Colors', 'yogh-bio-links' );
		}
		$wp_customize->add_section(
			'yoghbiolinks_colors',
			array(
				'title'    => $title,
				'priority' => 30,
				'panel'    => 'yoghbiolinks',
			)
		);

		// Page Background Color.
		$wp_customize->add_setting(
			'yoghbiolinks_background_color',
			array(
				'default'           => '#28303D',
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'yoghbiolinks_background_color',
				array(
					'label'       => __( 'Background color', 'yogh-bio-links' ),
					'description' => __( 'Dark or bright color combination will be used to make easy for people to read.', 'yogh-bio-links' ),
					'section'     => 'yoghbiolinks_colors',
				)
			)
		);

		if ( 'yes' !== get_option( 'yoghbiolinks_only_color_bg', 'yes' ) ) {
			// Page Text Background Color.
			$wp_customize->add_setting(
				'yoghbiolinks_color',
				array(
					'default'           => '#F0F0F0',
					'type'              => 'option',
					'capability'        => 'edit_pages',
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'yoghbiolinks_color',
					array(
						'label'   => __( 'Text color', 'yogh-bio-links' ),
						'section' => 'yoghbiolinks_colors',
					)
				)
			);
		}
	}

	/**
	 * Button colors section.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_button_colors_section( $wp_customize ) {
		$wp_customize->add_section(
			'yoghbiolinks_button_colors',
			array(
				'title'    => __( 'Button Colors', 'yogh-bio-links' ),
				'priority' => 40,
				'panel'    => 'yoghbiolinks',
			)
		);

		// Button Background Color.
		$wp_customize->add_setting(
			'yoghbiolinks_button_background_color',
			array(
				'default'           => '#F0F0F0',
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'yoghbiolinks_button_background_color',
				array(
					'label'   => __( 'Background color', 'yogh-bio-links' ),
					'section' => 'yoghbiolinks_button_colors',
				)
			)
		);

		// Button Text Background Color.
		$wp_customize->add_setting(
			'yoghbiolinks_button_color',
			array(
				'default'           => '#28303D',
				'type'              => 'option',
				'capability'        => 'edit_pages',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'yoghbiolinks_button_color',
				array(
					'label'   => __( 'Text color', 'yogh-bio-links' ),
					'section' => 'yoghbiolinks_button_colors',
				)
			)
		);
	}

	/**
	 * Footer section.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_footer_section( $wp_customize ) {
		$wp_customize->add_section(
			'yoghbiolinks_footer',
			array(
				'title'    => __( 'Footer', 'yogh-bio-links' ),
				'priority' => 50,
				'panel'    => 'yoghbiolinks',
			)
		);

		if ( 'yes' === get_option( 'yoghbiolinks_credit_text', 'no' ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin = get_plugin_data( YOGHBL_PLUGIN_FILE );

			$wp_customize->add_setting(
				'yoghbiolinks_credits_text',
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

			$wp_customize->add_control(
				'yoghbiolinks_credits_text',
				array(
					'label'    => __( 'Credits text', 'yogh-bio-links' ),
					'section'  => 'yoghbiolinks_footer',
					'settings' => 'yoghbiolinks_credits',
					'type'     => 'textarea',
				)
			);
		} else {
			$wp_customize->add_setting(
				'yoghbiolinks_credits',
				array(
					'default'           => false,
					'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
					'type'              => 'option',
					'capability'        => 'edit_pages',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				'yoghbiolinks_credits',
				array(
					'label'    => __( 'Hide credits', 'yogh-bio-links' ),
					'section'  => 'yoghbiolinks_footer',
					'settings' => 'yoghbiolinks_credits',
					'type'     => 'checkbox',
				)
			);
		}

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'yoghbiolinks_credits',
				array(
					'selector'            => '.yoghbiolinks-credits',
					'container_inclusive' => true,
					'render_callback'     => 'yoghbiolinks_credits',
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
				'post_name' => $value,
			)
		);
	}
}

global $pagenow;
if ( 'customize.php' === $pagenow || isset( $_REQUEST['customize_theme'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	new YoghBL_Customizer();
}

