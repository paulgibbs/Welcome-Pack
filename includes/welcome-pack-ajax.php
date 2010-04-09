<?php
function dpw_fetch_email() {
	global $bp;

	check_ajax_referer( 'dpw-emails', '_wpnonce' );

	if ( !is_user_logged_in() || !is_site_admin() || !isset( $_POST['id'] ) || !$_POST['id'] ) {
		echo '-1';
		return;
	}

	$emails = maybe_unserialize( get_site_option( 'welcomepack' ) );
	$emails = $emails['emails'];

	$id = (int)$_POST['id'];
	if ( !isset( $emails[$id] ) ) {
		echo '-1';
		return;
	}
	?>
bing: <?php echo $emails[$id]['name'] ?>
	<?php
}
add_action( 'wp_ajax_dpw_fetch_email', 'dpw_fetch_email' );
?>