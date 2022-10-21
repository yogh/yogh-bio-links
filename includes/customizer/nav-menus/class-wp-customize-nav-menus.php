<?php

final class WP_Customize_Nav_Menus {

	public $manager;

	public function __construct( $manager ) {
		$this->manager = $manager;

		add_action( 'customize_register', array( $this, 'customize_register' ), 11 );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_templates' ) );
	}

	public function enqueue_scripts() {
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;

		wp_enqueue_style( 'customize-nav-menus' );

		wp_dequeue_script( 'customize-nav-menus' );
		wp_deregister_script( 'customize-nav-menus' );

		wp_enqueue_script(
			'customize-nav-menus',
			YoghBL()->plugin_url() . '/assets/js/customize-nav-menus.js',
			array( 'jquery', 'wp-backbone', 'customize-controls', 'accordion', 'nav-menu', 'wp-sanitize' ),
			$version,
			true
		);

		$temp_nav_menu_setting = new WP_Customize_Nav_Menu_Setting( $this->manager, 'nav_menu[-1]' );

		// Pass data to JS.
		$settings = array(
			'settingTransport'         => 'postMessage',
			'phpIntMax'                => PHP_INT_MAX,
			'defaultSettingValues'     => array(
				'nav_menu_item' => $temp_nav_menu_setting->default,
			),
		);

		$data = sprintf( 'var _wpCustomizeNavMenusSettings = %s;', wp_json_encode( $settings ) );
		wp_scripts()->add_data( 'customize-nav-menus', 'data', $data );
	}

	public function print_templates() {
		?>
		<script type="text/html" id="tmpl-menu-item-reorder-nav">
			<div class="menu-item-reorder-nav">
				<?php
				printf(
					'<button type="button" class="menus-move-up">%1$s</button><button type="button" class="menus-move-down">%2$s</button><button type="button" class="menus-move-left">%3$s</button><button type="button" class="menus-move-right">%4$s</button>',
					__( 'Move up' ),
					__( 'Move down' ),
					__( 'Move one level up' ),
					__( 'Move one level down' )
				);
				?>
			</div>
		</script>
		<?php
	}

	public function customize_register() {
		require_once YOGHBL_ABSPATH . 'includes/customizer/nav-menus/controls/class-wp-customize-nav-menu-name-control.php';
		$this->manager->register_control_type( 'WP_Customize_Nav_Menu_Name_Control' );
		require_once YOGHBL_ABSPATH . 'includes/customizer/nav-menus/controls/class-wp-customize-nav-menu-item-control.php';
		$this->manager->register_control_type( 'WP_Customize_Nav_Menu_Item_Control' );

		$menus = wp_get_nav_menus();

		$locations = get_registered_nav_menus();

		$choices = array( '0' => __( '&mdash; Select &mdash;' ) );
		foreach ( $menus as $menu ) {
			$choices[ $menu->term_id ] = wp_html_excerpt( $menu->name, 40, '&hellip;' );
		}

		foreach ( $locations as $location => $description ) {
			$setting_id = "nav_menu_locations[{$location}]";

			$this->manager->add_control(
				new WP_Customize_Nav_Menu_Location_Control(
					$this->manager,
					$setting_id,
					array(
						'label'       => $description,
						'location_id' => $location,
						'section'     => 'menu_locations',
						'choices'     => $choices,
					)
				)
			);
		}

		if ( ! function_exists( 'get_post_states' ) ) {
			require_once ABSPATH . 'wp-admin/includes/template.php';
		}

		foreach ( $menus as $menu ) {
			$menu_id = $menu->term_id;

			$section_id = 'yoghbiolinks_links';
			$this->manager->add_section(
				new WP_Customize_Nav_Menu_Section(
					$this->manager,
					$section_id,
					array(
						'title'    => __( 'Links' ),
						'priority' => 10,
						'panel'    => 'yoghbiolinks',
					)
				)
			);

			$nav_menu_setting_id = 'nav_menu[' . $menu_id . ']';
			$this->manager->add_setting(
				new WP_Customize_Nav_Menu_Setting(
					$this->manager,
					$nav_menu_setting_id,
					array(
						'transport' => 'postMessage',
					)
				)
			);

			// $menu_items = (array) yoghbl_links();
			$menu_items = (array) wp_get_nav_menu_items( 5 );

			if ( $menu_items ) {
				require_once YOGHBL_ABSPATH . 'includes/customizer/nav-menus/settings/class-wp-customize-nav-menu-item-setting.php';
			}
			foreach ( array_values( $menu_items ) as $i => $item ) {

				$menu_item_setting_id = 'nav_menu_item[' . $item->ID . ']';

				$value = (array) $item;

				$value['nav_menu_term_id'] = $menu_id;
				$this->manager->add_setting(
					new WP_Customize_Nav_Menu_Item_Setting(
						$this->manager,
						$menu_item_setting_id,
						array(
							'value'     => $value,
							'transport' => 'postMessage',
						)
					)
				);

				$this->manager->add_control(
					new WP_Customize_Nav_Menu_Item_Control(
						$this->manager,
						$menu_item_setting_id,
						array(
							'label'    => $item->title,
							'section'  => $section_id,
							'priority' => 10 + $i,
						)
					)
				);
			}
		}
	}
}
