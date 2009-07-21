<?php
/*
Plugin Name: Welcome Pack
Plugin URI: http://www.twitter.com/pgibbs
Author: DJPaul
Author URI: http://www.twitter.com/pgibbs
Description: Provides default friend, default group and welcome message functionality to BuddyPress.
Version: 1.3
Site Wide Only: true
License: General Public License version 3 
Requires at least: WPMU 2.8.1, BuddyPress 1.0.2
Tested up to: WPMU 2.8.2, BuddyPress 1.0.3


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
*/
require_once( WP_PLUGIN_DIR . '/buddypress/bp-core.php' );
require_once( WP_PLUGIN_DIR . '/buddypress/bp-messages/bp-messages-classes.php' );


if ( file_exists( dirname(__FILE__) . '/welcome-pack-' . get_locale() . '.mo' ) )
load_textdomain( 'dp-welcomepack', dirname(__FILE__) . '/welcome-pack-' . get_locale() . '.mo' );

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
 * @uses dp_messages_send_message() Custom version of messages_send_message().
 */
function dp_welcomepack_welcomemessage( $user_id, $password, $meta ) {
	if ( !function_exists( 'messages_install' ) ) return;
	if ( 0 == (int) get_site_option( 'dp-welcomepack-welcomemessage-enabled' ) ) return;

	$sender_id = get_site_option( 'dp-welcomepack-welcomemessage-sender' );
	$subject   = get_site_option( 'dp-welcomepack-welcomemessage-subject' );
	$body      = get_site_option( 'dp-welcomepack-welcomemessage-msg' );
	if ( empty( $subject ) || empty( $body ) || empty( $sender_id ) ) return;

	$user_login = get_userdata( $user_id );
	$sender = (object) array( 'id' => $sender_id, 'domain' => bp_core_get_user_domain( $sender_id ) );
	dp_messages_send_message( $user_login->user_login, $subject, $body, $sender, false );
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
 * @uses Class WPDB Wordpress DB object
 */
function dp_welcomepack_defaultfriend( $user_id, $password, $meta ) {
	if ( !function_exists( 'friends_install' ) ) return;
	if ( 0 == (int) get_site_option( 'dp-welcomepack-friend-enabled' ) ) return;

	$default_friends = maybe_unserialize( get_site_option( 'dp-welcomepack-friend-id' ) );
	if ( empty( $default_friends ) ) return;
	if ( !is_array( $default_friends ) ) $default_friends = (array) $default_friends;

	global $wpdb;
	foreach ($default_friends as $friend) {
		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->base_prefix}users WHERE id = %d", $friend );
		if ( !$wpdb->get_row( $sql ) ) continue;

	  friends_add_friend( $friend, $user_id, true );
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
		dp_welcomepack_groups_invite_user( $user_id, $group_id );
	}
}

/* Copy of groups_invite_user as we can't specify the inviter_id yet - http://trac.buddypress.org/ticket/868. */
function dp_welcomepack_groups_invite_user( $user_id, $group_id ) {
	global $bp;
	
	if ( groups_is_user_member( $user_id, $group_id ) )
		return false;

	$group = new BP_Groups_Group( $group_id );

	$invite = new BP_Groups_Member;
	$invite->group_id = $group_id;
	$invite->user_id = $user_id;
	$invite->date_modified = time();
	$invite->inviter_id = $group->creator_id;  //changed
	$invite->is_confirmed = 0;
	
	if ( !$invite->save() )
		return false;
	
	dp_welcomepack_groups_send_invites( $group );  //added
	do_action( 'groups_invite_user', $group_id, $user_id );
		
	return true;
}

/* Copy of groups_send_invites as it is uses the logged in user id */
function dp_welcomepack_groups_send_invites( $group_obj, $deprecated = true ) {
	require_once ( BP_PLUGIN_DIR . '/bp-groups/bp-groups-notifications.php' );

	// Send friend invites.
	$invited_users = groups_get_invites_for_group( $group_obj->creator_id, $group_obj->id );
	
	for ( $i = 0; $i < count( $invited_users ); $i++ ) {
		$member = new BP_Groups_Member( $invited_users[$i], $group_obj->id );
		
		// Send the actual invite
		groups_notification_group_invites( $group_obj, $member, $group_obj->creator_id );
		
		$member->invite_sent = 1;
		$member->save();
	}
	
	do_action( 'groups_send_invites', $group_obj->id, $invited_users );
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
	add_submenu_page( 'bp-core.php', __( 'Welcome Pack', 'dp-welcomepack' ), __(' Welcome Pack', 'dp-welcomepack' ), 1, 'dp_welcomepack', 'dp_welcomepack_admin' );
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

			foreach ( (array) $_POST['df_id'] as $id ) { $id = (int) $id; }
			update_site_option( 'dp-welcomepack-friend-id', $_POST['df_id'] );
			update_site_option( 'dp-welcomepack-friend-enabled', (int) $_POST['df_enabled'] );
		}

		if ( function_exists( 'groups_install' ) ) {

			foreach ( (array) $_POST['dg_id'] as $id ) { $id = (int) $id; }
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
					<select name="dg_id[]" id="dg_id" multiple="multiple" style="height: auto;">
						<?php
						$groups = BP_Groups_Group::get_all();
						$default_groups = get_site_option( 'dp-welcomepack-group-id' );
						if (!$default_groups) $default_groups = array();
						
						foreach ( (array) $groups as $group ) { ?>
						<option value="<?php esc_attr_e( $group->group_id ); ?>"<?php echo( in_array( $group->group_id, $default_groups ) ? ' selected="selected"' : '' ); ?>><?php esc_attr_e( $group->slug ); ?></option>
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
	<?php wp_nonce_field( 'dp-welcomepack' ) ?>
	</forum>
</div>
<?php
}

class DP_WelcomePack_BP_Messages_Message extends BP_Messages_Message {
	function send() {
		global $wpdb, $bp;
		
		$this->sender_id = apply_filters( 'messages_message_sender_id_before_save', $this->sender_id, $this->id );
		$this->subject = apply_filters( 'messages_message_subject_before_save', $this->subject, $this->id );
		$this->message = apply_filters( 'messages_message_content_before_save', $this->message, $this->id );
		$this->date_sent = apply_filters( 'messages_message_date_sent_before_save', $this->date_sent, $this->id ); 
		$this->message_order = apply_filters( 'messages_message_order_before_save', $this->message_order, $this->id ); 
		$this->sender_is_group = apply_filters( 'messages_message_sender_is_group_before_save', $this->sender_is_group, $this->id );

		do_action( 'messages_message_before_save', $this );
		
		// First insert the message into the messages table
		if ( !$wpdb->query( $wpdb->prepare( "INSERT INTO {$bp->messages->table_name_messages} ( sender_id, subject, message, date_sent, message_order, sender_is_group ) VALUES ( %d, %s, %s, FROM_UNIXTIME(%d), %d, %d )", $this->sender_id, $this->subject, $this->message, $this->date_sent, $this->message_order, $this->sender_is_group ) ) )
			return false;
			
		// Next, if thread_id is set, we are adding to an existing thread, if not, start a new one.
		if ( $this->thread_id ) {
			// Select and update the current message ids for the thread.
			$the_ids = $wpdb->get_row( $wpdb->prepare( "SELECT message_ids, sender_ids FROM {$bp->messages->table_name_threads} WHERE id = %d", $this->thread_id ) );
			$message_ids = unserialize($the_ids->message_ids);
			$message_ids[] = $wpdb->insert_id;
			$message_ids = serialize($message_ids);
			
			// We need this so we can return the new message ID.
			$message_id = $wpdb->insert_id;
			
			// Update the sender ids for the thread
			$sender_ids = unserialize($the_ids->sender_ids);

			if ( !in_array( $this->sender_id, $sender_ids ) || !$sender_ids )
				$sender_ids[] = $this->sender_id;
				
			$sender_ids = serialize($sender_ids);			
			
			// Update the thread the message belongs to.
			$wpdb->query( $wpdb->prepare( "UPDATE {$bp->messages->table_name_threads} SET message_ids = %s, sender_ids = %s, last_message_id = %d, last_sender_id = %d, last_post_date = FROM_UNIXTIME(%d) WHERE id = %d", $message_ids, $sender_ids, $wpdb->insert_id, $this->sender_id, $this->date_sent, $this->thread_id ) );
						
			// Find the recipients and update the unread counts for each
			if ( !$this->recipients )
				$this->recipients = $this->get_recipients();
			
			for ( $i = 0; $i < count($this->recipients); $i++ ) {
				if ( $this->recipients[$i]->user_id != $bp->loggedin_user->id )
					$wpdb->query( $wpdb->prepare( "UPDATE {$bp->messages->table_name_recipients} SET unread_count = unread_count + 1, sender_only = 0 WHERE thread_id = %d AND user_id = %d", $this->thread_id, $this->recipients[$i] ) );
			}
		} else {
			// Create a new thread.
			$message_id = $wpdb->insert_id;
			$serialized_message_id = serialize( array( (int)$message_id ) );
			$serialized_sender_id = serialize( array( (int)$bp->loggedin_user->id ) );
			
			$sql = $wpdb->prepare( "INSERT INTO {$bp->messages->table_name_threads} ( message_ids, sender_ids, first_post_date, last_post_date, last_message_id, last_sender_id ) VALUES ( %s, %s, FROM_UNIXTIME(%d), FROM_UNIXTIME(%d), %d, %d )", $serialized_message_id, $serialized_sender_id, $this->date_sent, $this->date_sent, $message_id, $this->sender_id ); 
			
			if ( false === $wpdb->query($sql) )
				return false;
			

			$this->thread_id = $wpdb->insert_id;
			
			// Add a new entry for each recipient;
			for ( $i = 0; $i < count($this->recipients); $i++ ) {
				$wpdb->query( $wpdb->prepare( "INSERT INTO {$bp->messages->table_name_recipients} ( user_id, thread_id, unread_count ) VALUES ( %d, %d, 1 )", $this->recipients[$i], $this->thread_id ) );
			}
			
			if ( !in_array( $this->sender_id, $this->recipients ) ) {
				// Finally, add a recipient entry for the sender, as replies need to go to this person too.
				$wpdb->query( $wpdb->prepare( "INSERT INTO {$bp->messages->table_name_recipients} ( user_id, thread_id, unread_count, sender_only ) VALUES ( %d, %d, 0, 0 )", $this->sender_id, $this->thread_id ) );
			}
		}
		
		$this->id = $message_id;
		//messages_remove_callback_values();

		do_action( 'messages_message_after_save', $this );
		
		return true;
	}
}

/**
 * dp_messages_send_message().
 *
 * Not documented as expect to use a core version of this function in future - http://trac.buddypress.org/ticket/673.
 */
function dp_messages_send_message( $recipients, $subject, $content, $from, $thread_id, $from_ajax = false, $from_template = false, $is_reply = false ) {
	global $pmessage;
	global $message, $type;
	global $bp, $current_user;
		
	$recipients = explode( ' ', $recipients );
	
	if (true) {

		// Strip the logged in user from the recipient list if they exist
		if ( $key = array_search( $current_user->user_login, $recipients ) )
			unset( $recipients[$key] );
		
		$pmessage = new DP_WelcomePack_BP_Messages_Message;

		$pmessage->sender_id = $from->id;
		$pmessage->subject = $subject;
		$pmessage->message = $content;
		$pmessage->thread_id = $thread_id;
		$pmessage->date_sent = time();
		$pmessage->message_order = 0; // TODO
		$pmessage->sender_is_group = 0;
		
		if ( $is_reply ) {
			$thread = new BP_Messages_Thread($thread_id);
			$pmessage->recipients = $thread->get_recipients();
		} else {
			$pmessage->recipients = BP_Messages_Message::get_recipient_ids( $recipients );
		}

		if ( !is_null( $pmessage->recipients ) ) {
			if ( !$pmessage->send() ) {
				$message = __('Message could not be sent, please try again.', 'buddypress');
				$type = 'error';
		
				if ( $from_ajax ) {
					return array('status' => 0, 'message' => $message);
				} else {
					bp_core_add_message( $message, $type );
					bp_core_redirect( $bp->loggedin_user->domain . $bp->current_component . '/compose' );
				} 
			} else {
				$message = __('Message sent successfully!', 'buddypress') . ' <a href="' . $bp->loggedin_user->domain . $bp->messages->slug . '/view/' . $pmessage->thread_id . '">' . __('View Message', 'buddypress') . '</a> &raquo;';
				$type = 'success';
				
				// Send screen notifications to the recipients
				for ( $i = 0; $i < count($pmessage->recipients); $i++ ) {
					if ( $pmessage->recipients[$i] != $bp->loggedin_user->id ) {
						bp_core_add_notification( $pmessage->id, $pmessage->recipients[$i], 'messages', 'new_message' );	
					}
				}
				
				// Send email notifications to the recipients
				require_once( BP_PLUGIN_DIR . '/bp-messages/bp-messages-notifications.php' );
				messages_notification_new_message( array( 'item_id' => $pmessage->id, 'recipient_ids' => $pmessage->recipients, 'thread_id' => $pmessage->thread_id, 'component_name' => $bp->messages->slug, 'component_action' => 'message_sent', 'is_private' => 1 ) );

				do_action( 'messages_send_message', array( 'item_id' => $pmessage->id, 'recipient_ids' => $pmessage->recipients, 'thread_id' => $pmessage->thread_id, 'component_name' => $bp->messages->slug, 'component_action' => 'message_sent', 'is_private' => 1 ) );
		
				if ( $from_ajax ) {
					return array('status' => 1, 'message' => $message, 'reply' => $pmessage);
				} else {
/*					bp_core_add_message( $message );
					bp_core_redirect( $bp->loggedin_user->domain . $bp->current_component . '/inbox' );*/
				}
			}
		} else {
			$message = __('Message could not be sent, please try again.', 'buddypress');
			$type = 'error';
		
			if ( $from_ajax ) {
				return array('status' => 0, 'message' => $message);
			} else {
/*				bp_core_add_message( $message, $type );
				bp_core_redirect( $bp->loggedin_user->domain . $bp->messages->slug . '/compose' );*/
			}
		}
	}
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
add_action( 'wpmu_activate_blog', 'dp_welcomepack_onuserandblogregistration', 1, 5 );
add_action( 'admin_menu', 'dp_welcomepack_menu' );

add_action( 'plugins_loaded', 'dp_welcomepack_setup_globals', 5 );	
add_action( 'admin_menu', 'dp_welcomepack_setup_globals', 1 );
?>