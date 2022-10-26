<?php

class YoghBL_Customizer_Setting_Nav_Menu_Item extends WP_Customize_Setting {

	const ID_PATTERN = '/^yoghbl_nav_menu_item\[(?P<id>-?\d+)\]$/';

	public $type = 'yoghbl_nav_menu_item';

	public $default = array(
		'hash'  => '',
		'title' => '',
		'url'   => '',
	);

	public $transport = 'refresh';

	public $post_id;

	protected $value;

	public function __construct( WP_Customize_Manager $manager, $id, array $args = array() ) {
		if ( ! preg_match( self::ID_PATTERN, $id, $matches ) ) {
			throw new Exception( "Illegal widget setting ID: $id" );
		}

		$this->post_id = (int) $matches['id'];

		parent::__construct( $manager, $id, $args );

		if ( isset( $this->value ) ) {
			$this->populate_value();
			foreach ( array_diff( array_keys( $this->default ), array_keys( $this->value ) ) as $missing ) {
				throw new Exception( "Supplied link value missing property: $missing" );
			}
		}
	}
	public function value() {
		if ( isset( $this->value ) ) {
			$value = $this->value;
		} else {
			$value = false;
		}

		return $value;
	}

	protected function populate_value() {
		if ( ! is_array( $this->value ) ) {
			return;
		}

		$irrelevant_properties = array(
			'ID',
			'order',
		);
		foreach ( $irrelevant_properties as $property ) {
			unset( $this->value[ $property ] );
		}
	}
}
