jQuery(function ($) {
	// Submit handler for keydown and click on custom menu item.
	function _yoghblSubmitLink(event) {
		// Only proceed with keydown if it is Enter.
		if ("keydown" === event.type && 13 !== event.which) {
			return;
		}

		yoghblSubmitLink();
	}

	// Adds the custom menu item to the menu.
	function yoghblSubmitLink() {
		var menuItem,
			itemName = $("#yoghbl-custom-menu-item-name"),
			itemUrl = $("#yoghbl-custom-menu-item-url"),
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

		if ("" === itemName.val()) {
			itemName.addClass("invalid");
			return;
		} else if (!urlRegex.test(url)) {
			itemUrl.addClass("invalid");
			return;
		}

		menuItem = {
			title: itemName.val(),
			url: url,
		};

		yoghblAddItemToMenu(menuItem);

		// Reset the custom link form.
		itemUrl.val("").attr("placeholder", "https://");
		itemName.val("");
	}

	/**
	 * @return
	 */
	function yoghblGetMenuItemControls() {
		var menuControl = this,
			menuItemControls = [],
			menuTermId = menuControl.params.menu_id;

		api.control.each(function (control) {
			if (
				"nav_menu_item" === control.params.type &&
				control.setting() &&
				menuTermId === control.setting().nav_menu_term_id
			) {
				menuItemControls.push(control);
			}
		});

		return menuItemControls;
	}

	/**
	 * Add a new item to link list.
	 */
	function yoghblAddItemToMenu(item) {
		var menuControl = this,
			customizeId,
			settingArgs,
			setting,
			menuItemControl,
			placeholderId,
			position = 0,
			priority = 10,
			originalItemId = item.id || "";

		_.each(menuControl.getMenuItemControls(), function (control) {
			if (false === control.setting()) {
				return;
			}
			priority = Math.max(priority, control.priority());
			if (0 === control.setting().menu_item_parent) {
				position = Math.max(position, control.setting().position);
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

	$(document.body).on(
		"click",
		"#yoghbl-custom-menu-item-submit",
		function (event) {
			event.preventDefault();
			_yoghblSubmitLink(event);
		}
	);

	$(document.body).on(
		"keydown",
		"#yoghbl-custom-menu-item-name",
		function (event) {
			_yoghblSubmitLink(event);
		}
	);
});

jQuery(function ($) {
	$(document.body).on(
		"input",
		'.yoghbl-slug-control input[type="text"]',
		function () {
			var $wrapper = $(this).closest(".yoghbl-slug-control"),
				$postName = $wrapper.find(
					".edit-post-post-link__link-post-name"
				),
				$postLink = $wrapper.find(".edit-post-post-link__link"),
				value = $wrapper.find("input").val();

			if ("" === value) {
				value = $postName.data("default");
			}

			$postLink.attr(
				"href",
				$postLink.data("href").toString().replace("%%slug%%", value)
			);
			$postName.text(value);

			return false;
		}
	);

	$(document.body).on(
		"blur",
		'.yoghbl-slug-control input[type="text"]',
		function () {
			var $wrapper = $(this).closest(".yoghbl-slug-control"),
				$postName = $wrapper.find(
					".edit-post-post-link__link-post-name"
				),
				$postLink = $wrapper.find(".edit-post-post-link__link"),
				$input = $wrapper.find("input"),
				value = $input.val();

			if ("" !== value) {
				value = wp.url.cleanForSlug(value);
				$input.val(value);
				$postLink.attr(
					"href",
					$postLink.data("href").toString().replace("%%slug%%", value)
				);
				$postName.text(value);
			}

			return false;
		}
	);
});
