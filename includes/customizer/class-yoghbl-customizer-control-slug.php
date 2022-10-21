<?php
/**
 * Custom control for with text input with preview link.
 *
 * Used for our image cropping settings.
 *
 * @version 1.0.0
 * @package YoghBioLinks
 */

defined( 'ABSPATH' ) || exit;

/**
 * YoghBL_Customizer_Control_Cropping class.
 */
class YoghBL_Customizer_Control_Slug extends WP_Customize_Control {

	/**
	 * Declare the control type.
	 *
	 * @var string
	 */
	public $type = 'yoghbiolinks-slug-control';

	/**
	 * Constructor.
	 *
	 * Any supplied $args override class property defaults.
	 *
	 * @throws Exception If $id is not valid for this setting type.
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      A specific ID of the setting.
	 *                                      Can be a theme mod or option name.
	 * @param array                $args    Optional. Setting arguments.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );
		add_action( 'customize_controls_print_styles', array( $this, 'add_styles' ) );
		add_action( 'customize_controls_print_scripts', array( $this, 'add_scripts' ), 30 );
	}

	/**
	 * CSS styles to improve our form.
	 */
	public function add_styles() {
		?>

		<style type="text/css">
			.customize-control .yoghbiolinks-slug-control input[type=text] {
				margin-bottom: 8px;
			}
			.edit-post-post-link__link-post-name {
				font-weight: 600;
			}
			.css-rvs7bx {
				width: 1em;
				height: 1em;
				margin: 0px;
				vertical-align: middle;
				fill: currentcolor;
			}
		</style>

		<?php
	}

	/**
	 * Scripts to improve our form.
	 */
	public function add_scripts() {
		?>

		<script type="text/javascript">
			jQuery( function( $ ) {
				$( document.body ).on( 'input', '.yoghbiolinks-slug-control input[type="text"]', function() {
					var $wrapper = $( this ).closest( '.yoghbiolinks-slug-control' ),
						$postName = $wrapper.find( '.edit-post-post-link__link-post-name' ),
						value = $wrapper.find( 'input' ).val();

					if ( '' === value ) {
						value = $postName.data( 'default' );
					}

					$postName.text( value );

					return false;
				} );

				$( document.body ).on( 'blur', '.yoghbiolinks-slug-control input[type="text"]', function() {
					var $wrapper = $( this ).closest( '.yoghbiolinks-slug-control' ),
						$postName = $wrapper.find( '.edit-post-post-link__link-post-name' ),
						$input = $wrapper.find( 'input' ),
						value = $input.val();

					if ( '' !== value ) {
						value = wp.url.cleanForSlug( value );
						$input.val( value );
						$postName.text( value );
					}

					return false;
				} );
			} );
		</script>

		<?php
	}

	/**
	 * Render control.
	 */
	public function render_content() {
		$default = $this->value();

		$slug = $default;
		if ( isset( $this->input_attrs['value'] ) ) {
			$slug = $this->input_attrs['value'];
		}
		$input_id         = '_customize-input-' . $this->id;
		$description_id   = '_customize-description-' . $this->id;
		$describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';
		?>

		<div class="yoghbiolinks-slug-control">

			<?php if ( ! empty( $this->label ) ) : ?>
				<label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
			<?php endif; ?>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span id="<?php echo esc_attr( $description_id ); ?>" class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
			<input
				id="<?php echo esc_attr( $input_id ); ?>"
				type="<?php echo esc_attr( $this->type ); ?>"
				<?php echo $describedby_attr; ?>
				<?php $this->input_attrs(); ?>
				<?php if ( ! isset( $this->input_attrs['value'] ) ) : ?>
					value="<?php echo esc_attr( $this->value() ); ?>"
				<?php endif; ?>
				<?php $this->link(); ?>
				/>

			<div class="edit-post-post-link__preview-link-container">
				<a target="_blank" class="components-external-link edit-post-post-link__link" href="<?php echo home_url( "/{$slug}" ); ?>" rel="external noreferrer noopener">
					<span class="edit-post-post-link__link-prefix"><?php echo home_url( '/' ); ?></span><span class="edit-post-post-link__link-post-name" data-default="<?php echo esc_attr( $default ); ?>"><?php echo esc_html( $slug ); ?></span><span class="edit-post-post-link__link-suffix">/</span><span data-wp-c16t="true" data-wp-component="VisuallyHidden" class="components-visually-hidden css-0 em57xhy0" style="border: 0px; clip: rect(1px, 1px, 1px, 1px); clip-path: inset(50%); height: 1px; margin: -1px; overflow: hidden; padding: 0px; position: absolute; width: 1px; overflow-wrap: normal;"><?php esc_html_e( '(opens in a new tab)', 'yogh-bio-links' ); ?></span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="components-external-link__icon css-rvs7bx etxm6pv0" aria-hidden="true" focusable="false"><path d="M18.2 17c0 .7-.6 1.2-1.2 1.2H7c-.7 0-1.2-.6-1.2-1.2V7c0-.7.6-1.2 1.2-1.2h3.2V4.2H7C5.5 4.2 4.2 5.5 4.2 7v10c0 1.5 1.2 2.8 2.8 2.8h10c1.5 0 2.8-1.2 2.8-2.8v-3.6h-1.5V17zM14.9 3v1.5h3.7l-6.4 6.4 1.1 1.1 6.4-6.4v3.7h1.5V3h-6.3z"></path></svg>
				</a>
			</div>

		</div>

		<?php
	}
}
