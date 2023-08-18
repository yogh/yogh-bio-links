( function() {
	// Add listener for the "yoghbl_background_color" control.
	wp.customize( 'yoghbl_background_color', function( value ) {
		value.bind( function( to ) {
			var lum = yoghblGetHexLum( to ),
				isDark = 127 > lum,
				textColor = ! isDark ? 'var(--yoghbl--color-dark-gray)' : 'var(--yoghbl--color-light-gray)',
				stylesheetID = 'yoghbl-customizer-inline-styles',
				stylesheet,
				styles;

			stylesheet = jQuery( '#' + stylesheetID );
			styles = '';
			// If the stylesheet doesn't exist, create it and append it to <head>.
			if ( ! stylesheet.length ) {
				jQuery( '#yoghbl-general-inline-css' ).after( '<style id="' + stylesheetID + '"></style>' );
				stylesheet = jQuery( '#' + stylesheetID );
			}

			// Generate the styles.
			styles += '--yoghbl--body-color:' + textColor + ';';
			styles += '--yoghbl--body-bg:' + to + ';';

			// // Add the styles.
			stylesheet.html( ':root{' + styles + '}' );
		} );
	} );
	// Add listener for the "yoghbl_url_slug" control.
	wp.customize( 'yoghbl_url_slug', function( value ) {
		jQuery( '.edit-post-post-link__link-post-name' ).text( value );
	} );
}() );
