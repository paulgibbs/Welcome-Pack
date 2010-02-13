jQuery(document).ready( function() {
	jQuery("select#welcomepack_emails_picker").change( function() {
		var email_name = jQuery("OPTION:selected", this).attr('value');
		if ( "" == email_name )
			return;

		jQuery.post( ajaxurl, {
			action: 'dpw_admin_emails_picker',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce': jQuery("input#_wpnonce-dpw-emails").val(),
			'email_name': email_name
		},
		function(response) {
			if ("-1" == response)
				return;

			response = response.substr(0, response.length-1);
			jQuery("div#welcomepack_emails_details").empty().fadeIn(200).html(response);
		} );
	} );
} );