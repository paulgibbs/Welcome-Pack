<?php
/*
function bp_activity_at_message_notification( $content, $poster_user_id, $activity_id ) {
	global $bp;

	// Scan for @username strings in an activity update. Notify each user.
	$pattern = '/[@]+([A-Za-z0-9-_]+)/';
	preg_match_all( $pattern, $content, $usernames );

	// Make sure there's only one instance of each username 
	if ( !$usernames = array_unique( $usernames[1] ) )
		return false;

	foreach( (array)$usernames as $username ) {
		if ( !$receiver_user_id = bp_core_get_userid( $username ) )
			continue;

		// Now email the user with the contents of the message (if they have enabled email notifications)
		if ( 'no' != get_usermeta( $receiver_user_id, 'notification_activity_new_mention' ) ) {
			$poster_name = bp_core_get_user_displayname( $poster_user_id );

			$message_link = bp_activity_get_permalink( $activity_id );
			$settings_link = bp_core_get_user_domain( $receiver_user_id ) .  BP_SETTINGS_SLUG . '/notifications/';

			$poster_name = stripslashes( $poster_name );
			$content = bp_activity_filter_kses( stripslashes($content) );

			// Set up and send the message
			$ud = bp_core_get_core_userdata( $receiver_user_id );
			$to = $ud->user_email;
			$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( '%s mentioned you in an update', 'buddypress' ), $poster_name );

$message = sprintf( __(
'%s mentioned you in an update:

"%s"

To view and respond to the message, log in and visit: %s

---------------------
', 'buddypress' ), $poster_name, $content, $message_link );

			$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );

			// Send the message 
			$to = apply_filters( 'bp_activity_at_message_notification_to', $to );
			$subject = apply_filters( 'bp_activity_at_message_notification_subject', $subject, $poster_name );
			$message = apply_filters( 'bp_activity_at_message_notification_message', $message, $poster_name, $content, $message_link );

			wp_mail( $to, $subject, $message );
		}
	}
}
add_action( 'bp_activity_posted_update', 'bp_activity_at_message_notification', 10, 3 );
*/

// Notify user of signup success.
function dpw_bp_core_activation_signup_blog_notification( $domain, $path, $title, $user, $user_email, $key, $meta ) {
	global $current_site;

	// Send email with activation link.
	$activate_url = bp_get_activation_page() ."?key=$key";
	$activate_url = clean_url($activate_url);

	$admin_email = get_site_option( "admin_email" );

	if ( empty( $admin_email ) )
		$admin_email = 'support@' . $_SERVER['SERVER_NAME'];

	$from_name = ( '' == get_site_option( "site_name" ) ) ? 'WordPress' : wp_specialchars( get_site_option( "site_name" ) );
	$message_headers = "MIME-Version: 1.0\n" . "From: \"{$from_name}\" <{$admin_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
	$message = sprintf(__("Thanks for registering! To complete the activation of your account and blog, please click the following link:\n\n%s\n\n\n\nAfter you activate, you can visit your blog here:\n\n%s", 'buddypress' ), $activate_url, clean_url("http://{$domain}{$path}" ) );
	$subject = '[' . $from_name . '] ' . sprintf(__('Activate %s', 'buddypress' ), clean_url('http://' . $domain . $path));

	/* Send the message */
	$to = apply_filters( 'bp_core_activation_signup_blog_notification_to', $user_email );
	$subject = apply_filters( 'bp_core_activation_signup_blog_notification_subject', $subject );
	$message = apply_filters( 'bp_core_activation_signup_blog_notification_message', $message );

	wp_mail( $to, $subject, $message, $message_headers );

	// Return false to stop the original WPMU function from continuing
	return false;
}
if ( !is_admin() ) {
	remove_filter( 'wpmu_signup_blog_notification', 'bp_core_activation_signup_blog_notification', 1, 7 );
	add_filter( 'wpmu_signup_blog_notification', 'dpw_bp_core_activation_signup_blog_notification', 1, 7 );
}

function dpw_bp_core_activation_signup_user_notification( $user, $user_email, $key, $meta ) {
	global $current_site;

	$activate_url = bp_get_activation_page() ."?key=$key";
	$activate_url = clean_url($activate_url);
	$admin_email = get_site_option( "admin_email" );

	if ( empty( $admin_email ) )
		$admin_email = 'support@' . $_SERVER['SERVER_NAME'];

	/* If this is an admin generated activation, add a param to email the user login details */
	if ( is_admin() )
		$email = '&e=1';

	$from_name = ( '' == get_site_option( "site_name" ) ) ? 'WordPress' : wp_specialchars( get_site_option( "site_name" ) );
	$message_headers = "MIME-Version: 1.0\n" . "From: \"{$from_name}\" <{$admin_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
	$message = sprintf( __( "Thanks for registering! To complete the activation of your account please click the following link:\n\n%s\n\n", 'buddypress' ), $activate_url . $email, clean_url("http://{$domain}{$path}" ) );
	$subject = '[' . $from_name . '] ' . __( 'Activate Your Account', 'buddypress' );

	/* Send the message */
	$to = apply_filters( 'bp_core_activation_signup_user_notification_to', $user_email );
	$subject = apply_filters( 'bp_core_activation_signup_user_notification_subject', $subject );
	$message = apply_filters( 'bp_core_activation_signup_user_notification_message', $message );

	wp_mail( $to, $subject, $message, $message_headers );

	// Return false to stop the original WPMU function from continuing
	return false;
}
if ( !is_admin() || ( is_admin() && empty( $_POST['noconfirmation'] ) ) ) {
	remove_filter( 'wpmu_signup_user_notification', 'bp_core_activation_signup_user_notification', 1, 4 );
	add_filter( 'wpmu_signup_user_notification', 'dpw_bp_core_activation_signup_user_notification', 1, 4 );
}

/*
function groups_notification_promoted_member( $user_id, $group_id ) {
	global $bp;

	if ( groups_is_user_admin( $user_id, $group_id ) ) {
		$promoted_to = __( 'an administrator', 'buddypress' );
		$type = 'member_promoted_to_admin';
	} else {
		$promoted_to = __( 'a moderator', 'buddypress' );
		$type = 'member_promoted_to_mod';
	}

	// Post a screen notification first.
	bp_core_add_notification( $group_id, $user_id, 'groups', $type );

	if ( 'no' == get_usermeta( $user_id, 'notification_groups_admin_promotion' ) )
		return false;

	$group = new BP_Groups_Group( $group_id );
	$ud = bp_core_get_core_userdata($user_id);

	$group_link = bp_get_group_permalink( $group );
	$settings_link = bp_core_get_user_domain( $user_id ) .  BP_SETTINGS_SLUG . '/notifications/';

	// Set up and send the message
	$to = $ud->user_email;

	$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( 'You have been promoted in the group: "%s"', 'buddypress' ), $group->name );

	$message = sprintf( __(
'You have been promoted to %s for the group: "%s".

To view the group please visit: %s

---------------------
', 'buddypress' ), $promoted_to, $group->name, $group_link );

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );

	// Send the message
	$to = apply_filters( 'groups_notification_promoted_member_to', $to );
	$subject = apply_filters( 'groups_notification_promoted_member_subject', $subject, &$group );
	$message = apply_filters( 'groups_notification_promoted_member_message', $message, &$group, $promoted_to, $group_link );

	wp_mail( $to, $subject, $message );
}
add_action( 'groups_promoted_member', 'groups_notification_promoted_member', 10, 2 );
*/

/*
function groups_at_message_notification( $content, $poster_user_id, $group_id, $activity_id ) {
	global $bp;

	// Scan for @username strings in an activity update. Notify each user.
	$pattern = '/[@]+([A-Za-z0-9-_]+)/';
	preg_match_all( $pattern, $content, $usernames );

	// Make sure there's only one instance of each username
	if ( !$usernames = array_unique( $usernames[1] ) )
		return false;

	$group = new BP_Groups_Group( $group_id );

	foreach( (array)$usernames as $username ) {
		if ( !$receiver_user_id = bp_core_get_userid($username) )
			continue;

		// Check the user is a member of the group before sending the update.
		if ( !groups_is_user_member( $receiver_user_id, $group_id ) )
			continue;

		// Now email the user with the contents of the message (if they have enabled email notifications)
		if ( 'no' != get_usermeta( $user_id, 'notification_activity_new_mention' ) ) {
			$poster_name = bp_core_get_user_displayname( $poster_user_id );

			$message_link = bp_activity_get_permalink( $activity_id );
			$settings_link = bp_core_get_user_domain( $receiver_user_id ) .  BP_SETTINGS_SLUG . '/notifications/';

			$poster_name = stripslashes( $poster_name );
			$content = bp_groups_filter_kses( stripslashes( $content ) );

			// Set up and send the message
			$ud = bp_core_get_core_userdata( $receiver_user_id );
			$to = $ud->user_email;
			$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( '%s mentioned you in the group "%s"', 'buddypress' ), $poster_name, $group->name );

$message = sprintf( __(
'%s mentioned you in the group "%s":

"%s"

To view and respond to the message, log in and visit: %s

---------------------
', 'buddypress' ), $poster_name, $group->name, $content, $message_link );

			$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );

			// Send the message
			$to = apply_filters( 'groups_at_message_notification_to', $to );
			$subject = apply_filters( 'groups_at_message_notification_subject', $subject, &$group, $poster_name );
			$message = apply_filters( 'groups_at_message_notification_message', $message, &$group, $poster_name, $content, $message_link );

			wp_mail( $to, $subject, $message );
		}
	}
}
add_action( 'bp_groups_posted_update', 'groups_at_message_notification', 10, 4 );
*/
?>