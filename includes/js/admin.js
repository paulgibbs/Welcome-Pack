jQuery(document).ready( function() {

	jq('.if-js-closed').removeClass('if-js-closed').addClass('closed');

	if (-1 === window.location.search.indexOf('tab=emails'))
		postboxes.add_postbox_toggles('buddypress_page_welcome-pack');
	else
		postboxes.add_postbox_toggles('buddypress_page_welcome-pack-emails');

	jq('div.initially-hidden').each( function() { 
		jq(this).hide();
	});

	jq('#dpw-admin-metaboxes-general input').click( function() {
		var button = jq(this);
		var config = jq('div.setting-' + button.attr('class'));

		if ( 1 == button.attr('value') )
			config.css('background-color', 'rgb(255,255,224)').slideDown('fast').animate( { backgroundColor: 'rgb(255,255,255)' }, 1600);
		else
			config.stop(true).slideUp();
	});

	jq('#emailpicker').change( function() {
		var index = this.selectedIndex;
		if ( 0 == index ) {
			jq('#email').hide().empty();
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

			jq('#email').html(response.substr(0, response.length-1)).show();
		});
	});

});