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
class YoghBL_Customizer_Control_Links extends WP_Customize_Control {

	/**
	 * WP_Customize_Manager instance.
	 *
	 * @var WP_Customize_Manager
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
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
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
			'yoghbiolinks-customize-links',
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
		wp_scripts()->add_data( 'yoghbiolinks-customize-links', 'data', $data );
	}

	/**
	 * CSS styles to improve our form.
	 */
	public function add_styles() {
		?>

		<style type="text/css">
			#customize-theme-controls .customize-pane-child.control-panel-yoghbiolink_links {
				padding: 12px;
			}
			#yoghbl-menu-item-name-wrap {
				display: block;
			}
			.customize-control .customlinkdiv input[type=text] {
				width: 215px;
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
				// Submit handler for keydown and click on custom menu item.
				function _yoghblSubmitLink( event ) {

					// Only proceed with keydown if it is Enter.
					if ( 'keydown' === event.type && 13 !== event.which ) {
						return;
					}

					yoghblSubmitLink();
				}

				// Adds the custom menu item to the menu.
				function yoghblSubmitLink() {
					var menuItem,
						itemName = $( '#yoghbl-custom-menu-item-name' ),
						itemUrl = $( '#yoghbl-custom-menu-item-url' ),
						url = itemUrl.val().trim(),
						urlRegex;

					/*
					* Allow URLs including:
					* - http://example.com/
					* - //example.com
					* - /directory/
					* - ?query-param
					* - #target
					* - mailto:foo@example.com
					*
					* Any further validation will be handled on the server when the setting is attempted to be saved,
					* so this pattern does not need to be complete.
					*/
					urlRegex = /^((\w+:)?\/\/\w.*|\w+:(?!\/\/$)|\/|\?|#)/;

					if ( '' === itemName.val() ) {
						itemName.addClass( 'invalid' );
						return;
					} else if ( ! urlRegex.test( url ) ) {
						itemUrl.addClass( 'invalid' );
						return;
					}

					menuItem = {
						'title': itemName.val(),
						'url': url,
					};

					yoghblAddItemToMenu( menuItem );

					// Reset the custom link form.
					itemUrl.val( '' ).attr( 'placeholder', 'https://' );
					itemName.val( '' );
				}

				/**
				 * @return
				 */
				function yoghblGetMenuItemControls() {
					var menuControl = this,
						menuItemControls = [],
						menuTermId = menuControl.params.menu_id;

					api.control.each(function( control ) {
						if ( 'nav_menu_item' === control.params.type && control.setting() && menuTermId === control.setting().nav_menu_term_id ) {
							menuItemControls.push( control );
						}
					});

					return menuItemControls;
				}

				/**
				 * Add a new item to link list.
				 */
				function yoghblAddItemToMenu( item ) {
					var menuControl = this, customizeId, settingArgs, setting, menuItemControl, placeholderId, position = 0, priority = 10,
						originalItemId = item.id || '';

					_.each( menuControl.getMenuItemControls(), function( control ) {
						if ( false === control.setting() ) {
							return;
						}
						priority = Math.max( priority, control.priority() );
						if ( 0 === control.setting().menu_item_parent ) {
							position = Math.max( position, control.setting().position );
						}
					});
					// position += 1;
					// priority += 1;

					// item = $.extend(
					// 	{},
					// 	api.Menus.data.defaultSettingValues.nav_menu_item,
					// 	item,
					// 	{
					// 		nav_menu_term_id: menuControl.params.menu_id,
					// 		original_title: item.title,
					// 		position: position
					// 	}
					// );
					// delete item.id; // Only used by Backbone.

					// placeholderId = api.Menus.generatePlaceholderAutoIncrementId();
					// customizeId = 'nav_menu_item[' + String( placeholderId ) + ']';
					// settingArgs = {
					// 	type: 'nav_menu_item',
					// 	transport: api.Menus.data.settingTransport,
					// 	previewer: api.previewer
					// };
					// setting = api.create( customizeId, customizeId, {}, settingArgs );
					// setting.set( item ); // Change from initial empty object to actual item to mark as dirty.

					// // Add the menu item control.
					// menuItemControl = new api.controlConstructor.nav_menu_item( customizeId, {
					// 	type: 'nav_menu_item',
					// 	section: menuControl.id,
					// 	priority: priority,
					// 	settings: {
					// 		'default': customizeId
					// 	},
					// 	menu_item_id: placeholderId,
					// 	original_item_id: originalItemId
					// } );

					// api.control.add( menuItemControl );
					// setting.preview();
					// menuControl.debouncedReflowMenuItems();

					// wp.a11y.speak( api.Menus.data.l10n.itemAdded );

					// return menuItemControl;
				}

				$( document.body ).on( 'click', '#yoghbl-custom-menu-item-submit', function( event ) {
					event.preventDefault();
					_yoghblSubmitLink( event );
				} );

				$( document.body ).on( 'keydown', '#yoghbl-custom-menu-item-name', function( event ) {
					_yoghblSubmitLink( event );
				} );
			} );
		</script>

		<?php
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

		<div class="yoghbiolinks-links-control">

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
					<label class="howto" for="yoghbl-custom-menu-item-url"><?php _e( 'URL' ); ?></label>
					<input id="yoghbl-custom-menu-item-url" name="menu-item[-1][menu-item-url]" type="text" class="code menu-item-textbox" placeholder="https://">
				</p>
				<p id="yoghbl-menu-item-name-wrap" class="wp-clearfix">
					<label class="howto" for="yoghbl-custom-menu-item-name"><?php _e( 'Link Text' ); ?></label>
					<input id="yoghbl-custom-menu-item-name" name="menu-item[-1][menu-item-title]" type="text" class="regular-text menu-item-textbox">
				</p>
				<p class="button-controls">
					<span class="add-to-menu">
						<input type="submit" class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add' ); ?>" name="add-custom-menu-item" id="yoghbl-custom-menu-item-submit">
						<span class="spinner"></span>
					</span>
				</p>
			</div> -->
		</div>

		<?php
	}
}
