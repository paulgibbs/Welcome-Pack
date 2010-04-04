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

});