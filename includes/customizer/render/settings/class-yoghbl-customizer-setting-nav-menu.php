<?php

class YoghBL_Customizer_Setting_Nav_Menu extends WP_Customize_Setting {

	const ID_PATTERN = '/^yoghbiolinks_links$/';

	const TYPE = 'yoghbiolinks_links';

	public $type = self::TYPE;

	/**
	 * Default transport.
	 *
	 * @since 4.3.0
	 * @var string
	 */
	public $transport = 'postMessage';
}
