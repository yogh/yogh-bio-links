<?php

class YoghBL_Customizer_Setting_Nav_Menu_Item extends WP_Customize_Setting {

	const ID_PATTERN = '/^yoghbl_nav_menu_item\[(?P<hash>-?[\da-z]+)\]$/';

	const TYPE = 'yoghbl_nav_menu_item';

	public $type = self::TYPE;

	public $default = array(
		'hash'     => '',
		'position' => 0, // A.K.A. menu_order.
		'title'    => '',
		'url'      => '',
	);

	public $transport = 'refresh';

	public $hash;

	protected $value;

	/**
	 * Whether or not update() was called.
	 *
	 * @since 4.3.0
	 * @var bool
	 */
	protected $is_updated = false;

	/**
	 * Status for calling the update method, used in customize_save_response filter.
	 *
	 * See {@see 'customize_save_response'}.
	 *
	 * When status is inserted, the placeholder post ID is stored in $previous_post_id.
	 * When status is error, the error is stored in $update_error.
	 *
	 * @since 4.3.0
	 * @var string updated|inserted|deleted|error
	 *
	 * @see WP_Customize_Nav_Menu_Item_Setting::update()
	 * @see WP_Customize_Nav_Menu_Item_Setting::amend_customize_save_response()
	 */
	public $update_status;

	/**
	 * Any error object returned by wp_update_nav_menu_item() when setting is updated.
	 *
	 * @since 4.3.0
	 * @var WP_Error
	 *
	 * @see WP_Customize_Nav_Menu_Item_Setting::update()
	 * @see WP_Customize_Nav_Menu_Item_Setting::amend_customize_save_response()
	 */
	public $update_error;

	public function __construct( WP_Customize_Manager $manager, $id, array $args = array() ) {
		if ( ! preg_match( self::ID_PATTERN, $id, $matches ) ) {
			throw new Exception( "Illegal widget setting ID: $id" );
		}

		$this->hash = $matches['hash'];

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

		if ( ! isset( $this->value['position'] ) ) {
			$this->value['position'] = 0;
		}
	}

	/**
	 * Creates/updates the nav_menu_item post for this setting.
	 *
	 * Any created menu items will have their assigned post IDs exported to the client
	 * via the {@see 'customize_save_response'} filter. Likewise, any errors will be
	 * exported to the client via the customize_save_response() filter.
	 *
	 * To delete a menu, the client can send false as the value.
	 *
	 * @since 4.3.0
	 *
	 * @see wp_update_nav_menu_item()
	 *
	 * @param array|false $value The menu item array to update. If false, then the menu item will be deleted
	 *                           entirely. See WP_Customize_Nav_Menu_Item_Setting::$default for what the value
	 *                           should consist of.
	 * @return null|void
	 */
	protected function update( $value ) {
		if ( $this->is_updated ) {
			return;
		}

		$this->is_updated = true;
		$is_placeholder   = ( is_integer( $this->hash ) && intval( $this->hash ) < 0 );
		$is_delete        = ( false === $value );

		// Update the cached value.
		$this->value = $value;

		add_filter( 'customize_save_response', array( $this, 'amend_customize_save_response' ) );

		if ( $is_delete ) {
			// If the current setting post is a placeholder, a delete request is a no-op.
			if ( $is_placeholder ) {
				$this->update_status = 'deleted';
			} else {
				$r = yoghbl_links_delete_item( $this->hash );

				if ( false === $r ) {
					$this->update_error  = new WP_Error( 'delete_failure' );
					$this->update_status = 'error';
				} else {
					$this->update_status = 'deleted';
				}
				// @todo send back the IDs for all associated nav menu items deleted, so these settings (and controls) can be removed from Customizer?
			}
		} else {

			// Insert or update menu.
			$item_data = array(
				'title' => $value['title'],
				'url'   => $value['url'],
			);

			$r = yoghbl_links_update_item(
				wp_slash( $item_data )
			);

			if ( is_wp_error( $r ) ) {
				$this->update_status = 'error';
				$this->update_error  = $r;
			} else {
				if ( $is_placeholder ) {
					$this->update_status    = 'inserted';
				} else {
					$this->update_status = 'updated';
				}
			}
		}

	}

	/**
	 * Export data for the JS client.
	 *
	 * @since 4.3.0
	 *
	 * @see WP_Customize_Nav_Menu_Item_Setting::update()
	 *
	 * @param array $data Additional information passed back to the 'saved' event on `wp.customize`.
	 * @return array Save response data.
	 */
	public function amend_customize_save_response( $data ) {
		if ( ! isset( $data['nav_menu_item_updates'] ) ) {
			$data['nav_menu_item_updates'] = array();
		}

		$data['nav_menu_item_updates'][] = array(
			'error'  => $this->update_error ? $this->update_error->get_error_code() : null,
			'status' => $this->update_status,
		);
		return $data;
	}
}
