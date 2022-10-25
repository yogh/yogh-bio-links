<?php

class YoghBL_Customizer_Setting_Link extends WP_Customize_Setting {

	const ID_PATTERN = '/^yoghbiolinks_link\[(?P<ID>[\d+]+)\]$/';

	public $type = 'yoghbiolinks_link';

	public $default = array(
		'link_id'  => '',
		'position' => 0, // A.K.A. order.
		'title'    => '',
		'url'      => '',
	);

	public $transport = 'refresh';

	public $link_id;

	protected $value;

	public function __construct( WP_Customize_Manager $manager, $id, array $args = array() ) {
		if ( ! preg_match( self::ID_PATTERN, $id, $matches ) ) {
			throw new Exception( "Illegal widget setting ID: $id" );
		}

		$this->link_id = (int) $matches['ID'];

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

		if ( isset( $this->value['order'] ) ) {
			$this->value['position'] = $this->value['order'];
			unset( $this->value['order'] );
		}

		if ( isset( $this->value['ID'] ) ) {
			$this->value['link_id'] = $this->value['ID'];
			unset( $this->value['ID'] );
		}

		if ( ! isset( $this->value['title'] ) ) {
			$this->value['title'] = '';
		}

		$irrelevant_properties = array(
			'ID',
			'hash',
		);
		foreach ( $irrelevant_properties as $property ) {
			unset( $this->value[ $property ] );
		}
	}
}
