( function( api, $ ) {
	'use strict';

	api.YoghbiolinkLinks = api.YoghbiolinkLinks || {};

	api.YoghbiolinkLinks.LinksSection = api.Section.extend( {

		initialize: function( id, options ) {
			var section = this;
			api.Section.prototype.initialize.call( section, id, options );
		},

		ready: function() {
			var section = this;

			section.populateControls();
		},

		populateControls: function() {
			var section = this,
				menuControl;

			menuControl = new api.controlConstructor.yoghbl_links( section.id, {
				type: 'yoghbl_links',
				section: section.id,
				priority: 998,
				settings: {
					'default': section.id
				}
			} );
			api.control.add( menuControl );
			menuControl.active.set( true );
		},

		onChangeExpanded: function( expanded, args ) {
			var section = this;

			api.Section.prototype.onChangeExpanded.call( section, expanded, args );
			if ( expanded ) {
				_.each( api.section( section.id ).controls(), function( control ) {
					if ( 'yoghbl_link' === control.params.type ) {
						control.actuallyEmbed();
					}
				} );
			}
		}
	} );

	api.YoghbiolinkLinks.LinkControl = api.Control.extend( {

		initialize: function( id, options ) {
			var control = this;
			control.expanded = new api.Value( false );
			control.expandedArgumentsQueue = [];
			control.expanded.bind( function( expanded ) {
				var args = control.expandedArgumentsQueue.shift();
				args = $.extend( {}, control.defaultExpandedArguments, args );
				control.onChangeExpanded( expanded, args );
			} );
			api.Control.prototype.initialize.call( control, id, options );
			control.active.validate = function() {
				var value, section = api.section( control.section() );
				if ( section ) {
					value = section.active();
				} else {
					value = false;
				}
				return value;
			};
		},

		embed: function() {
			var control = this,
				sectionId = control.section(),
				section;
			if ( ! sectionId ) {
				return;
			}
			section = api.section( sectionId );
			if ( ( section && section.expanded() ) || api.settings.autofocus.control === control.id ) {
				control.actuallyEmbed();
			}
		},

		actuallyEmbed: function() {
			var control = this;
			if ( 'resolved' === control.deferred.embedded.state() ) {
				return;
			}
			control.renderContent();
			control.deferred.embedded.resolve(); // This triggers control.ready().
		},

		ready: function() {
			this._setupControlToggle();
			this._setupUpdateUI();
			this._setupTitleUI();
		},

		_setupControlToggle: function() {
			var control = this;

			this.container.find( '.menu-item-handle' ).on( 'click', function( e ) {
				e.preventDefault();
				e.stopPropagation();
				control.toggleForm();
			} );
		},

		_setupUpdateUI: function() {
			var control = this,
				settingValue = control.setting();

			control.elements = {};
			control.elements.url = new api.Element( control.container.find( '.edit-menu-item-url' ) );
			control.elements.title = new api.Element( control.container.find( '.edit-menu-item-title' ) );

			_.each( control.elements, function( element, property ) {
				element.bind(function( value ) {
					var settingValue = control.setting();
					if ( settingValue && settingValue[ property ] !== value ) {
						settingValue = _.clone( settingValue );
						settingValue[ property ] = value;
						control.setting.set( settingValue );
					}
				});
				if ( settingValue ) {
					element.set( settingValue[ property ] );
				}
			} );
		},

		_setupTitleUI: function() {
			var control = this, titleEl;

			control.container.find( '.edit-menu-item-title' ).on( 'blur', function() {
				$( this ).val( $( this ).val().trim() );
			} );

			titleEl = control.container.find( '.menu-item-title' );
			control.setting.bind( function( item ) {
				var trimmedTitle, titleText;
				if ( ! item ) {
					return;
				}
				item.title = item.title || '';
				trimmedTitle = item.title.trim();

				titleText = trimmedTitle || api.Menus.data.l10n.untitled;

				if ( trimmedTitle ) {
					titleEl
						.text( titleText );
				}
			} );
		},

		renderContent: function() {
			var control = this,
				settingValue = control.setting(),
				containerClasses;

			containerClasses = [
				'menu-item',
				'menu-item-edit-inactive',
			];

			control.params.el_classes = containerClasses.join( ' ' );
			control.params.url = settingValue.url;
			control.params.title = settingValue.title;

			control.container.addClass( control.params.el_classes );

			api.Control.prototype.renderContent.call( control );
		},

		expandControlSection: function() {
			var $section = this.container.closest( '.accordion-section' );
			if ( ! $section.hasClass( 'open' ) ) {
				$section.find( '.accordion-section-title:first' ).trigger( 'click' );
			}
		},

		_toggleExpanded: api.Section.prototype._toggleExpanded,

		expand: api.Section.prototype.expand,

		expandForm: function( params ) {
			this.expand( params );
		},

		collapse: api.Section.prototype.collapse,

		collapseForm: function( params ) {
			this.collapse( params );
		},

		toggleForm: function( showOrHide, params ) {
			if ( typeof showOrHide === 'undefined' ) {
				showOrHide = ! this.expanded();
			}
			if ( showOrHide ) {
				this.expand( params );
			} else {
				this.collapse( params );
			}
		},

		onChangeExpanded: function( showOrHide, params ) {
			var self = this, $menuitem, $inside, complete;

			$menuitem = this.container;
			$inside = $menuitem.find( '.menu-item-settings:first' );
			if ( 'undefined' === typeof showOrHide ) {
				showOrHide = ! $inside.is( ':visible' );
			}

			// Already expanded or collapsed.
			if ( $inside.is( ':visible' ) === showOrHide ) {
				if ( params && params.completeCallback ) {
					params.completeCallback();
				}
				return;
			}

			if ( showOrHide ) {
				// Close all other menu item controls before expanding this one.
				api.control.each( function( otherControl ) {
					if ( self.params.type === otherControl.params.type && self !== otherControl ) {
						otherControl.collapseForm();
					}
				} );

				complete = function() {
					$menuitem
						.removeClass( 'menu-item-edit-inactive' )
						.addClass( 'menu-item-edit-active' );
					self.container.trigger( 'expanded' );

					if ( params && params.completeCallback ) {
						params.completeCallback();
					}
				};

				$menuitem.find( '.item-edit' ).attr( 'aria-expanded', 'true' );
				$inside.slideDown( 'fast', complete );

				self.container.trigger( 'expand' );
			} else {
				complete = function() {
					$menuitem
						.addClass( 'menu-item-edit-inactive' )
						.removeClass( 'menu-item-edit-active' );
					self.container.trigger( 'collapsed' );

					if ( params && params.completeCallback ) {
						params.completeCallback();
					}
				};

				self.container.trigger( 'collapse' );

				$menuitem.find( '.item-edit' ).attr( 'aria-expanded', 'false' );
				$inside.slideUp( 'fast', complete );
			}
		}
	} );

	api.YoghbiolinkLinks.LinksControl = api.Control.extend( {
		/**
		 * Set up the control.
		 */
		ready: function() {
			var control = this,
				section = api.section( control.section() ),
				menuId = control.params.menu_id,
				menu = control.setting(),
				name,
				widgetTemplate,
				select;
			console.log( control.params.menu_id );

			if ( 'undefined' === typeof this.params.menu_id ) {
				throw new Error( 'params.menu_id was not defined' );
			}

			/*
			 * Since the control is not registered in PHP, we need to prevent the
			 * preview's sending of the activeControls to result in this control
			 * being deactivated.
			 */
			control.active.validate = function() {
				var value;
				if ( section ) {
					value = section.active();
				} else {
					value = false;
				}
				return value;
			};

			control.$controlSection = section.headContainer;
			control.$sectionContent = control.container.closest( '.accordion-section-content' );

			this._setupModel();

			api.section( control.section(), function( section ) {
				section.deferred.initSortables.done(function( menuList ) {
					control._setupSortable( menuList );
				});
			} );

			this._setupAddition();
			this._setupTitle();

			// Add menu to Navigation Menu widgets.
			if ( menu ) {
				name = displayNavMenuName( menu.name );

				// Add the menu to the existing controls.
				api.control.each( function( widgetControl ) {
					if ( ! widgetControl.extended( api.controlConstructor.widget_form ) || 'nav_menu' !== widgetControl.params.widget_id_base ) {
						return;
					}
					widgetControl.container.find( '.nav-menu-widget-form-controls:first' ).show();
					widgetControl.container.find( '.nav-menu-widget-no-menus-message:first' ).hide();

					select = widgetControl.container.find( 'select' );
					if ( 0 === select.find( 'option[value=' + String( menuId ) + ']' ).length ) {
						select.append( new Option( name, menuId ) );
					}
				} );

				// Add the menu to the widget template.
				widgetTemplate = $( '#available-widgets-list .widget-tpl:has( input.id_base[ value=nav_menu ] )' );
				widgetTemplate.find( '.nav-menu-widget-form-controls:first' ).show();
				widgetTemplate.find( '.nav-menu-widget-no-menus-message:first' ).hide();
				select = widgetTemplate.find( '.widget-inside select:first' );
				if ( 0 === select.find( 'option[value=' + String( menuId ) + ']' ).length ) {
					select.append( new Option( name, menuId ) );
				}
			}

			/*
			 * Wait for menu items to be added.
			 * Ideally, we'd bind to an event indicating construction is complete,
			 * but deferring appears to be the best option today.
			 */
			_.defer( function () {
				control.updateInvitationVisibility();
			} );
		},

		/**
		 * Update ordering of menu item controls when the setting is updated.
		 */
		_setupModel: function() {
			var control = this,
				menuId = control.params.menu_id;

			control.setting.bind( function( to ) {
				var name;
				if ( false === to ) {
					control._handleDeletion();
				} else {
					// Update names in the Navigation Menu widgets.
					name = displayNavMenuName( to.name );
					api.control.each( function( widgetControl ) {
						if ( ! widgetControl.extended( api.controlConstructor.widget_form ) || 'nav_menu' !== widgetControl.params.widget_id_base ) {
							return;
						}
						var select = widgetControl.container.find( 'select' );
						select.find( 'option[value=' + String( menuId ) + ']' ).text( name );
					});
				}
			} );
		},

		/**
		 * Allow items in each menu to be re-ordered, and for the order to be previewed.
		 *
		 * Notice that the UI aspects here are handled by wpNavMenu.initSortables()
		 * which is called in MenuSection.onChangeExpanded()
		 *
		 * @param {Object} menuList - The element that has sortable().
		 */
		_setupSortable: function( menuList ) {
			var control = this;

			if ( ! menuList.is( control.$sectionContent ) ) {
				throw new Error( 'Unexpected menuList.' );
			}

			menuList.on( 'sortstart', function() {
				control.isSorting = true;
			});

			menuList.on( 'sortstop', function() {
				setTimeout( function() { // Next tick.
					var menuItemContainerIds = control.$sectionContent.sortable( 'toArray' ),
						menuItemControls = [],
						position = 0,
						priority = 10;

					control.isSorting = false;

					// Reset horizontal scroll position when done dragging.
					control.$sectionContent.scrollLeft( 0 );

					_.each( menuItemContainerIds, function( menuItemContainerId ) {
						var menuItemId, menuItemControl, matches;
						matches = menuItemContainerId.match( /^customize-control-nav_menu_item-(-?\d+)$/, '' );
						if ( ! matches ) {
							return;
						}
						menuItemId = parseInt( matches[1], 10 );
						menuItemControl = api.control( 'nav_menu_item[' + String( menuItemId ) + ']' );
						if ( menuItemControl ) {
							menuItemControls.push( menuItemControl );
						}
					} );

					_.each( menuItemControls, function( menuItemControl ) {
						if ( false === menuItemControl.setting() ) {
							// Skip deleted items.
							return;
						}
						var setting = _.clone( menuItemControl.setting() );
						position += 1;
						priority += 1;
						setting.position = position;
						menuItemControl.priority( priority );

						// Note that wpNavMenu will be setting this .menu-item-data-parent-id input's value.
						setting.menu_item_parent = parseInt( menuItemControl.container.find( '.menu-item-data-parent-id' ).val(), 10 );
						if ( ! setting.menu_item_parent ) {
							setting.menu_item_parent = 0;
						}

						menuItemControl.setting.set( setting );
					});
				});

			});
			control.isReordering = false;

			/**
			 * Keyboard-accessible reordering.
			 */
			this.container.find( '.reorder-toggle' ).on( 'click', function() {
				control.toggleReordering( ! control.isReordering );
			} );
		},

		/**
		 * Set up UI for adding a new menu item.
		 */
		_setupAddition: function() {
			var self = this;

			this.container.find( '.add-new-menu-item' ).on( 'click', function( event ) {
				if ( self.$sectionContent.hasClass( 'reordering' ) ) {
					return;
				}

				if ( ! $( 'body' ).hasClass( 'adding-menu-items' ) ) {
					$( this ).attr( 'aria-expanded', 'true' );
					api.Menus.availableMenuItemsPanel.open( self );
				} else {
					$( this ).attr( 'aria-expanded', 'false' );
					api.Menus.availableMenuItemsPanel.close();
					event.stopPropagation();
				}
			} );
		},

		_handleDeletion: function() {
			var control = this,
				section,
				menuId = control.params.menu_id,
				removeSection,
				widgetTemplate,
				navMenuCount = 0;
			section = api.section( control.section() );
			removeSection = function() {
				section.container.remove();
				api.section.remove( section.id );
			};

			if ( section && section.expanded() ) {
				section.collapse({
					completeCallback: function() {
						removeSection();
						wp.a11y.speak( api.Menus.data.l10n.menuDeleted );
						api.panel( 'nav_menus' ).focus();
					}
				});
			} else {
				removeSection();
			}

			api.each(function( setting ) {
				if ( /^nav_menu\[/.test( setting.id ) && false !== setting() ) {
					navMenuCount += 1;
				}
			});

			// Remove the menu from any Navigation Menu widgets.
			api.control.each(function( widgetControl ) {
				if ( ! widgetControl.extended( api.controlConstructor.widget_form ) || 'nav_menu' !== widgetControl.params.widget_id_base ) {
					return;
				}
				var select = widgetControl.container.find( 'select' );
				if ( select.val() === String( menuId ) ) {
					select.prop( 'selectedIndex', 0 ).trigger( 'change' );
				}

				widgetControl.container.find( '.nav-menu-widget-form-controls:first' ).toggle( 0 !== navMenuCount );
				widgetControl.container.find( '.nav-menu-widget-no-menus-message:first' ).toggle( 0 === navMenuCount );
				widgetControl.container.find( 'option[value=' + String( menuId ) + ']' ).remove();
			});

			// Remove the menu to the nav menu widget template.
			widgetTemplate = $( '#available-widgets-list .widget-tpl:has( input.id_base[ value=nav_menu ] )' );
			widgetTemplate.find( '.nav-menu-widget-form-controls:first' ).toggle( 0 !== navMenuCount );
			widgetTemplate.find( '.nav-menu-widget-no-menus-message:first' ).toggle( 0 === navMenuCount );
			widgetTemplate.find( 'option[value=' + String( menuId ) + ']' ).remove();
		},

		/**
		 * Update Section Title as menu name is changed.
		 */
		_setupTitle: function() {
			var control = this;

			control.setting.bind( function( menu ) {
				if ( ! menu ) {
					return;
				}

				var section = api.section( control.section() ),
					menuId = control.params.menu_id,
					controlTitle = section.headContainer.find( '.accordion-section-title' ),
					sectionTitle = section.contentContainer.find( '.customize-section-title h3' ),
					location = section.headContainer.find( '.menu-in-location' ),
					action = sectionTitle.find( '.customize-action' ),
					name = displayNavMenuName( menu.name );

				// Update the control title.
				controlTitle.text( name );
				if ( location.length ) {
					location.appendTo( controlTitle );
				}

				// Update the section title.
				sectionTitle.text( name );
				if ( action.length ) {
					action.prependTo( sectionTitle );
				}

				// Update the nav menu name in location selects.
				api.control.each( function( control ) {
					if ( /^nav_menu_locations\[/.test( control.id ) ) {
						control.container.find( 'option[value=' + menuId + ']' ).text( name );
					}
				} );

				// Update the nav menu name in all location checkboxes.
				section.contentContainer.find( '.customize-control-checkbox input' ).each( function() {
					if ( $( this ).prop( 'checked' ) ) {
						$( '.current-menu-location-name-' + $( this ).data( 'location-id' ) ).text( name );
					}
				} );
			} );
		},

		/***********************************************************************
		 * Begin public API methods
		 **********************************************************************/

		/**
		 * Enable/disable the reordering UI
		 *
		 * @param {boolean} showOrHide to enable/disable reordering
		 */
		toggleReordering: function( showOrHide ) {
			var addNewItemBtn = this.container.find( '.add-new-menu-item' ),
				reorderBtn = this.container.find( '.reorder-toggle' ),
				itemsTitle = this.$sectionContent.find( '.item-title' );

			showOrHide = Boolean( showOrHide );

			if ( showOrHide === this.$sectionContent.hasClass( 'reordering' ) ) {
				return;
			}

			this.isReordering = showOrHide;
			this.$sectionContent.toggleClass( 'reordering', showOrHide );
			this.$sectionContent.sortable( this.isReordering ? 'disable' : 'enable' );
			if ( this.isReordering ) {
				addNewItemBtn.attr({ 'tabindex': '-1', 'aria-hidden': 'true' });
				reorderBtn.attr( 'aria-label', api.Menus.data.l10n.reorderLabelOff );
				wp.a11y.speak( api.Menus.data.l10n.reorderModeOn );
				itemsTitle.attr( 'aria-hidden', 'false' );
			} else {
				addNewItemBtn.removeAttr( 'tabindex aria-hidden' );
				reorderBtn.attr( 'aria-label', api.Menus.data.l10n.reorderLabelOn );
				wp.a11y.speak( api.Menus.data.l10n.reorderModeOff );
				itemsTitle.attr( 'aria-hidden', 'true' );
			}

			if ( showOrHide ) {
				_( this.getMenuItemControls() ).each( function( formControl ) {
					formControl.collapseForm();
				} );
			}
		},

		/**
		 * @return {wp.customize.controlConstructor.nav_menu_item[]}
		 */
		getMenuItemControls: function() {
			var menuControl = this,
				menuItemControls = [],
				menuTermId = menuControl.params.menu_id;

			api.control.each(function( control ) {
				if ( 'nav_menu_item' === control.params.type && control.setting() && menuTermId === control.setting().nav_menu_term_id ) {
					menuItemControls.push( control );
				}
			});

			return menuItemControls;
		},

		/**
		 * Make sure that each menu item control has the proper depth.
		 */
		reflowMenuItems: function() {
			var menuControl = this,
				menuItemControls = menuControl.getMenuItemControls(),
				reflowRecursively;

			reflowRecursively = function( context ) {
				var currentMenuItemControls = [],
					thisParent = context.currentParent;
				_.each( context.menuItemControls, function( menuItemControl ) {
					if ( thisParent === menuItemControl.setting().menu_item_parent ) {
						currentMenuItemControls.push( menuItemControl );
						// @todo We could remove this item from menuItemControls now, for efficiency.
					}
				});
				currentMenuItemControls.sort( function( a, b ) {
					return a.setting().position - b.setting().position;
				});

				_.each( currentMenuItemControls, function( menuItemControl ) {
					// Update position.
					context.currentAbsolutePosition += 1;
					menuItemControl.priority.set( context.currentAbsolutePosition ); // This will change the sort order.

					// Update depth.
					if ( ! menuItemControl.container.hasClass( 'menu-item-depth-' + String( context.currentDepth ) ) ) {
						_.each( menuItemControl.container.prop( 'className' ).match( /menu-item-depth-\d+/g ), function( className ) {
							menuItemControl.container.removeClass( className );
						});
						menuItemControl.container.addClass( 'menu-item-depth-' + String( context.currentDepth ) );
					}
					menuItemControl.container.data( 'item-depth', context.currentDepth );

					// Process any children items.
					context.currentDepth += 1;
					context.currentParent = menuItemControl.params.menu_item_id;
					reflowRecursively( context );
					context.currentDepth -= 1;
					context.currentParent = thisParent;
				});

				// Update class names for reordering controls.
				if ( currentMenuItemControls.length ) {
					_( currentMenuItemControls ).each(function( menuItemControl ) {
						menuItemControl.container.removeClass( 'move-up-disabled move-down-disabled move-left-disabled move-right-disabled' );
						if ( 0 === context.currentDepth ) {
							menuItemControl.container.addClass( 'move-left-disabled' );
						} else if ( 10 === context.currentDepth ) {
							menuItemControl.container.addClass( 'move-right-disabled' );
						}
					});

					currentMenuItemControls[0].container
						.addClass( 'move-up-disabled' )
						.addClass( 'move-right-disabled' )
						.toggleClass( 'move-down-disabled', 1 === currentMenuItemControls.length );
					currentMenuItemControls[ currentMenuItemControls.length - 1 ].container
						.addClass( 'move-down-disabled' )
						.toggleClass( 'move-up-disabled', 1 === currentMenuItemControls.length );
				}
			};

			reflowRecursively( {
				menuItemControls: menuItemControls,
				currentParent: 0,
				currentDepth: 0,
				currentAbsolutePosition: 0
			} );

			menuControl.updateInvitationVisibility( menuItemControls );
			menuControl.container.find( '.reorder-toggle' ).toggle( menuItemControls.length > 1 );
		},

		/**
		 * Note that this function gets debounced so that when a lot of setting
		 * changes are made at once, for instance when moving a menu item that
		 * has child items, this function will only be called once all of the
		 * settings have been updated.
		 */
		debouncedReflowMenuItems: _.debounce( function() {
			this.reflowMenuItems.apply( this, arguments );
		}, 0 ),

		/**
		 * Add a new item to this menu.
		 *
		 * @param {Object} item - Value for the nav_menu_item setting to be created.
		 * @return {wp.customize.Menus.controlConstructor.nav_menu_item} The newly-created nav_menu_item control instance.
		 */
		addItemToMenu: function( item ) {
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
			position += 1;
			priority += 1;

			item = $.extend(
				{},
				api.Menus.data.defaultSettingValues.nav_menu_item,
				item,
				{
					nav_menu_term_id: menuControl.params.menu_id,
					original_title: item.title,
					position: position
				}
			);
			delete item.id; // Only used by Backbone.

			placeholderId = api.Menus.generatePlaceholderAutoIncrementId();
			customizeId = 'nav_menu_item[' + String( placeholderId ) + ']';
			settingArgs = {
				type: 'nav_menu_item',
				transport: api.Menus.data.settingTransport,
				previewer: api.previewer
			};
			setting = api.create( customizeId, customizeId, {}, settingArgs );
			setting.set( item ); // Change from initial empty object to actual item to mark as dirty.

			// Add the menu item control.
			menuItemControl = new api.controlConstructor.nav_menu_item( customizeId, {
				type: 'nav_menu_item',
				section: menuControl.id,
				priority: priority,
				settings: {
					'default': customizeId
				},
				menu_item_id: placeholderId,
				original_item_id: originalItemId
			} );

			api.control.add( menuItemControl );
			setting.preview();
			menuControl.debouncedReflowMenuItems();

			wp.a11y.speak( api.Menus.data.l10n.itemAdded );

			return menuItemControl;
		},

		/**
		 * Show an invitation to add new menu items when there are no menu items.
		 *
		 * @since 4.9.0
		 *
		 * @param {wp.customize.controlConstructor.nav_menu_item[]} optionalMenuItemControls
		 */
		updateInvitationVisibility: function ( optionalMenuItemControls ) {
			var menuItemControls = optionalMenuItemControls || this.getMenuItemControls();

			this.container.find( '.new-menu-item-invitation' ).toggle( menuItemControls.length === 0 );
		}
	} );

	$.extend( api.controlConstructor, {
		yoghbl_link: api.YoghbiolinkLinks.LinkControl,
		yoghbl_links: api.YoghbiolinkLinks.LinksControl
	} );

	$.extend( api.sectionConstructor, {
		yoghbl_links: api.YoghbiolinkLinks.LinksSection
	} );

})( wp.customize, jQuery );
