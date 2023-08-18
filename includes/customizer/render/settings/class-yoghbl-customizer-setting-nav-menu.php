<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class YoghBL_Customizer_Setting_Nav_Menu extends yoghbl_customize_Setting {

	const ID_PATTERN = '/^yoghbl_links$/';

	const TYPE = 'yoghbl_links';

	public $type = self::TYPE;

	/**
	 * Default transport.
	 *
	 * @since 4.3.0
	 * @var string
	 */
	public $transport = 'postMessage';
}
