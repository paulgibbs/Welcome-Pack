<?php
/*
Plugin Name: Welcome Pack
Plugin URI: http://www.twitter.com/pgibbs
Author: DJPaul
Author URI: http://www.twitter.com/pgibbs
Description: Brings default friend, default group and welcome message functionality to BuddyPress.
Version: 1.41
Site Wide Only: true
License: General Public License version 3 
Requires at least: WPMU 2.8.1, BuddyPress 1.1
Tested up to: WPMU 2.8.4a, BuddyPress 1.1.1


"Welcome Pack" for BuddyPress
Copyright (C) 2009 Paul Gibbs

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License version 3 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/.


This code isn't written particulary well.  I'll tidy it up in a future release.  --DJPaul
*/
function dp_welcomepack_load_textdomain() {
	$locale = apply_filters( 'buddypress_locale', get_locale() );
	$mofile = WP_PLUGIN_DIR . "/welcome-pack/welcome-pack-$locale.mo";

	if ( file_exists( $mofile ) )
		load_textdomain( 'dp-welcomepack', $mofile );
}

/* Check that BuddyPress is loaded before Welcome Pack */
function dp_welcomepack_load_buddypress() {
	if ( function_exists( 'bp_core_setup_globals' ) )
		return true;

	/* Get the list of active sitewide plugins */
	$active_sitewide_plugins = maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) );

	if ( !isset( $active_sidewide_plugins['buddypress/bp-loader.php'] ) )
		return false;

	if ( isset( $active_sidewide_plugins['buddypress/bp-loader.php'] ) && !function_exists( 'bp_core_setup_globals' ) ) {
		require_once( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
		return true;
	}

	return false;
}

/**
 * dp_welcomepack_welcomemessage()
 *
 * Called by the wpmu_activate_user action when a new user activates their account (i.e. following the link in their email).
 *
 * @package Welcome Pack
 * @param $user_id The user ID of the new user
 * @param $password Password of the new user
 * @param $meta User meta
 * @uses get_site_option() Selects a site setting from the DB.
 * @uses get_userdata() Get user object from the DB.
 * @uses bp_core_get_user_domain() Returns 'http://www.example.com/members/USERNAME'.
 * @uses messages_new_message() Creates a new message.
 */
function dp_welcomepack_welcomemessage( $user_id, $password, $meta ) {
	if ( !function_exists( 'messages_install' ) ) return;
	if ( 0 == (int) get_site_option( 'dp-welcomepack-welcomemessage-enabled' ) ) return;

	$sender_id = get_site_option( 'dp-welcomepack-welcomemessage-sender' );
	$subject   = get_site_option( 'dp-welcomepack-welcomemessage-subject' );
	$content   = get_site_option( 'dp-welcomepack-welcomemessage-msg' );
	if ( empty( $subject ) || empty( $content ) || empty( $sender_id ) ) return;

	messages_new_message( array( 'sender_id' => $sender_id,
	                             'recipients' => array( $user_id ),
	                             'subject' => $subject,
	                             'content' => $content ) );
}

/**
 * dp_welcomepack_defaultfriend()
 *
 * Called by the wpmu_activate_user action when a new user activates their account (i.e. following the link in their email).
 *
 * @package Welcome Pack
 * @param $user_id The user ID of the new user
 * @param $password Password of the new user
 * @param $meta User meta
 * @uses friends_add_friend() Creates a new friend relationship
 * @uses get_site_option() Selects a site setting from the DB.
 * @uses maybe_unserialize() Unserialize value only if it was serialized.
 */
function dp_welcomepack_defaultfriend( $user_id, $password, $meta ) {
	if ( !function_exists( 'friends_install' ) ) return;
	if ( 0 == (int) get_site_option( 'dp-welcomepack-friend-enabled' ) ) return;

	$default_friends = maybe_unserialize( get_site_option( 'dp-welcomepack-friend-id' ) );
	if ( empty( $default_friends ) ) return;
	if ( !is_array( $default_friends ) ) $default_friends = (array) $default_friends;

	global $wpdb;
	foreach ( $default_friends as $friend ) {
		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->base_prefix}users WHERE id = %d", $friend );
		if ( !$wpdb->get_row( $sql ) ) continue;

	  friends_add_friend( $friend, $user_id );
	}
}

/**
 * dp_welcomepack_defaultgroup()
 *
 * Called by the wpmu_activate_user action when a new user activates their account (i.e. following the link in their email).
 *
 * @package Welcome Pack
 * @param $user_id The user ID of the new user
 * @param $password Password of the new user
 * @param $meta User meta
 * @uses groups_invite_user() Sends a group invitation to the specified user.
 * @uses get_site_option() Selects a site setting from the DB.
 * @uses maybe_unserialize() Unserialize value only if it was serialized.
 */
function dp_welcomepack_defaultgroup( $user_id, $password, $meta ) {
	if ( !function_exists( 'groups_install' ) ) return;
	if ( 0 == (int) get_site_option( 'dp-welcomepack-group-enabled' ) ) return;

	$default_groups = maybe_unserialize( get_site_option( 'dp-welcomepack-group-id' ) );
	if ( empty( $default_groups ) ) return;
	if ( !is_array( $default_groups ) ) $default_groups = (array) $default_groups;

	foreach ($default_groups as $group_id) {
		$group = new BP_Groups_Group( $group_id );
		groups_invite_user( array( 'user_id' => $user_id, 'group_id' => $group_id, 'inviter_id' => $group->creator_id ) );
		groups_send_invites( $group->creator_id, $group_id );
	}
}

/**
 * dp_welcomepack_menu()
 * 
 * Adds the "Welcome Pack" admin submenu item to the Site Admin tab, if the user is a site admin.
 *
 * @package Welcome Pack
 * @uses is_site_admin() returns true if the current user is a site admin, false if not
 * @uses add_submenu_page() WP function to add a submenu item
 */
function dp_welcomepack_menu() {
	if ( !is_site_admin() )
		return false;

  /* Add "Welcome Pack" under the "BuddyPress" tab for site administrators */
	add_submenu_page( 'bp-general-settings', __( 'Welcome Pack', 'dp-welcomepack' ), __(' Welcome Pack', 'dp-welcomepack' ), 'manage_options', 'dp_welcomepack', 'dp_welcomepack_admin' );
}

/**
 * dp_welcomepack_admin()
 * 
 * HTML for the admin settings page (default friend & default group).
 *
 * @package Welcome Pack
 * @uses check_admin_referer() Makes sure that a user was referred from another admin page
 * @uses update_site_option() Update the value of an option that was already added
 * @uses get_site_option() Selects a site setting from the DB.
 * @uses wp_nonce_field() Retrieve or display nonce hidden field for forms
 * @uses bp_core_get_userlink() Returns a HTML formatted link for a user with the user's full name as the link text
 * @uses BP_Core_User Class Fetches useful details for any user when provided with a user_id
 * @uses BP_Core_User::get_alphabetical_users() Return array of BP users sorted alphabetically
 * @uses BP_Groups_Group Class Fetches details for groups
 * @uses BP_Groups_Group:get_all() Returns array of BP groups sorted alphabetically
 * @uses maybe_unserialize() Unserialize value only if it was serialized.
 * @uses esc_attr_e() Escaping for HTML attributes.
 */
function dp_welcomepack_admin() {
	if ( isset( $_POST['submit'] ) ) {
		check_admin_referer( 'dp-welcomepack' );

		global $wpdb;
		if ( function_exists( 'friends_install' ) ) {

			foreach ( (array) $_POST['df_id'] as $key => $value) { $_POST[$key] = (int) $value; }	
			update_site_option( 'dp-welcomepack-friend-id', $_POST['df_id'] );
			update_site_option( 'dp-welcomepack-friend-enabled', (int) $_POST['df_enabled'] );
		}

		if ( function_exists( 'groups_install' ) ) {

			foreach ( (array) $_POST['dg_id'] as $key => $value) { $_POST[$key] = (int) $value; }	
			update_site_option( 'dp-welcomepack-group-id', $_POST['dg_id'] );
			update_site_option( 'dp-welcomepack-group-enabled', (int) $_POST['dg_enabled'] );
		}

		if ( function_exists( 'messages_install' ) ) {

			update_site_option( 'dp-welcomepack-welcomemessage-sender', (int) $_POST['dm_sender'] );
			update_site_option( 'dp-welcomepack-welcomemessage-subject', $_POST['dm_subject'] );
			update_site_option( 'dp-welcomepack-welcomemessage-msg', $_POST['dm_msg'] );
			update_site_option( 'dp-welcomepack-welcomemessage-enabled', (int) $_POST['dm_enabled'] );
		}

		echo "<div id='message' class='updated fade'><p>" . __( 'Options updated.', 'dp-welcomepack' ) . "</p></div>";
	}
?>
<div class="wrap">
	<h2><?php _e(' Welcome Pack', 'dp-welcomepack' ) ?></h2>
	<br />

	<p><?php _e( 'Welcome Pack provides default friend, default group and welcome message functionality to a Wordpress MU & Buddypress installation.', 'dp-welcomepack' ) ?></p>

	<form action="<?php echo site_url() . '/wp-admin/admin.php?page=dp_welcomepack' ?>" name="welcomepack-form" id="welcomepack-form" method="post">
	<?php wp_nonce_field( 'dp-welcomepack' ) ?>

	<?php if ( function_exists( 'friends_install' ) ) { ?>
		<h3><?php _e( 'Default friends', 'dp-welcomepack' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="df_enabled"><?php _e( 'Default friends enabled', 'dp-welcomepack' ) ?></label></th>
				<td>
					<input name="df_enabled" type="checkbox" id="df_enabled" value="1"<?php echo( '1' == get_site_option( 'dp-welcomepack-friend-enabled' ) ? ' checked="checked"' : '' ); ?> />
					<?php _e( 'Turn on the default friends feature.', 'dp-welcomepack' ); ?>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="df_id"><?php _e( 'Default friends', 'dp-welcomepack' ) ?></label></th>
				<td>
					<select name="df_id[]" id="df_id" multiple="multiple" size="8" style="height: auto;">
						<?php
						$users = BP_Core_User::get_alphabetical_users();
						$default_friends = get_site_option( 'dp-welcomepack-friend-id' );
						if (!$default_friends) $default_friends = array();

						foreach ( (array) $users['users'] as $user ) { $name = bp_core_get_userlink( $user->user_id, true ); ?>
						<option value="<?php esc_attr_e( $user->user_id ); ?>"<?php echo( in_array( $user->user_id, $default_friends )  ? ' selected="selected"' : '' ); ?>><?php esc_attr_e( $name ); ?></option>
						<?php } ?>
					</select><br />
					<?php _e( "The user accounts that become a person's first friends.", 'dp-welcomepack' ); ?>
				</td>
			</tr>
		</table>
	<?php } ?>

	<?php if ( function_exists( 'groups_install' ) ) { ?>
		<h3><?php _e( 'Default groups', 'dp-welcomepack' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="dg_enabled"><?php _e( 'Default groups enabled', 'dp-welcomepack' ) ?></label></th>
				<td>
					<input name="dg_enabled" type="checkbox" id="dg_enabled" value="1"<?php echo( '1' == get_site_option(  'dp-welcomepack-group-enabled' ) ? ' checked="checked"' : '' ); ?> />
					<?php _e( 'Turn on the default groups feature.', 'dp-welcomepack' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="dg_id"><?php _e( 'Default groups', 'dp-welcomepack' ) ?></label></th>
				<td>
					<select name="dg_id[]" id="dg_id" multiple="multiple" size="8" style="height: auto;">
						<?php
						$groups = BP_Groups_Group::get_all();
						$default_groups = get_site_option( 'dp-welcomepack-group-id' );
						if (!$default_groups) $default_groups = array();
						
						foreach ( (array) $groups as $group ) { ?>
						<option value="<?php esc_attr_e( $group->id ); ?>"<?php echo( in_array( $group->id, $default_groups ) ? ' selected="selected"' : '' ); ?>><?php esc_attr_e( $group->slug ); ?></option>
						<?php } ?>
					</select><br />
					<?php _e( "The groups that a new user is joined to automatically.", 'dp-welcomepack' ); ?>
				</td>
			</tr>
		</table>
	<?php } ?>
	
	<?php if ( function_exists( 'messages_install' ) ) { ?>
		<h3><?php _e( "Welcome message (sent using BuddyPress' message system)", 'dp-welcomepack' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="dm_enabled"><?php _e( 'Welcome message enabled', 'dp-welcomepack' ) ?></label></th>
				<td>
					<input name="dm_enabled" type="checkbox" id="dm_enabled" value="1"<?php echo( '1' == get_site_option(  'dp-welcomepack-welcomemessage-enabled' ) ? ' checked="checked"' : '' ); ?> />
					<?php _e( 'Turn on the welcome message feature.', 'dp-welcomepack' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="dm_sender"><?php _e( 'Welcome message sender', 'dp-welcomepack' ) ?></label></th>
				<td>
					<select name="dm_sender" id="dm_sender" style="height: auto;">
						<?php
						$users = BP_Core_User::get_alphabetical_users();
						$default_sender = get_site_option( 'dp-welcomepack-welcomemessage-sender' );
						if (!$default_sender) $default_sender = '';

						foreach ( (array) $users['users'] as $user ) { $name = bp_core_get_userlink( $user->user_id, true ); ?>
						<option value="<?php esc_attr_e( $user->user_id ); ?>"<?php echo( ( $user->user_id == $default_sender ) ? ' selected="selected"' : '' ); ?>><?php esc_attr_e( $name ); ?></option>
						<?php } ?>
					</select><br />
					<?php _e( 'The user account that the welcome message is sent from.', 'dp-welcomepack' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="dm_subject"><?php _e( 'Welcome message subject', 'dp-welcomepack' ) ?></label></th>
				<td>
					<input name="dm_subject" type="text" id="dm_subject" style="width: 95%" value="<?php esc_attr_e( get_site_option( 'dp-welcomepack-welcomemessage-subject' ) ) ?>" size="45" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="dm_msg"><?php _e( 'Welcome message body', 'dp-welcomepack' ) ?></label></th>
				<td>
					<textarea name="dm_msg" id="dm_msg" rows="5" cols="45" style="width: 95%"><?php esc_attr_e( get_site_option( 'dp-welcomepack-welcomemessage-msg' ) ) ?></textarea>
				</td>
			</tr>
		</table>
	<?php } ?>

	<p class="submit"><input type="submit" name="submit" value="<?php _e( 'Save Settings', 'buddypress' ) ?>"/></p>
	</forum>
</div>
<?php
}

/**
 * dp_welcomepack_setup_globals()
 *
 * Set-up default values for options used by the plugins in case blog admins do not visit the admin panel before someone triggers the functionality.
 *
 * @package Welcome Pack
 * @uses get_site_option() Selects a site setting from the DB.
 * @uses update_site_option() Update the value of an option that was already added.
 */
function dp_welcomepack_setup_globals() {
	// Default friend
	if ( !get_site_option( 'dp-welcomepack-friend-id' ) ) { update_site_option( 'dp-welcomepack-friend-id', array() ); }
	if ( !get_site_option( 'dp-welcomepack-friend-enabled' ) ) { update_site_option( 'dp-welcomepack-friend-enabled', 0 ); }

	// Default group
	if ( !get_site_option( 'dp-welcomepack-group-id' ) ) { update_site_option( 'dp-welcomepack-group-id', array() ); }
	if ( !get_site_option( 'dp-welcomepack-group-enabled' ) ) { update_site_option( 'dp-welcomepack-group-enabled', 0 ); }

	// Welcome message
	if ( !get_site_option( 'dp-welcomepack-welcomemessage-sender' ) ) { update_site_option( 'dp-welcomepack-welcomemessage-sender', 0 ); }
	if ( !get_site_option( 'dp-welcomepack-welcomemessage-subject' ) ) { update_site_option( 'dp-welcomepack-welcomemessage-subject', '' ); }
	if ( !get_site_option( 'dp-welcomepack-welcomemessage-msg' ) ) { update_site_option( 'dp-welcomepack-welcomemessage-msg', '' ); }
	if ( !get_site_option( 'dp-welcomepack-welcomemessage-enabled' ) ) { update_site_option( 'dp-welcomepack-welcomemessage-enabled', 0 ); }
}

/**
 * dp_welcomepack_onuserandblogregistration()
 *
 * Handles user + new blog registration.  These are handled differently by WPMU in wpmu_activate_signup().
 *
 * @package Welcome Pack
 * @uses dp_welcomepack_defaultfriend() Calls default friend routine.
 * @uses dp_welcomepack_defaultgroup() Calls default group routine.
 * @uses dp_welcomepack_welcomemessage() Calls welcome message routine.
 */
function dp_welcomepack_onuserandblogregistration( $blog_id, $user_id, $password, $signup_title, $meta ) {
	dp_welcomepack_defaultfriend( $user_id, $password, $meta );
	dp_welcomepack_defaultgroup( $user_id, $password, $meta );
	dp_welcomepack_welcomemessage( $user_id, $password, $meta );
}

add_action( 'wpmu_activate_user', 'dp_welcomepack_defaultfriend', 1, 3 );
add_action( 'wpmu_activate_user', 'dp_welcomepack_defaultgroup', 1, 3 );
add_action( 'wpmu_activate_user', 'dp_welcomepack_welcomemessage', 1, 3 );
add_action( 'bp_core_account_activated', 'dp_welcomepack_defaultfriend', 1, 3 );
add_action( 'bp_core_account_activated', 'dp_welcomepack_defaultgroup', 1, 3 );
add_action( 'bp_core_account_activated', 'dp_welcomepack_welcomemessage', 1, 3 );

add_action( 'wpmu_activate_blog', 'dp_welcomepack_onuserandblogregistration', 1, 5 );

add_action( 'plugins_loaded', 'dp_welcomepack_load_buddypress', 11 );
add_action( 'plugins_loaded', 'dp_welcomepack_load_textdomain', 19 );
add_action( 'plugins_loaded', 'dp_welcomepack_setup_globals', 15 );	
add_action( 'admin_menu', 'dp_welcomepack_menu' );
add_action( 'admin_menu', 'dp_welcomepack_setup_globals', 12 );
?>