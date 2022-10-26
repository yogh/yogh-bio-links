<?php

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

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'available_items_template' ) );
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
				'itemAdded'              => __( 'Menu item added' ),
			),
			'settingTransport'         => 'postMessage',
			'phpIntMax'                => PHP_INT_MAX,
			'defaultSettingValues'     => array(
				'yoghbl_nav_menu_item' => $temp_nav_menu_item_setting->default,
			),
		);

		$data = sprintf( 'var _yoghblCustomizerSettingsNavMenus = %s;', wp_json_encode( $settings ) );
		wp_scripts()->add_data( 'customize-render-nav-menus', 'data', $data );
	}

	public function customize_register() {
		$this->manager->register_control_type( 'YoghBL_Customizer_Control_Nav_Menu' );
		$this->manager->register_control_type( 'YoghBL_Customizer_Control_Nav_Menu_Item' );

		$this->manager->add_section(
			'yoghbiolinks_links',
			array(
				'title'    => __( 'Links', 'yogh-bio-links' ),
				'priority' => 20,
				'panel'    => 'yoghbiolinks',
			)
		);

		$this->manager->add_section(
			new YoghBL_Customizer_Section_Nav_Menu(
				$this->manager,
				'yoghbiolinks_links',
				array(
					'title'    => __( 'Links', 'yogh-bio-links' ),
					'priority' => 10,
					'panel'    => 'yoghbiolinks',
				)
			)
		);

		$this->manager->add_setting(
			new YoghBL_Customizer_Setting_Nav_Menu(
				$this->manager,
				'yoghbiolinks_links',
				array(
					'transport' => 'postMessage',
				)
			)
		);

		$links = (array) yoghbl_links();
		foreach ( array_values( $links ) as $i => $link ) {

			$link_setting_id = 'yoghbl_nav_menu_item[' . $link->ID . ']';

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
						'section'  => 'yoghbiolinks_links',
						'priority' => 10 + $i,
					)
				)
			);

		}
	}

	public function available_items_template() {
		?>
		<div id="available-yoghbl-menu-items" class="accordion-container">
			<div class="customize-section-title">
				<button type="button" class="customize-section-back" tabindex="-1">
					<span class="screen-reader-text"><?php _e( 'Back' ); ?></span>
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

	protected function print_custom_links_available_menu_item() {
		?>
		<div id="new-custom-menu-item" class="accordion-section open">
			<div class="accordion-section-content customlinkdiv">
				<p id="menu-item-url-wrap" class="wp-clearfix">
					<label class="howto" for="custom-yoghbl-menu-item-url"><?php _e( 'URL' ); ?></label>
					<input id="custom-yoghbl-menu-item-url" name="yobhbl-menu-item[-1][url]" type="text" class="code menu-item-textbox" placeholder="https://">
				</p>
				<p id="menu-item-name-wrap" class="wp-clearfix">
					<label class="howto" for="custom-yoghbl-menu-item-name"><?php _e( 'Link Text' ); ?></label>
					<input id="custom-yoghbl-menu-item-name" name="yobhbl-menu-item[-1][title]" type="text" class="regular-text menu-item-textbox">
				</p>
				<p class="button-controls">
					<span class="add-to-menu">
						<input type="submit" class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add' ); ?>" name="add-custom-menu-item" id="custom-yoghbl-menu-item-submit">
						<span class="spinner"></span>
					</span>
				</p>
			</div>
		</div>
		<?php
	}
}
