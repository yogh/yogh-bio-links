<?php

class YoghBL_Customizer_Control_Nav_Menu extends WP_Customize_Control {

	public $type = 'yoghbl_nav_menu';

	public function render_content() {}

	public function content_template() {
		?>
		<div class="customize-control-nav_menu-buttons">
			<button type="button" class="button add-new-yoghbl-menu-item" aria-label="<?php esc_attr_e( 'Add or remove menu items' ); ?>" aria-expanded="false" aria-controls="available-menu-items">
				<?php esc_html_e( 'Add Items' ); ?>
			</button>
		</div>
		<?php
	}
}
