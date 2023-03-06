<?php
/**
 * YoghBL Admin
 *
 * @class    YoghBL_Admin
 * @package  YoghBL\Admin
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * YoghBL_Admin class.
 */
class YoghBL_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );

		// Add a post display state for page.
		add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );

		// Change edit link and remove quick edit.
		add_filter( 'page_row_actions', array( $this, 'post_row_actions' ), 16, 2 );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once __DIR__ . '/yoghbl-admin-functions.php';
	}

	/**
	 * Add a post display state for special YoghBL page in the page list table.
	 *
	 * @param array   $post_states An array of post display states.
	 * @param WP_Post $post        The current post object.
	 */
	public function add_display_post_states( $post_states, $post ) {
		if ( yoghbl_get_page_id( 'biolinks' ) === $post->ID ) {
			$post_states['yoghbl_page_for_biolinks'] = __( 'Yogh Bio Links Page', 'yogh-bio-links' );
		}

		return $post_states;
	}

	/**
	 * Change edit link and remove quick edit in admin products list.
	 *
	 * @param array   $actions Array of actions.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public function post_row_actions( $actions, $post ) {
		if ( yoghbl_get_page_id( 'biolinks' ) === $post->ID ) {
			if ( isset( $actions['inline hide-if-no-js'] ) ) {
				unset( $actions['inline hide-if-no-js'] );
			}

			if ( isset( $actions[0] ) && isset( $actions[1] ) ) {
				$maybe_edit = array(
					$actions[0],
					$actions[1],
				);

				$only_edit = true;
				foreach ( $maybe_edit as $i => $action ) {
					if ( __( 'Edit' ) === preg_replace( '/\s+\([^)]+\)/', '', wp_strip_all_tags( $action ) ) ) {
						unset( $actions[ $i ] );
					} else {
						$only_edit = false;
					}
				}
				if ( $only_edit ) {
					$actions = array_merge(
						array( 'edit' => '' ),
						$actions
					);
				}
			}
			if ( isset( $actions['edit'] ) ) {
				$actions['edit'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					esc_url( yoghbl_edit_link() ),
					/* translators: %s: Post title. */
					esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $post->post_title ) ),
					esc_html__( 'Edit' )
				);
			};
		}

		return $actions;
	}
}

return new YoghBL_Admin();
