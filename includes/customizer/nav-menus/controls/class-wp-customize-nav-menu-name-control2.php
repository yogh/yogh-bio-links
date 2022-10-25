<?php

class WP_Customize_Nav_Menu_Name_Control2 extends WP_Customize_Control {

	public $type = 'nav_menu_name';

	protected function render_content() {}

	protected function content_template() {
		?>
		<label><span class="customize-control-title"><?php esc_html_e( 'Links', 'yoghbiolinks' ); ?></span></label>
		<div class="customlinkdiv">
			<input type="hidden" value="custom" id="custom-menu-item-type" name="menu-item[-1][menu-item-type]" />
			<p id="menu-item-url-wrap" class="wp-clearfix">
				<label class="howto" for="custom-menu-item-url"><?php _e( 'URL' ); ?></label>
				<input id="custom-menu-item-url" name="menu-item[-1][menu-item-url]" type="text" class="code menu-item-textbox" placeholder="https://">
			</p>
			<p id="menu-item-name-wrap" class="wp-clearfix">
				<label class="howto" for="custom-menu-item-name"><?php _e( 'Link Text' ); ?></label>
				<input id="custom-menu-item-name" name="menu-item[-1][menu-item-title]" type="text" class="regular-text menu-item-textbox">
			</p>
			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit" class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-custom-menu-item" id="custom-menu-item-submit">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}
}
