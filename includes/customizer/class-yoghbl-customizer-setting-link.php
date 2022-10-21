<?php
/**
 * Custom setting for link.
 *
 * @version 1.0.0
 * @package YoghBioLinks
 */

defined( 'ABSPATH' ) || exit;

/**
 * YoghBL_Customizer_Setting_Link class.
 */
class YoghBL_Customizer_Setting_Link extends WP_Customize_Setting {

	const ID_PATTERN = '/^yoghbl_link\[(?P<hash>[\da-z]+)\]$/';

	/**
	 * Declare the setting type.
	 *
	 * @var string
	 */
	public $type = 'yoghbiolinks-link';

	/**
	 * Default setting value.
	 *
	 * @var array
	 *
	 * @see yoghbl_link()
	 */
	public $default = array(
		'hash'             => '',
		'position'         => 0, // A.K.A. menu_order.
		'title'            => '',
		'url'              => '',
	);

	/**
	 * Default transport.
	 *
	 * @var string
	 */
	public $transport = 'refresh';

	/**
	 * The link hash.
	 *
	 * @var string
	 */
	public $hash;

	/**
	 * Storage of pre-setup menu item.
	 *
	 * @var array|null
	 */
	protected $value;

	/**
	 * Constructor.
	 *
	 * Any supplied $args override class property defaults.
	 *
	 * @throws Exception If $id is not valid for this setting type.
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      A specific ID of the setting.
	 *                                      Can be a theme mod or option name.
	 * @param array                $args    Optional. Setting arguments.
	 */
	public function __construct( WP_Customize_Manager $manager, $id, array $args = array() ) {
		if ( ! preg_match( self::ID_PATTERN, $id, $matches ) ) {
			throw new Exception( "Illegal widget setting ID: $id" );
		}

		$this->hash = (int) $matches['hash'];

		parent::__construct( $manager, $id, $args );

		// Ensure that an initially-supplied value is valid.
		if ( isset( $this->value ) ) {
			$this->populate_value();
			foreach ( array_diff( array_keys( $this->default ), array_keys( $this->value ) ) as $missing ) {
				throw new Exception( "Supplied link value missing property: $missing" );
			}
		}
	}

	/**
	 * Ensure that the value is fully populated with the necessary properties.
	 *
	 * Translates some properties added by yoghbl_link() and removes others.
	 */
	protected function populate_value() {
		if ( ! is_array( $this->value ) ) {
			return;
		}

		if ( isset( $this->value['order'] ) ) {
			$this->value['position'] = $this->value['order'];
			unset( $this->value['order'] );
		}

		if ( ! isset( $this->value['title'] ) ) {
			$this->value['title'] = '';
		}
	}
}
