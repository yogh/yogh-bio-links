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
			<button type="button" class="button-link reorder-toggle" aria-label="<?php esc_attr_e( 'Reorder menu items' ); ?>" aria-describedby="reorder-items-desc-{{ data.menu_id }}">
				<span class="reorder"><?php _e( 'Reorder' ); ?></span>
				<span class="reorder-done"><?php _e( 'Done' ); ?></span>
			</button>
		</div>
		<div class="note-publish-refresh">
			<p><?php echo esc_html_e( 'Click Publish and refresh the page to see the links changes', 'yogh-bio-links' ); ?></p>
		</div>
		<?php
	}
}
