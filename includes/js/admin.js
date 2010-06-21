jQuery(document).ready( function() {

	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

	if (-1 === window.location.search.indexOf('tab=emails'))
		postboxes.add_postbox_toggles('buddypress_page_welcome-pack');
	else
		postboxes.add_postbox_toggles('buddypress_page_welcome-pack-emails');

	jQuery('div.initially-hidden').each( function() { 
		jQuery(this).hide();
	});

	jQuery('#dpw-admin-metaboxes-general input').click( function() {
		var button = jQuery(this);
		var config = jQuery('div.setting-' + button.attr('class'));

		if ( 1 == button.attr('value') )
			config.css('background-color', 'rgb(255,255,224)').slideDown('fast').animate( { backgroundColor: 'rgb(255,255,255)' }, 1600);
		else
			config.stop(true).slideUp();
	});

	jQuery('#emailpicker').change( function() {
		var index = this.selectedIndex;
		if ( 0 == index ) {
			jQuery('#email').hide().empty();
			return;
		}

		jQuery.post( ajaxurl, {
			action: 'dpw_fetch_email',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce': jQuery("#_ajax_nonce_dpw_emails").val(),
			'id': index
		},
		function(response)
		{
			if ( response[0] + response[1] == '-1' )
				return;

			jQuery('#email').html(response.substr(0, response.length-1)).show();
		});
	});

});