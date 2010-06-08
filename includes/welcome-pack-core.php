<?php
define( 'WELCOME_PACK_IS_INSTALLED', 1 );

if ( !defined( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) )
	define( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS', false );

load_plugin_textdomain( 'dpw', false, '/welcome-pack/includes/languages/' );

/* The ajax file should hold all functions used in AJAX queries */
require ( dirname( __FILE__ ) . '/welcome-pack-ajax.php' );

/* The cssjs file should set up and enqueue all CSS and JS files used by the component */
require ( dirname( __FILE__ ) . '/welcome-pack-cssjs.php' );

/* The filters file should create and apply filters to component output functions. */
require( dirname( __FILE__ ) . '/welcome-pack-filters.php' );


function dpw_add_admin_menu() {
	global $bp;

	if ( !$bp->loggedin_user->is_site_admin )
		return false;

	require ( dirname( __FILE__ ) . '/welcome-pack-admin.php' );

	add_submenu_page( 'bp-general-settings',__( 'Welcome Pack', 'dpw' ), __( 'Welcome Pack', 'dpw' ), 'manage_options', 'welcome-pack', 'dpw_admin_screen' );
	add_action( 'load-buddypress_page_welcome-pack', 'dpw_admin_screen_on_load' );
	add_action( 'admin_init', 'dpw_admin_register_settings' );
}
add_action( 'admin_menu', 'dpw_add_admin_menu' );

function dpw_on_user_registration( $user_id ) {
	$settings = maybe_unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) );

	if ( $settings['friendstoggle'] && function_exists( 'friends_install' ) )
		foreach ( $settings['friends'] as $friend_id )
			friends_add_friend( $friend_id, $user_id, constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) );

	if ( $settings['groupstoggle'] && function_exists( 'groups_install' ) ) {
		foreach ( $settings['groups'] as $group_id ) {
			$group = new BP_Groups_Group( $group_id );
			groups_invite_user( array( 'user_id' => $user_id, 'group_id' => $group_id, 'inviter_id' => $group->creator_id, 'is_confirmed' => constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) ) );
			groups_send_invites( $group->creator_id, $group_id );
		}
	}

	if ( $settings['welcomemsgtoggle'] && function_exists( 'messages_install' ) ) {
		if ( !$settings['welcomemsgsender'] || !$settings['welcomemsgsubject'] || !$settings['welcomemsg'] )
			return;

		messages_new_message( array( 'sender_id' => $settings['welcomemsgsender'], 'recipients' => $user_id, 'subject' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsgsubject'], $user_id ), 'content' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsg'], $user_id ) ) );
	}

	if ( $settings['startpagetoggle'] )
		update_usermeta( $user_id, 'welcomepack_firstlogin', true );
}
add_action( 'bp_core_activated_user', 'dpw_on_user_registration' );

function dpw_first_login_redirect( $redirect_to, $notused, $WP_User ) {
	$settings = maybe_unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) );
	if ( !$settings['startpagetoggle'] || !$settings['firstloginurl'] )
		return $redirect_to;

	if ( is_wp_error( $WP_User ) )
		return $redirect_to;

	$user_id = $WP_User->ID;
	if ( !get_usermeta( $user_id, 'welcomepack_firstlogin', true ) )
		return $redirect_to;

	delete_usermeta( $user_id, 'welcomepack_firstlogin' );
	return esc_url( $settings['firstloginurl'] );
}
add_filter( 'login_redirect', 'dpw_first_login_redirect', 15, 3 );

function dpw_do_keyword_replacement( $text, $user_id ) {
	$text = str_replace( "USERNAME", bp_core_get_username( $user_id ), $text );
	$text = str_replace( "NICKNAME", bp_core_get_user_displayname( $user_id ), $text );
	$text = str_replace( "USER_URL", bp_core_get_user_domain( $user_id ), $text );

	return $text;
}
add_filter( 'dpw_keyword_replacement', 'dpw_do_keyword_replacement', 10, 2 );

function dpw_load_dynamic_i18n() {
	global $l10n;

	$emails = maybe_unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) );
	if ( !$emails['emailstoggle'] )
		return;

	$emails = $emails['emails'];
	$defaults = dpw_get_default_email_data();

	for ( $i=1; $i<count( $emails ); $i++ ) {
		for ( $j=0; $j<count( $defaults[$i]['values'] ); $j++ ) {
			if ( $defaults[$i]['values'][$j] != $emails[$i]['values'][$j] ) {

				if ( isset( $l10n['buddypress']->entries[$defaults[$i]['id'][$j]] ) ) {
					$l10n['buddypress']->entries[$defaults[$i]['id'][$j]]->translations[0] = $emails[$i]['values'][$j];

				} else {
					$mo = new MO();
					$mo->add_entry( array( 'singular' => $defaults[$i]['id'][$j], 'translations' => array( $emails[$i]['values'][$j] ) ) );

					if ( isset( $l10n['buddypress'] ) )
						$mo->merge_with( $l10n['buddypress'] );

					$l10n['buddypress'] = &$mo;
					unset( $mo );
				}

			}
		}
	}
}
add_action( 'init', 'dpw_load_dynamic_i18n', 9 );

function dpw_get_default_email_data() {
	/* Translators: some of these strings below intentionally use the BuddyPress textdomain. */
	$emails = array(
		array( 'name' => '----', 'values' => array() ),
		array( 'name' => __( 'Signup validation', 'dpw' ), 'values' => array( 'Activate Your Account', "Thanks for registering! To complete the activation of your account please click the following link:\n\n%s\n\n" ) ),
		array( 'name' => __( 'New message notification', 'dpw' ), 'values' => array( 'New message from %s', '%s sent you a new message:

Subject: %s

"%s"

To view and read your messages please log in and visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'Group details updated', 'dpw' ), 'values' => array( 'Group Details Updated', 'Group details for the group "%s" were updated:

To view the group: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'New group membership request', 'dpw' ), 'values' => array( 'Membership request for group: %s', '%s wants to join the group "%s".

Because you are the administrator of this group, you must either accept or reject the membership request.

To view all pending membership requests for this group, please visit:
%s

To view %s\'s profile: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'Group membership request accepted', 'dpw' ), 'values' => array( 'Membership request for group "%s" accepted', 'Your membership request for the group "%s" has been accepted.

To view the group please login and visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'Group membership request rejected', 'dpw' ), 'values' => array( 'Membership request for group "%s" rejected', 'Your membership request for the group "%s" has been rejected.

To submit another request please log in and visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'Group promotion', 'dpw' ), 'values' => array( 'You have been promoted in the group: "%s"', 'You have been promoted to %s for the group: "%s".

To view the group please visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s', 'an administrator', 'a moderator' ) ),
		array( 'name' => __( 'Group invitation received', 'dpw' ), 'values' => array( 'You have an invitation to the group: "%s"', 'One of your friends %s has invited you to the group: "%s".

To view your group invites visit: %s

To view the group visit: %s

To view %s\'s profile visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'Mentioned in a group', 'dpw' ), 'values' => array( '%s mentioned you in the group "%s"', '%s mentioned you in the group "%s":

"%s"

To view and respond to the message, log in and visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'Friendship accepted notification', 'dpw' ), 'values' => array( '%s accepted your friendship request', '%s accepted your friend request.

To view %s\'s profile: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'New friendship request', 'dpw' ), 'values' => array( 'New friendship request from %s', "%s wants to add you as a friend.

To view all of your pending friendship requests: %s

To view %s's profile: %s

---------------------
", 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'Mentioned in an update', 'dpw' ), 'values' => array( '%s mentioned you in an update', '%s mentioned you in an update:

"%s"

To view and respond to the message, log in and visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'Replied to one of your updates', 'dpw' ), 'values' => array( '%s replied to one of your updates', '%s replied to one of your updates:

"%s"

To view your original update and all comments, log in and visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) ),
		array( 'name' => __( 'Replied to one of your comments', 'dpw' ), 'values' => array( '%s replied to one of your comments', '%s replied to one of your comments:

"%s"

To view the original activity, your comment and all replies, log in and visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ) )
	);

	for ( $j=1; $j<count( $emails ); $j++ ) {
		$emails[$j]['id'] = $emails[$j]['values'];

		for ( $i=0; $i<count( $emails[$j]['values'] ); $i++ )	
			$emails[$j]['values'][$i] = __( $emails[$j]['values'][$i], 'buddypress' );
	}

	return $emails;
}
?>