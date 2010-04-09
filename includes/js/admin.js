jQuery(document).ready( function() {

	jq('.if-js-closed').removeClass('if-js-closed').addClass('closed');
	postboxes.add_postbox_toggles('buddypress_page_welcome-pack');

	jq('div.initially-hidden').each( function() { 
		jq(this).hide();
	});

	jq('div#dpw-admin-metaboxes-general input').click( function() {
		var button = jq(this);
		var config = jq('div.setting-' + button.attr('class'));

		if ( 1 == button.attr('value') )
			config.css('background-color', 'rgb(255,255,224)').slideDown('fast').animate( { backgroundColor: 'rgb(255,255,255)' }, 1600);
		else
			config.stop(true).slideUp();
	});

	jq('select#emailpicker').change( function() {
		var index = this.selectedIndex;
		if ( 0 == index )
			return;

		jQuery.post( ajaxurl, {
			action: 'dpw_fetch_email',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce': jQuery("input#_ajax_nonce_dpw_emails").val(),
			'id': this.selectedIndex
		},
		function(response)
		{
			if ( response[0] + response[1] == '-1' )
				return;

			var email = jq('div#email');
			email.html(response.substr(0, response.length-1));
			email.show();
		});
	});

});