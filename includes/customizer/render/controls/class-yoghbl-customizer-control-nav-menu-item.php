<?php

class YoghBL_Customizer_Control_Nav_Menu_Item extends WP_Customize_Control {

	public $type = 'yoghbl_nav_menu_item';

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

		<div class="menu-item-settings" id="menu-item-settings-{{ data.menu_item_id }}">
			<p class="field-url description description-thin">
				<label for="edit-menu-item-url-{{ data.menu_item_id }}">
					<?php _e( 'URL' ); ?><br />
					<input class="widefat code edit-menu-item-url" type="text" id="edit-menu-item-url-{{ data.menu_item_id }}" name="menu-item-url" />
				</label>
			</p>
			<p class="description description-thin">
				<label for="edit-menu-item-title-{{ data.menu_item_id }}">
					<?php _e( 'Navigation Label' ); ?><br />
					<input type="text" id="edit-menu-item-title-{{ data.menu_item_id }}" placeholder="{{ data.title }}" class="widefat edit-menu-item-title" name="menu-item-title" />
				</label>
			</p>
		</div><!-- .menu-item-settings-->
		<ul class="menu-item-transport"></ul>
		<?php
	}

	public function json() {
		$exported = parent::json();

		$exported['menu_item_id'] = $this->setting->post_id;

		return $exported;
	}
}
