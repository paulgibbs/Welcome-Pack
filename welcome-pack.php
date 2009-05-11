<?php
/*
Plugin Name: Welcome Pack
Author: DJPaul
Author URI: http://www.metabiscuits.com
Description: Provides default friend, default group and welcome message functionality to BuddyPress.
Version: 1.12
Site Wide Only: true
License: GNU General Public License 3.0 (GPL) http://www.gnu.org/licenses/gpl.html
Requires at least: WPMU 2.7.1, BuddyPress 1.0 RC-2
Tested up to: WPMU 2.7.1, BuddyPress 1.0 RC-2
*/

require_once( WP_PLUGIN_DIR . '/buddypress/bp-core.php' );


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
 * @uses dp_welcomepack_force_add_friend() Force-create a new friend relationship
 * @uses get_site_option() Selects a site setting from the DB.
 * @uses maybe_unserialize() Unserialize value only if it was serialized.
 * @uses Class WPDB Wordpress db object
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

	  dp_welcomepack_force_add_friend( $friend, $user_id );
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
 * @uses dp_welcomepack_force_join_group() Forces the new user to join a group
 * @uses get_site_option() Selects a site setting from the DB.
 * @uses maybe_unserialize() Unserialize value only if it was serialized.
 * @uses Class WPDB Wordpress db object
 */
function dp_welcomepack_defaultgroup( $user_id, $password, $meta ) {
	if ( !function_exists( 'groups_install' ) ) return;
	if ( 0 == (int) get_site_option( 'dp-welcomepack-group-enabled' ) ) return;

	$default_groups = maybe_unserialize( get_site_option( 'dp-welcomepack-group-id' ) );
	if ( empty( $default_groups ) ) return;
	if ( !is_array( $default_groups ) ) $default_groups = (array) $default_groups;

	global $wpdb, $bp;
	foreach ($default_groups as $group) {
		$sql = $wpdb->prepare( "SELECT * FROM {$bp->groups->table_name} WHERE id = %d", $group );
		if ( !$wpdb->get_row( $sql ) ) continue;

  	dp_welcomepack_force_join_group( $user_id, $group );
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
 * @uses attribute_escape() Escaping for HTML attributes.
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
						<option value="<?php echo attribute_escape( $user->user_id ); ?>"<?php echo( in_array( $user->user_id, $default_friends )  ? ' selected="selected"' : '' ); ?>><?php echo attribute_escape( $name ); ?></option>
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
						<option value="<?php echo attribute_escape( $group->group_id ); ?>"<?php echo( in_array( $group->group_id, $default_groups ) ? ' selected="selected"' : '' ); ?>><?php echo attribute_escape( $group->slug ); ?></option>
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
					<select name="dm_sender[]" id="dm_sender" style="height: auto;">
						<?php
						$users = BP_Core_User::get_alphabetical_users();
						$default_sender = get_site_option( 'dp-welcomepack-welcomemessage-sender' );
						if (!$default_sender) $default_sender = '';

						foreach ( (array) $users['users'] as $user ) { $name = bp_core_get_userlink( $user->user_id, true ); ?>
						<option value="<?php echo attribute_escape( $user->user_id ); ?>"<?php echo( ( $user->user_id == $default_sender ) ? ' selected="selected"' : '' ); ?>><?php echo attribute_escape( $name ); ?></option>
						<?php } ?>
					</select><br />
					<?php _e( 'The user account that the welcome message is sent from.', 'dp-welcomepack' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="dm_subject"><?php _e( 'Welcome message subject', 'dp-welcomepack' ) ?></label></th>
				<td>
					<input name="dm_subject" type="text" id="dm_subject" style="width: 95%" value="<?php echo attribute_escape( get_site_option( 'dp-welcomepack-welcomemessage-subject' ) ) ?>" size="45" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="dm_msg"><?php _e( 'Welcome message body', 'dp-welcomepack' ) ?></label></th>
				<td>
					<textarea name="dm_msg" id="dm_msg" rows="5" cols="45" style="width: 95%"><?php echo attribute_escape( get_site_option( 'dp-welcomepack-welcomemessage-msg' ) ) ?></textarea>
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

/**
 * dp_welcomepack_force_add_friend()
 *
 * Force-create a new friend relationship.
 *
 * @package Welcome Pack
 * @param $initiator_user_id The user ID of the person initiating the friend request
 * @param $friend_user_id The user ID of the new friend
 * @uses BP_Friends_Friendship Class Manages relationships
 * @uses friends_record_activity() Record relationship activity
 * @uses friends_update_friend_totals() Updates relationship meta
 * @uses do_action() Calls an action that triggers any registered filters
 * @return Boolean represent success or failure creating the new relationship
 */
function dp_welcomepack_force_add_friend( $initiator_user_id, $friend_user_id ) {
	if ( !function_exists( 'friends_install' ) )
		return false;

	$friendship = new BP_Friends_Friendship;	
	if ( (int)$friendship->is_confirmed )
		return true;

	$friendship->initiator_user_id = $initiator_user_id;
	$friendship->friend_user_id = $friend_user_id;
	$friendship->is_confirmed = 1;
	$friendship->is_limited = 0;
	$friendship->date_created = time();

	if ( !$friendship->save() )
		return false;

	/* Record this in activity streams */
	friends_record_activity( array( 'item_id' => $friendship->id, 'component_name' => 'friends', 'component_action' => 'friendship_accepted', 'is_private' => 0, 'user_id' => $friendship->initiator_user_id, 'secondary_user_id' => $friendship->friend_user_id ) );

	/* Modify relationship meta */
	friends_update_friend_totals( $friendship->initiator_user_id, $friendship->friend_user_id );
	
	do_action( 'friends_friendship_accepted', $friendship->id, $friendship->initiator_user_id, $friendship->friend_user_id );
	return true;
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

	messages_add_callback_values( $recipients, $subject, $content );

	$recipients = explode( ' ', $recipients );
	if ( count( $recipients ) < 1 ) {
		if ( !$from_ajax ) {	
/*			bp_core_add_message( __('Please enter at least one valid user to send this message to.', 'buddypress'), 'error' );
			bp_core_redirect( $from->domain . $bp->current_component . '/compose' );*/
		} else {
			return array('status' => 0, 'message' => __('There was an error sending the reply, please try again.', 'buddypress'));
		}
		
	// If there is only 1 recipient and it is the logged in user.
	} else if ( 1 == count( $recipients ) && $recipients[0] == $current_user->user_login ) {
/*		bp_core_add_message( __('You must send your message to one or more users not including yourself.', 'buddypress'), 'error' );
		bp_core_redirect( $from->domain . $bp->current_component . '/compose' );	*/
	
	// If the subject or content boxes are empty.
	} else if ( empty( $subject ) || empty( $content ) ) {
		if ( !$from_ajax ) {
/*			bp_core_add_message( __('Please make sure you fill in all the fields.', 'buddypress'), 'error' );
			bp_core_redirect( $from->domain . $bp->current_component . '/compose' );*/
		} else {
			return array('status' => 0, 'message' => __('Please make sure you have typed a message before sending a reply.', 'buddypress'));
		}
		
	// Passed validation continue.
	} else {
		// Strip the logged in user from the recipient list if they exist
/*		if ( $key = array_search( $current_user->user_login, $recipients ) )
			unset( $recipients[$key] );
*/

		$pmessage = new BP_Messages_Message;

		$pmessage->sender_id = $from->id;
		$pmessage->subject = $subject;
		$pmessage->message = $content;
		$pmessage->thread_id = $thread_id;
		$pmessage->date_sent = time();
		$pmessage->message_order = 0;
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
/*					bp_core_add_message( $message, $type );
					bp_core_redirect( $from->domain . $bp->current_component . '/compose' );*/
				} 
			} else {

				$message = __('Message sent successfully!', 'buddypress') . ' <a href="' . $from->domain . $bp->messages->slug . '/view/' . $pmessage->thread_id . '">' . __('View Message', 'buddypress') . '</a> &raquo;';
				$type = 'success';
				
				// Send screen notifications to the recipients
				for ( $i = 0; $i < count($pmessage->recipients); $i++ ) {
					if ( $pmessage->recipients[$i] != $from->id ) {
						bp_core_add_notification( $pmessage->id, $pmessage->recipients[$i], 'messages', 'new_message' );	
					}
				}

				// Send email notifications to the recipients
				require_once( WP_PLUGIN_DIR . '/buddypress/bp-messages/bp-messages-notifications.php' );
				
				messages_notification_new_message( array( 'item_id' => $pmessage->id, 'recipient_ids' => $pmessage->recipients, 'thread_id' => $pmessage->thread_id, 'component_name' => 'messages', 'component_action' => 'message_sent', 'is_private' => 1 ) );

				do_action( 'messages_send_message', array( 'item_id' => $pmessage->id, 'recipient_ids' => $pmessage->recipients, 'thread_id' => $pmessage->thread_id, 'component_name' => 'messages', 'component_action' => 'message_sent', 'is_private' => 1 ) );
		
				if ( $from_ajax ) {
					return array('status' => 1, 'message' => $message, 'reply' => $pmessage);
				} else {
/*					bp_core_add_message( $message );
					bp_core_redirect( $from->domain . $bp->current_component . '/inbox' );*/
				}
			}
		} else {
			$message = __('Message could not be sent, please try again.', 'buddypress');
			$type = 'error';
		
			if ( $from_ajax ) {
				return array('status' => 0, 'message' => $message);
			} else {
/*				bp_core_add_message( $message, $type );
				bp_core_redirect( $from->domain . $bp->messages->slug . '/compose' );*/
			}
		}
	}
}

/**
 * dp_welcomepack_force_join_group()
 *
 * Force-create a group membership.
 *
 * @package Welcome Pack
 * @param $user_id  The user ID to add to the group
 * @param $group_id The ID of the group to join
 * @uses BP_Groups_Member Class Manages group membership
 * @uses groups_record_activity() Record group activity
 * @uses groups_update_groupmeta() Updates group meta
 * @uses do_action() Calls an action that triggers any registered filters
 * @return Boolean represent success or failure creating the group membership
 */
function dp_welcomepack_force_join_group( $user_id, $group_id ) {
	global $bp;

	if ( !function_exists( 'groups_install' ) )
		return false;
	
	$new_member = new BP_Groups_Member;
	$new_member->group_id = $group_id;
	$new_member->user_id = $user_id;
	$new_member->inviter_id = 0;
	$new_member->is_admin = 0;
	$new_member->user_title = '';
	$new_member->date_modified = time();
	$new_member->is_confirmed = 1;

	if ( !$new_member->save() )
		return false;

	/* Record this in activity streams */
	groups_record_activity( array( 'item_id' => $new_member->group_id, 'component_name' => $bp->groups->slug, 'component_action' => 'joined_group', 'is_private' => 0, 'user_id' => $user_id ) );
	
	/* Modify group meta */
	groups_update_groupmeta( $group_id, 'total_member_count', (int) groups_get_groupmeta( $group_id, 'total_member_count') + 1 );
	groups_update_groupmeta( $group_id, 'last_activity', time() );

	do_action( 'groups_join_group', $group_id, $user_id );
	return true;
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

add_action( 'wpmu_activate_user', 'dp_welcomepack_defaultfriend', 1, 3 );
add_action( 'wpmu_activate_user', 'dp_welcomepack_defaultgroup', 1, 3 );
add_action( 'wpmu_activate_user', 'dp_welcomepack_welcomemessage', 1, 3 );
add_action( 'admin_menu', 'dp_welcomepack_menu' );

add_action( 'plugins_loaded', 'dp_welcomepack_setup_globals', 5 );	
add_action( 'admin_menu', 'dp_welcomepack_setup_globals', 1 );
?>