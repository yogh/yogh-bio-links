<?php
/**
 * Setup menus in WP admin.
 *
 * @package YoghBioLinks\Admin
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'YoghBL_Admin_Bar', false ) ) {
	return new YoghBL_Admin_Bar();
}

/**
 * YoghBL_Admin_Bar Class.
 */
class YoghBL_Admin_Bar {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Admin bar menus.
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menus' ), 81 );
	}

	/**
	 * Change the "Edit Page" link in admin bar main menu.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public function admin_bar_menus( $wp_admin_bar ) {
		global $wp_the_query;

		if ( ! is_admin_bar_showing() ) {
			return;
		}

		$current_object = $wp_the_query->get_queried_object();

		if ( empty( $current_object ) ) {
			return;
		}

		if ( ! empty( $current_object->post_type ) ) {
			if ( yoghbl_get_page_id( 'biolinks' ) !== $current_object->ID ) {
				return;
			}

			$post_type_object = get_post_type_object( $current_object->post_type );
			$edit_post_link   = yoghbl_edit_link();
			if ( $post_type_object
				&& $edit_post_link
				&& current_user_can( 'edit_post', $current_object->ID )
				&& $post_type_object->show_in_admin_bar ) {
				$wp_admin_bar->remove_node( 'edit' );
				$wp_admin_bar->add_node(
					array(
						'id'    => 'edit',
						'title' => $post_type_object->labels->edit_item,
						'href'  => $edit_post_link,
					)
				);
			}
		}
	}
}

return new YoghBL_Admin_Bar();
