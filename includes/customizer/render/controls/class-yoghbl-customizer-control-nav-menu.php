<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class YoghBL_Customizer_Control_Nav_Menu extends yoghbl_customize_Control {

	public $type = 'yoghbl_nav_menu';

	public function render_content() {}

	public function content_template() {
		?>
		<div class="customize-control-nav_menu-buttons">
			<button type="button" class="button add-new-yoghbl-menu-item" aria-label="<?php esc_attr_e( 'Add or remove menu items', 'yogh-bio-links' ); ?>" aria-expanded="false" aria-controls="available-menu-items">
				<?php esc_html_e( 'Add Items', 'yogh-bio-links' ); ?>
			</button>
			<button type="button" class="button-link reorder-toggle" aria-label="<?php esc_attr_e( 'Reorder menu items', 'yogh-bio-links' ); ?>" aria-describedby="reorder-items-desc-{{ data.menu_id }}">
				<span class="reorder"><?php _e( 'Reorder', 'yogh-bio-links' ); ?></span>
				<span class="reorder-done"><?php _e( 'Done', 'yogh-bio-links' ); ?></span>
			</button>
		</div>
		<div class="note-publish-refresh">
			<p><?php echo esc_html_e( 'Click Publish and refresh the page to see the links changes', 'yogh-bio-links' ); ?></p>
		</div>
		<?php
	}
}
