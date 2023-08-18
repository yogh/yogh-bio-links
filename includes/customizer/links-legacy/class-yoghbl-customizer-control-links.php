<?php
/**
 * Custom control for links.
 *
 * Used for our image cropping settings.
 *
 * @version 1.0.0
 * @package YoghBioLinks
 */

defined( 'ABSPATH' ) || exit;

/**
 * YoghBL_Customizer_Control_Links class.
 */
class YoghBL_Customizer_Control_Links extends yoghbl_customize_Control {

	/**
	 * yoghbl_customize_Manager instance.
	 *
	 * @var yoghbl_customize_Manager
	 */
	public $manager;

	/**
	 * Declare the control type.
	 *
	 * @var string
	 */
	public $type = 'yoghbl_links';

	/**
	 * Constructor.
	 *
	 * Any supplied $args override class property defaults.
	 *
	 * @throws Exception If $id is not valid for this setting type.
	 *
	 * @param yoghbl_customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      A specific ID of the setting.
	 *                                      Can be a theme mod or option name.
	 * @param array                $args    Optional. Setting arguments.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		$this->manager = $manager;
		parent::__construct( $manager, $id, $args );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'customize_controls_print_styles', array( $this, 'add_styles' ) );
		add_action( 'customize_controls_print_scripts', array( $this, 'add_scripts' ), 30 );
		// add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_templates' ) );
	}

	/**
	 * Enqueues scripts for Customizer pane.
	 */
	public function enqueue_scripts() {
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;

		$temp_yoghbl_nav_menu_item_setting = new YoghBL_Customizer_Setting_Link( $this->manager, 'yoghbl_link[' . md5( '' ) . ']' );

		wp_enqueue_script(
			'yoghbl-customize-links',
			YoghBL()->plugin_url() . '/assets/js/customize-links.js',
			array( 'jquery', 'wp-backbone', 'customize-controls', 'accordion', 'nav-menu', 'wp-sanitize' ),
			$version,
			true
		);

		// Pass data to JS.
		$settings = array(
			'settingTransport'         => 'postMessage',
			'phpIntMax'                => PHP_INT_MAX,
			'defaultSettingValues'     => array(
				'yoghbl_nav_menu_item' => $temp_yoghbl_nav_menu_item_setting->default,
			),
		);

		$data = sprintf( 'var _yoghblWpCustomizeNavMenusSettings = %s;', wp_json_encode( $settings ) );
		wp_scripts()->add_data( 'yoghbl-customize-links', 'data', $data );
	}

	/**
	 * CSS styles to improve our form.
	 */
	public function add_styles() {
		// CSS em /assets/css/customize-control-links.css
	}

	/**
	 * Scripts to improve our form.
	 */
	public function add_scripts() {
		$version = defined( 'YOGHBL_VERSION' ) ? YOGHBL_VERSION : null;
		
		wp_enqueue_script(
			'yoghbl-customize-footer',
			YoghBL()->plugin_url() . '/assets/js/customize-footer.js',
			array( 'jquery' ),
			$version,
			true
		);

	}

	/**
	 * Prints the JavaScript templates used to render Menu Customizer components.
	 *
	 * Templates are imported into the JS use wp.template.
	 *
	 * @since 4.3.0
	 */
	public function print_templates() {
		?>

		<script type="text/html" id="tmpl-yoghbl-available-menu-item">
			<li id="menu-item-tpl-{{ data.id }}" class="menu-item-tpl" data-menu-item-id="{{ data.id }}">
				<div class="menu-item-bar">
					<div class="menu-item-handle">
						<span class="item-title" aria-hidden="true">
							<span class="menu-item-title<# if ( ! data.title ) { #> no-title<# } #>">{{ data.title || wp.customize.Menus.data.l10n.untitled }}</span>
						</span>
						<button type="button" class="button-link item-add">
							<span class="screen-reader-text">
							<?php
								/* translators: 1: Title of a menu item, 2: Type of a menu item. */
								printf( __( 'Add to menu: %1$s (%2$s)' ), '{{ data.title || wp.customize.Menus.data.l10n.untitled }}', '{{ data.type_label }}' );
							?>
							</span>
						</button>
					</div>
				</div>
			</li>
		</script>

		<script type="text/html" id="tmpl-yoghbl-menu-item-reorder-nav">
			<div class="menu-item-reorder-nav">
				<?php
				printf(
					'<button type="button" class="menus-move-up">%1$s</button><button type="button" class="menus-move-down">%2$s</button>',
					__( 'Move up' ),
					__( 'Move down' )
				);
				?>
			</div>
		</script>

		<?php
	}

	/**
	 * Render control.
	 */
	public function render_content() {
		$input_id         = '_customize-input-' . $this->id;
		$description_id   = '_customize-description-' . $this->id;
		$describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';
		?>

		<div class="yoghbl-links-control">

			<div class="wp-clearfix">
				<?php if ( ! empty( $this->label ) ) : ?>
					<label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
				<?php endif; ?>
				<?php if ( ! empty( $this->description ) ) : ?>
					<span id="<?php echo esc_attr( $description_id ); ?>" class="description customize-control-description"><?php echo wp_kses_post($this->description); ?></span>
				<?php endif; ?>
				<textarea
					id="<?php echo esc_attr( $input_id ); ?>"
					rows="5"
					<?php echo esc_textarea($describedby_attr); ?>
					<?php $this->input_attrs(); ?>
					<?php $this->link(); ?>
				><?php echo esc_textarea( $this->value() ); ?></textarea>
			</div>

			<!--- <div class="customlinkdiv wp-clearfix">
				<input type="hidden" value="custom" id="yoghbl-custom-menu-item-type" name="menu-item[-1][menu-item-type]" />
				<p id="yoghbl-menu-item-url-wrap" class="wp-clearfix">
					<label class="howto" for="yoghbl-custom-menu-item-url"><?php _e( 'URL', 'yogh-bio-links' ); ?></label>
					<input id="yoghbl-custom-menu-item-url" name="menu-item[-1][menu-item-url]" type="text" class="code menu-item-textbox" placeholder="https://">
				</p>
				<p id="yoghbl-menu-item-name-wrap" class="wp-clearfix">
					<label class="howto" for="yoghbl-custom-menu-item-name"><?php _e( 'Link Text', 'yogh-bio-links' ); ?></label>
					<input id="yoghbl-custom-menu-item-name" name="menu-item[-1][menu-item-title]" type="text" class="regular-text menu-item-textbox">
				</p>
				<p class="button-controls">
					<span class="add-to-menu">
						<input type="submit" class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add', 'yogh-bio-links' ); ?>" name="add-custom-menu-item" id="yoghbl-custom-menu-item-submit">
						<span class="spinner"></span>
					</span>
				</p>
			</div> -->
		</div>

		<?php
	}
}
