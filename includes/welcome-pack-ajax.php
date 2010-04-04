<?php
//return email subject + body
function dpw_fetch_email() {
	global $bp;

	check_ajax_referer( 'dpw-emails', '_ajax_nonce_dpw_emails' );

	if ( !is_user_logged_in() || !is_site_admin() || !isset( $_POST['id'] ) ) {
		echo '-1';
		return false;
	}

//	$emails
}
add_action( 'wp_ajax_dpw_fetch_email', 'dpw_fetch_email' );
?>