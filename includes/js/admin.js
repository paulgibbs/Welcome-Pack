jQuery(document).ready( function() {

	jq('.if-js-closed').removeClass('if-js-closed').addClass('closed');
	postboxes.add_postbox_toggles('buddypress_page_welcome-pack');

	/*jq('input#remembercustomdetails').click( function(event) {
		jq('tr.initially_hidden').toggle();
		jq('input#remembercustomdetails_name').focus();
	});*/

});