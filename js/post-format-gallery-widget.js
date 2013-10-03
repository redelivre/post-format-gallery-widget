/*
http://www.johngadbois.com/adding-your-own-callbacks-to-wordpress-ajax-requests/
http://wordpress.stackexchange.com/questions/5515/update-widget-form-after-drag-and-drop-wp-save-bug
*/

jQuery(document).ready(function(){

	jQuery(document).ajaxSuccess(function( event, xhr, ajaxOptions ) {
	
		var request = {}, pairs = ajaxOptions.data.split('&'), i, split, galleryStyle;
		
		// Split the data
		for ( i in pairs ) {
			split = pairs[i].split('=');
			request[decodeURIComponent(split[0])] = decodeURIComponent(split[1]);
			
			// Search for use-gallery-style checkbox
			if ( decodeURIComponent(split[0]).search('use-gallery-style') > -1 )
				galleryStyle = true;
		}
		
		if ( request['action'] && request['action'] === 'save-widget' && request['id_base'] === 'post-format-gallery-widget' ) {
			
			if ( galleryStyle === true )
				jQuery('[id$="' + request['widget-id'] + '"]' + ' .wp-use-gallery-style-options').show();
			else
				jQuery('[id$="' + request['widget-id'] + '"]' + ' .wp-use-gallery-style-options').hide();

		}
	
	});

	jQuery('body').on('change', '.wp-use-gallery-style-checkbox', function(){
		
		var $wpgs = jQuery(this);
		
		// Verify if the widget will use the default WP gallery style
		if ( $wpgs.is( ':checked' ) )
			$wpgs.parent().next().slideDown();
		else
			$wpgs.parent().next().slideUp();
		
	});
	
	// Trigger change to verify the checkboxes
	jQuery('.wp-use-gallery-style-checkbox').trigger('change');
	
});
