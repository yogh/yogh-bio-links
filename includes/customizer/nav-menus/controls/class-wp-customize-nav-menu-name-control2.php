<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class yoghbl_customize_Nav_Menu_Name_Control2 extends yoghbl_customize_Control {

	public $type = 'nav_menu_name2';

	protected function render_content() {}

	protected function content_template() {
		?>
		<label><span class="customize-control-title"><?php esc_html_e( 'Links', 'yogh-bio-links' ); ?></span></label>
		<div class="customlinkdiv">
			<input type="hidden" value="custom" id="custom-menu-item-type" name="menu-item[-1][menu-item-type]" />
			<p id="menu-item-url-wrap" class="wp-clearfix">
				<label class="howto" for="custom-menu-item-url"><?php _e( 'URL', 'yogh-bio-links' ); ?></label>
				<input id="custom-menu-item-url" name="menu-item[-1][menu-item-url]" type="text" class="code menu-item-textbox" placeholder="https://">
			</p>
			<p id="menu-item-name-wrap" class="wp-clearfix">
				<label class="howto" for="custom-menu-item-name"><?php _e( 'Link Text', 'yogh-bio-links' ); ?></label>
				<input id="custom-menu-item-name" name="menu-item[-1][menu-item-title]" type="text" class="regular-text menu-item-textbox">
			</p>
			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit" class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'yogh-bio-links' ); ?>" name="add-custom-menu-item" id="custom-menu-item-submit">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}
}
