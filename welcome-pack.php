<?php
/*
Plugin Name: Welcome Pack
Plugin URI: http://www.twitter.com/pgibbs
Author: DJPaul
Author URI: http://www.twitter.com/pgibbs
Description: When a user registers on your site, you may want to automatically send them a friend or group invitation, or a welcome message. This plugin lets you do that. If you want to customise the default emails that your site sends, well, we do that too.
Version: 2.0
License: General Public License version 3 
Requires at least: WP/MU 2.9, BuddyPress 1.2
Tested up to: WP/MU 2.9, BuddyPress 1.2


"Welcome Pack" for BuddyPress
Copyright (C) 2009-10 Paul Gibbs

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License version 3 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/.
*/
if ( defined( 'BP_VERSION' ) )
	dpw_buddypress_loaded();
else
	add_action( 'bp_init', 'dpw_buddypress_loaded' );

function dpw_buddypress_loaded() {
	add_action( 'plugins_loaded', 'dpw_load_textdomain' );

	// register_activation_hook isn't fired in MU
	if ( function_exists( 'wpmu_signup_blog' ) )
		dpw_activation_hook();

	add_action( 'user_register', 'dpw_new_user_registration_by_admin', 11 );
}

function dpw_activation_hook() {
	if ( '' != get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) )
		return;

	$default_settings = array( 'friends' => array(), 'groups' => array(), 'welcomemsgsubject' => '', 'welcomemsg' => '', 'welcomemsgsender' => 0, 'welcomemsgtoggle' => false, 'friendstoggle' => false, 'groupstoggle' => false );
	update_blog_option( BP_ROOT_BLOG, 'welcomepack', serialize( $default_settings ) );

	$emails = array();
	$emails['bp_activity_at_message_notification'] = array( 'subject' => '%s mentioned you in an update', 'message' => __( 
'%s mentioned you in an update:

"%s"

To view and respond to the message, log in and visit: %s

---------------------
', 'buddypress' ) );

	$emails['bp_activity_new_comment_notification-updates'] = array( 'subject' => '%s replied to one of your updates', 'message' => __( 
'%s replied to one of your updates:

"%s"

To view your original update and all comments, log in and visit: %s

---------------------
', 'buddypress' ) );

	$emails['bp_activity_new_comment_notification-comments'] = array( 'subject' => '%s replied to one of your comments', 'message' => __( 
'%s replied to one of your comments:

"%s"

To view the original activity, your comment and all replies, log in and visit: %s

---------------------
', 'buddypress' ) );

	$emails['bp_core_activation_signup_blog_notification'] = array( 'subject' => 'Activate %s', 'message' => __( "Thanks for registering! To complete the activation of your account and blog, please click the following link:\n\n%s\n\n\n\nAfter you activate, you can visit your blog here:\n\n%s", 'buddypress' ) );

	$emails['bp_core_activation_signup_user_notification'] = array( 'subject' => 'Activate Your Account', 'message' => __( "Thanks for registering! To complete the activation of your account please click the following link:\n\n%s\n\n", 'buddypress' ) );

	$emails['friends_notification_new_request'] = array( 'subject' => 'New friendship request from %s', 'message' => __( 
"%s wants to add you as a friend.

To view all of your pending friendship requests: %s

To view %s's profile: %s

---------------------
", 'buddypress' ) );

	$emails['friends_notification_accepted_request'] = array( 'subject' => '%s accepted your friendship request', 'message' => __( 
'%s accepted your friend request.

To view %s\'s profile: %s

---------------------
', 'buddypress' ) );

	$emails['messages_notification_new_message'] = array( 'subject' => 'New message from %s', 'message' => __( 
'%s sent you a new message:

Subject: %s

"%s"

To view and read your messages please log in and visit: %s

---------------------
', 'buddypress' ) );

	$emails['groups_notification_group_updated'] = array( 'subject' => 'Group Details Updated', 'message' => __( 
'Group details for the group "%s" were updated:

To view the group: %s

---------------------
', 'buddypress' ) );

	$emails['groups_notification_new_membership_request'] = array( 'subject' => 'Membership request for group: %s', 'message' => __( 
'%s wants to join the group "%s".

Because you are the administrator of this group, you must either accept or reject the membership request.

To view all pending membership requests for this group, please visit:
%s

To view %s\'s profile: %s

---------------------
', 'buddypress' ));

	$emails['groups_notification_membership_request_completed-accepted'] = array( 'subject' => 'Membership request for group "%s" accepted', 'message' => __( 
'Your membership request for the group "%s" has been accepted.

To view the group please login and visit: %s

---------------------
', 'buddypress' ) );

	$emails['groups_notification_membership_request_completed-rejected'] = array( 'subject' => 'Membership request for group "%s" rejected', 'message' => __( 
'Your membership request for the group "%s" has been rejected.

To submit another request please log in and visit: %s

---------------------
', 'buddypress' ) );

	$emails['groups_notification_promoted_member'] = array( 'subject' => 'You have been promoted in the group: "%s"', 'message' => __( 
'You have been promoted to %s for the group: "%s".

To view the group please visit: %s

---------------------
', 'buddypress' ) );

	$emails['groups_notification_group_invites'] = array( 'subject' => 'You have an invitation to the group: "%s"', 'message' => __( 
'One of your friends %s has invited you to the group: "%s".

To view your group invites visit: %s

To view the group visit: %s

To view %s\'s profile visit: %s

---------------------
', 'buddypress' ) );

	$emails['groups_at_message_notification'] = array( 'subject' => '%s mentioned you in the group "%s"', 'message' => __( 
'%s mentioned you in the group "%s":

"%s"

To view and respond to the message, log in and visit: %s

---------------------
', 'buddypress' ) );
	update_blog_option( BP_ROOT_BLOG, 'welcomepack_emails', serialize( $emails ) );
}
register_activation_hook( __FILE__, 'dpw_activation_hook' );

function dpw_deactivation_hook() {
	delete_blog_option( BP_ROOT_BLOG, 'welcomepack' );
	delete_blog_option( BP_ROOT_BLOG, 'welcomepack_emails' );
}
register_deactivation_hook( __FILE__, 'dpw_deactivation_hook' );


function dpw_load_textdomain() {
	$locale = apply_filters( 'buddypress_locale', get_locale() );
	$mofile = dirname( __FILE__ ) . "/i18n/$locale.mo";

	if ( file_exists( $mofile ) )
		load_textdomain( 'dpw', $mofile );
}


// *******************************************
// Admin screens
// *******************************************
function dpw_admin_add_css_js() {
	wp_enqueue_style( 'welcomepack', WP_PLUGIN_URL . '/welcome-pack/admin.css' );
 	wp_enqueue_script( 'welcomepack', WP_PLUGIN_URL . '/welcome-pack/admin.js', array( 'jquery' ) );
}
add_action( 'admin_print_styles-settings_page_welcome-pack', 'dpw_admin_add_css_js' );

function dpw_admin_menu() {
	if ( !is_site_admin() )
		return false;

	add_options_page( __( 'Welcome Pack settings', 'dpw' ), __( 'Welcome Pack', 'dpw' ), 'administrator', 'welcome-pack', 'dpw_admin_settings' );
	add_action( 'admin_init', 'dpw_admin_register_settings' );
}
add_action( 'admin_menu', 'dpw_admin_menu' );

function dpw_admin_register_settings() {
	register_setting( 'dpw-settings-group', 'welcomepack', 'dpw_admin_validate' );
	register_setting( 'dpw-emails-group', 'welcomepack_emails', 'dpw_admin_validate_emails' );
}

function dpw_admin_settings() {
	$settings = unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) );
	$emails = unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack_emails' ) );
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2><?php _e( 'Welcome Pack', 'dpw' ) ?></h2>
		<p><?php _e( 'When a user registers on your site, you may want to automatically send them a friend or group invitation, or a welcome message. This plugin lets you do that.', 'dpw' ) ?></p>
		<p><?php echo sprintf( __( "If you want to customise the default emails that your site sends, jump to the <a href='%s'>email customisation</a> section.", 'dpw' ), '#emails' ) ?></p>

		<h3><?php _e( 'Your Welcome Pack', 'dpw' ) ?></h3>
		<a name="welcomepack"></a>
		<form method="post" action="options.php" id="welcomepack">
			<?php settings_fields( 'dpw-settings-group' ) ?>

			<?php if ( function_exists( 'friends_install' ) ) : ?>	
				<div class="settingname">
					<p><?php _e( 'Invite the new user to become friends with these people:', 'dpw' ) ?></p>
					<?php dpw_admin_settings_toggle( 'friends', $settings ) ?>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_friends( $settings ) ?>
				</div>
				<div style="clear: left"></div>
			<?php endif ?>

			<?php if ( function_exists( 'groups_install' ) && ( bp_has_groups( 'type=alphabetically' ) ) ) : ?>
				<div class="settingname">
					<p><?php _e( 'Ask the new user if they\'d like to join these groups:', 'dpw' ) ?></p>
					<?php dpw_admin_settings_toggle( 'groups', $settings ) ?>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_groups( $settings ) ?>
				</div>
				<div style="clear: left"></div>
			<?php endif ?>

			<?php if ( function_exists( 'messages_install' ) ) : ?>
				<div class="settingname">
					<p><?php _e( 'Send the new user a welcome message&hellip;', 'dpw' ) ?></p>
					<?php dpw_admin_settings_toggle( 'welcomemsg', $settings ) ?>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_welcomemsg( $settings ) ?>
				</div>
				<div style="clear: left"></div>

				<div class="settingname">
					<p><?php _e( '&hellip;with this subject:', 'dpw' ) ?></p>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_welcomemsg_subject( $settings ) ?>
				</div>
				<div style="clear: left"></div>

				<div class="settingname">
					<p><?php _e( '&hellip;from this user:', 'dpw' ) ?></p>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_welcomemsg_sender( $settings ) ?>
				</div>
				<div style="clear: left"></div>
			<?php endif ?>

			<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Welcome Pack Settings', 'dpw' ) ?>"/></p>
		</forum>

		<h3><?php _e( 'Email Customisation', 'dpw' ) ?></h3>
		<a name="emails"></a>
		<p><?php _e( "Be careful not to add or remove any <em>%s</em> tags. These are replaced by customised pieces of data, such as a person's name or web link.  To find out what the tag replacements are, compare the contents of the email you are editing to the original.", 'dpw' ) ?></p>

		<div class="settingname">
			<p><?php _e( 'Pick an email, by its subject line, to customise:', 'dpw' ) ?></p>
			<form method="post" action="#"><?php wp_nonce_field( 'dpw_admin_settings', '_wpnonce-dpw-emails' ); ?></form>
		</div>
		<div class="settingvalue">
			<?php dpw_admin_emails_picker( $emails ) ?>
		</div>
		<div style="clear: left"></div>

		<form method="post" action="options.php" id="welcomepack_emails">
			<?php settings_fields( 'dpw-emails-group' ) ?>

			<div id="welcomepack_emails_details"></div>
		</form>
	</div>
<?php
}


// *******************************************
// User registration
// *******************************************
function dpw_new_user_registration_by_admin( $user_id ) {
	/* Only map data when the site admin is adding users, not on registration. */
	if ( !is_admin() )
		return false;

	dpw_new_user_registration( $user_id );
}

function dpw_new_user_registration( $signup, $key = null ) {
	$settings = unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) );

	if ( is_int( $signup ) )
		$new_user_id = $signup;
	else
		$new_user_id = $signup['user_id'];

	$test = $new_user_id;
	if ( $settings['friendstoggle'] && function_exists( 'friends_install' ) ) {

		$default_friends = $settings['friends'];
		foreach ( $default_friends as $friend_id ) {
			friends_add_friend( $friend_id, $new_user_id );
		}
	}

	if ( $settings['groupstoggle'] && function_exists( 'groups_install' ) ) {

		$default_groups = $settings['groups'];
		foreach ( $default_groups as $group_id ) {
			$group = new BP_Groups_Group( $group_id );
			groups_invite_user( array( 'user_id' => $new_user_id, 'group_id' => $group_id, 'inviter_id' => $group->creator_id ) );
			groups_send_invites( $group->creator_id, $group_id );
		}
	}

	if ( $settings['welcomemsgtoggle'] && function_exists( 'messages_install' ) ) {

		if ( empty( $settings['welcomemsgsender'] ) || empty( $settings['welcomemsgsubject'] ) || empty( $settings['welcomemsg'] ) )
			return;

		messages_new_message( array( 'sender_id' => $settings['welcomemsgsender'], 'recipients' => $new_user_id, 'subject' => $settings['welcomemsgsubject'], 'content' => $settings['welcomemsg'] ) );
	}
}
add_action( 'bp_core_account_activated', 'dpw_new_user_registration', 10, 2 );


// *******************************************
// Customised emails 
// *******************************************
function dpw_wp_mail( $email ) {  //'to', 'subject', 'message', 'headers', 'attachments'
	$bp_mail_functions = array( 'bp_activity_at_message_notification', 'bp_activity_new_comment_notification-updates', 'bp_activity_new_comment_notification-comments', 'bp_core_activation_signup_blog_notification', 'bp_core_activation_signup_user_notification', 'friends_notification_new_request', 'friends_notification_accepted_request', 'messages_notification_new_message', 'groups_notification_group_updated', 'groups_notification_new_membership_request', 'groups_notification_membership_request_completed', 'groups_notification_promoted_member', 'groups_notification_group_invites', 'groups_at_message_notification' );
	$caller_function = array_shift( debug_backtrace() );
	$caller_function_name = $caller_function['function'];

	if ( !in_array( $caller_function_name, $bp_mail_functions ) )
		return $email;

	if ( 'groups_notification_membership_request_completed' == $caller_function_name ) {  // Two calls to wp_mail in this function
		if ( $caller_function['args'][2] )
			$caller_function_name .= '-approved';
		else
			$caller_function_name .= '-rejected';
	}

		$caller_function = debug_backtrace();
	// /	$caller_function_name = $caller_function['function'];
		die(print_r($caller_function[5])); /* Array ( [function] => groups_setup_globals [args] => Array ( [0] => ) ) 1 */
}
//add_filter( 'wp_mail', 'dpw_wp_mail', 9, 1 );


// *******************************************
// Convenience functions for admin screen
// *******************************************
function dpw_admin_settings_friends( $settings ) {
	$friend_ids = $settings['friends'];
?>
	<p><select multiple="multiple" name="welcomepack[friends][]">
	<?php if ( bp_has_members( 'type=alphabetical&populate_extras=false' ) ) : while ( bp_members() ) : bp_the_member(); ?>
		<option value="<?php echo esc_attr( bp_get_member_user_id() ) ?>"<?php foreach ( $friend_ids as $id ) { if ( bp_get_member_user_id() == $id ) echo " selected='selected'"; } ?>><?php bp_member_name() ?></option>
	<?php endwhile; endif; ?>
	</select></p>
<?php
}

function dpw_admin_settings_groups( $settings ) {
	$group_ids = $settings['groups'];
?>
	<p><select multiple="multiple" name="welcomepack[groups][]">
	<?php while ( bp_groups() ) : bp_the_group(); ?>
		<option value="<?php echo esc_attr( bp_get_group_id() ) ?>"<?php foreach ( $group_ids as $id ) { if ( bp_get_group_id() == $id ) echo " selected='selected'"; } ?>><?php bp_group_name() ?></option>
	<?php endwhile; ?>
	</select></p>
<?php
}

function dpw_admin_settings_welcomemsg( $settings ) {
	$welcomemsg = esc_html( $settings['welcomemsg'] );
?>
	<textarea name="welcomepack[welcomemsg]"><?php echo $welcomemsg ?></textarea>
<?php
}

function dpw_admin_settings_welcomemsg_subject( $settings ) {
	$subject = esc_html( $settings['welcomemsgsubject'] );
?>
	<input type="text" name="welcomepack[welcomemsgsubject]" value="<?php echo $subject ?>" />
<?php
}

function dpw_admin_settings_welcomemsg_sender( $settings ) {
	$sender_id = $settings['welcomemsgsender'];
?>
	<p><select name="welcomepack[welcomemsgsender]">
	<?php if ( bp_has_members( 'type=alphabetical&populate_extras=false' ) ) : while ( bp_members() ) : bp_the_member(); ?>
		<option value="<?php echo esc_attr( bp_get_member_user_id() ) ?>"<?php if ( bp_get_member_user_id() == $sender_id ) echo " selected='selected'"; ?>><?php bp_member_name() ?></option>
	<?php endwhile; endif; ?>
	</select></p>
<?php
}

function dpw_admin_settings_toggle( $name, $settings ) {
	$checked = $settings[$name . 'toggle'];
?>
	<p><label for="<?php echo $name ?>"><?php _e( 'Enable', 'dpw' ) ?>&nbsp;<input type="checkbox" name=welcomepack[<?php echo $name ?>toggle] <?php if ( $checked ) echo 'checked="checked" ' ?>/></label></p>
<?php
}

function dpw_admin_emails_picker( $default_emails ) {
?>
	<p><select name="welcomepack_emails" id="welcomepack_emails_picker">
		<option value="" selected="selected">-----</option>
	<?php foreach ( $default_emails as $function_name => $details ) : ?>
			<option value="<?php echo esc_attr( $details['subject'] ) ?>"><?php echo esc_html( $details['subject'] ) ?></option>
	<?php endforeach; ?>
	</select></p>
<?php
}

function dpw_admin_emails( $subject ) {
	$emails = unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack_emails' ) );
	$subject = stripslashes( $subject );

	foreach ( $emails as $func_name => $email ) {
		if ( $subject == $email['subject'] ) {
			$content = esc_html( $email['message'] );
			break;
		}
	}

	if ( !isset( $content ) ) {
		echo '-1';
		return false;
	} ?>
	<textarea name="welcomepack_emails[<?php echo esc_attr( $func_name ) ?>]"><?php echo $content ?></textarea>
<?php
}

// *******************************************
// AJAX for admin screen
// *******************************************
function dpw_admin_emails_ajax() {
	/* Check the nonce */
	check_admin_referer( 'dpw_admin_settings' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( empty( $_POST['email_name'] ) ) {
		echo '-1';
		return false;
	} ?>
	<div class="settingvalue">
		<form method="post" action="#"><?php wp_nonce_field( 'dpw_admin_settings', '_wpnonce-dpw-emails' ); ?></form>
		<?php dpw_admin_emails( $_POST['email_name'] ) ?>
	</div>
	<div style="clear: left"></div>
	<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Email Customisation Settings', 'dpw' ) ?>"/></p>
<?php
}
add_action( 'wp_ajax_dpw_admin_emails_ajax', 'dpw_admin_emails_ajax' );


// *******************************************
// Validation functiona for register_setting
// *******************************************
function dpw_admin_validate_emails( $input ) {
	$emails = unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack_emails' ) );

	// Need to treat $input as an array otherwise no way of telling what email the new text belongs to.
	foreach ( $input as $key => $value ) {
		$emails[$key]['message'] = strip_tags( $value );
	}

	return serialize( $emails );
}

function dpw_admin_validate( $input ) {
	if ( isset( $input['friends'] ) )
		array_map( 'absint', &$input['friends'] );
	else
		$input['friends'] = array();

	if ( isset( $input['groups'] ) )
		array_map( 'absint', &$input['groups'] );
	else
		$input['groups'] = array();

	if ( isset( $input['welcomemsg'] ) )
		$input['welcomemsg'] = strip_tags( $input['welcomemsg'] );

	if ( isset( $input['welcomemsgsubject'] ) )
		$input['welcomemsgsubject'] = strip_tags( $input['welcomemsgsubject'] );

	if ( isset( $input['welcomemsgsender'] ) )
		$input['welcomemsgsender'] = absint( $input['welcomemsgsender'] );
 
	if ( isset( $input['groupstoggle'] ) )
		$input['groupstoggle'] = ( 'on' == $input['groupstoggle'] ) ? true : false;

	if ( isset( $input['friendstoggle'] ) )
		$input['friendstoggle'] = ( 'on' == $input['friendstoggle'] ) ? true : false;

	if ( isset( $input['welcomemsgtoggle'] ) )
		$input['welcomemsgtoggle'] = ( 'on' == $input['welcomemsgtoggle'] ) ? true : false;

	return serialize( $input );
}
?>