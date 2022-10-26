<?php

class WP_Customize_Nav_Menu_Item_Setting2 extends WP_Customize_Setting {

	const ID_PATTERN = '/^nav_menu_item2\[(?P<id>-?\d+)\]$/';

	const POST_TYPE = 'nav_menu_item2';

	const TYPE = 'nav_menu_item2';

	public $type = self::TYPE;

	public $default = array(
		'object_id'        => 0,
		'position'         => 0, // A.K.A. menu_order.
		'title'            => '',
		'url'              => '',
	);

	public $transport = 'refresh';

	public $post_id;

	protected $value;

	public function __construct( WP_Customize_Manager $manager, $id, array $args = array() ) {
		if ( empty( $manager->nav_menus ) ) {
			throw new Exception( 'Expected WP_Customize_Manager::$nav_menus to be set.' );
		}

		if ( ! preg_match( self::ID_PATTERN, $id, $matches ) ) {
			throw new Exception( "Illegal widget setting ID: $id" );
		}

		$this->post_id = (int) $matches['id'];

		parent::__construct( $manager, $id, $args );

		// Ensure that an initially-supplied value is valid.
		if ( isset( $this->value ) ) {
			$this->populate_value();
			foreach ( array_diff( array_keys( $this->default ), array_keys( $this->value ) ) as $missing ) {
				throw new Exception( "Supplied nav_menu_item value missing property: $missing" );
			}
		}

	}

	protected function populate_value() {
		if ( ! is_array( $this->value ) ) {
			return;
		}

		if ( isset( $this->value['menu_order'] ) ) {
			$this->value['position'] = $this->value['menu_order'];
			unset( $this->value['menu_order'] );
		}

		if ( isset( $this->value['ID'] ) ) {
			$this->value['object_id'] = $this->value['ID'];
			unset( $this->value['ID'] );
		}

		foreach ( array( 'object_id' ) as $key ) {
			if ( ! is_int( $this->value[ $key ] ) ) {
				$this->value[ $key ] = (int) $this->value[ $key ];
			}
		}

		if ( ! isset( $this->value['title'] ) ) {
			$this->value['title'] = '';
		}

		// Remove remaining properties available on a setup nav_menu_item post object which aren't relevant to the setting value.
		$irrelevant_properties = array(
			'ID',
			'hash',
		);
		foreach ( $irrelevant_properties as $property ) {
			unset( $this->value[ $property ] );
		}
	}
}
