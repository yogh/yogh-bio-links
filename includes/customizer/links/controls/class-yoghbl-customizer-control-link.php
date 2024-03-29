<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class YoghBL_Customizer_Control_Link extends yoghbl_customize_Control {

	public $type = 'yoghbl_link';

	public $setting;

	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );
	}

	public function render_content() {}

	public function content_template() {
		?>
		<div class="menu-item-bar">
			<div class="menu-item-handle">
				<span class="item-title" aria-hidden="true">
					<span class="spinner"></span>
					<span class="menu-item-title<# if ( ! data.title ) { #> no-title<# } #>">{{ data.title || wp.customize.Menus.data.l10n.untitled }}</span>
				</span>
				<span class="item-controls">
					<button type="button" class="button-link item-edit" aria-expanded="false"><span class="screen-reader-text">
					<?php
						/* translators: 1: Title of a menu item. */
						printf( __( 'Edit menu item: %s', 'yogh-bio-links' ), '{{ data.title || wp.customize.Menus.data.l10n.untitled }}' );
					?>
					</span><span class="toggle-indicator" aria-hidden="true"></span></button>
					<button type="button" class="button-link item-delete submitdelete deletion"><span class="screen-reader-text">
					<?php
						/* translators: 1: Title of a menu item. */
						printf( __( 'Remove Menu Item: %s', 'yogh-bio-links' ), '{{ data.title || wp.customize.Menus.data.l10n.untitled }}' );
					?>
					</span></button>
				</span>
			</div>
		</div>

		<div class="menu-item-settings" id="menu-item-settings-{{ data.link_id }}">
			<p class="field-url description description-thin">
				<label for="edit-menu-item-url-{{ data.link_id }}">
					<?php _e( 'URL' ); ?><br />
					<input class="widefat code edit-menu-item-url" type="text" id="edit-menu-item-url-{{ data.link_id }}" name="menu-item-url" />
				</label>
			</p>
			<p class="description description-thin">
				<label for="edit-menu-item-title-{{ data.link_id }}">
					<?php _e( 'Navigation Label' ); ?><br />
					<input type="text" id="edit-menu-item-title-{{ data.link_id }}" placeholder="{{ data.title }}" class="widefat edit-menu-item-title" name="menu-item-title" />
				</label>
			</p>
		</div><!-- .menu-item-settings-->
		<ul class="menu-item-transport"></ul>
		<?php
	}

	/**
	 * Return parameters for this control.
	 *
	 * @return array Exported parameters.
	 */
	public function json() {
		$exported              = parent::json();
		$exported['link_id'] = intval($this->setting->link_id);

		return $exported;
	}
}
