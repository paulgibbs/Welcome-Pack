<?php
function dpw_fetch_email() {
	global $bp;

	check_ajax_referer( 'dpw-emails', '_wpnonce' );

	if ( !is_user_logged_in() || !is_site_admin() || !isset( $_POST['id'] ) || !$_POST['id'] ) {
		echo '-1';
		return;
	}

	$emails = maybe_unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) );
	$emails = $emails['emails'];

	$id = (int)$_POST['id'];
	if ( !isset( $emails[$id] ) ) {
		echo '-1';
		return;
	}
?>
	<input type="hidden" name="welcomepack[email_id]" value="<?php echo $id ?>" />
	<div class="setting wide">
		<div class="settingname"><p><?php _e( 'Subject', 'dpw' ) ?></p></div>
		<div class="settingvalue">
			<input type="text" name="welcomepack[email][]" value="<?php echo apply_filters( 'dpw_admin_settings_email', $emails[$id]['values'][0] ) ?>" />
		</div>
		<div style="clear: left"></div>
	</div>

	<?php for ( $i=1; $i<count( $emails[$id]['values'] ); $i++ ) : ?>
	<div class="setting wide">
		<div class="settingname"><p><?php _e( 'Text', 'dpw' ) ?></p></div>
		<div class="settingvalue">
			<textarea name="welcomepack[email][]"><?php echo apply_filters( 'dpw_admin_settings_email', $emails[$id]['values'][$i] ) ?></textarea>
		</div>
		<div style="clear: left"></div>
	</div>
	<?php endfor; ?>

	<div class="setting wide">
		<div class="settingname">
			<h5><?php _e( "For your information", 'dpw' ) ?></h5>
			<p><?php _e( "In the email text, there are often special placeholders. Typically, these are either %s or %d. The placeholders are replaced with another value when an email is about to be sent, such as person's name or a link to a web page.", 'dpw' ) ?></p>
			<p><?php _e( "To learn what these placeholders are replaced with, refer to an email which you've already been sent.", 'dpw' ) ?></p>
		</div>
		<div style="clear: left"></div>
	</div>
<?php
}
add_action( 'wp_ajax_dpw_fetch_email', 'dpw_fetch_email' );
?>