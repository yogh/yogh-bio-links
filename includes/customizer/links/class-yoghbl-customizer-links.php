<?php

final class YoghBL_Customizer_Links {

	public $manager;

	public function __construct( $manager ) {
		$this->manager = $manager;

		add_action( 'customize_register', array( $this, 'customize_register' ), 11 );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;

		wp_enqueue_style( 'customize-nav-menus' );

		wp_enqueue_script(
			'yoghbiolink-customize-links',
			YoghBL()->plugin_url() . '/assets/js/customize-links.js',
			array( 'jquery' ),
			$version,
			true
		);
	}

	public function customize_register() {
		require_once YOGHBL_ABSPATH . 'includes/customizer/links/sections/class-yoghbl-customizer-section-links.php';
		require_once YOGHBL_ABSPATH . 'includes/customizer/links/settings/class-yoghbl-customizer-setting-links.php';
		require_once YOGHBL_ABSPATH . 'includes/customizer/links/settings/class-yoghbl-customizer-setting-link.php';
		require_once YOGHBL_ABSPATH . 'includes/customizer/links/controls/class-yoghbl-customizer-control-links.php';
		require_once YOGHBL_ABSPATH . 'includes/customizer/links/controls/class-yoghbl-customizer-control-link.php';

		$this->manager->register_control_type( 'YoghBL_Customizer_Control_Links' );
		$this->manager->register_control_type( 'YoghBL_Customizer_Control_Link' );

		$section_id = 'yoghbiolinks_links';
		$this->manager->add_section(
			new YoghBL_Customizer_Section_Links(
				$this->manager,
				$section_id,
				array(
					'title'    => __( 'Links' ),
					'priority' => 10,
					'panel'    => 'yoghbiolinks',
				)
			)
		);

		$this->manager->add_setting(
			new YoghBL_Customizer_Setting_Links(
				$this->manager,
				'yoghbiolink_links',
				array(
					'transport' => 'postMessage',
				)
			)
		);

		$links = (array) yoghbl_links();
		foreach ( array_values( $links ) as $i => $link ) {

			$link_setting_id = 'yoghbiolinks_link[' . $link->ID . ']';

			$value = (array) $link;

			$this->manager->add_setting(
				new YoghBL_Customizer_Setting_Link(
					$this->manager,
					$link_setting_id,
					array(
						'value'     => $value,
						'transport' => 'postMessage',
					)
				)
			);

			$this->manager->add_control(
				new YoghBL_Customizer_Control_Link(
					$this->manager,
					$link_setting_id,
					array(
						'label'    => $link->title,
						'section'  => $section_id,
						'priority' => 10 + $i,
					)
				)
			);

		}
	}
}
