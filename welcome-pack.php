<?php
/*
Plugin Name: Welcome Pack
Author: DJPaul
Author URI: http://www.metabiscuits.com
Description: Provides default friend, default group and welcome mail functionality.
Plugin URI: http://svn.dangerous-minds.com/djpaul/welcome-pack/
Version: 0.2
Site Wide Only: true
License: ??? TODO ???

Requires WPMU >2.7 and BuddyPress >RC-1
*/

require_once(WP_CONTENT_DIR . '/mu-plugins/bp-core.php');

/**
 * dp_welcomepack_defaultfriend()
 *
 * Called by the wpmu_activate_user action when a new user activates their account (i.e. following the link in their email).
 *
 * @package Welcome Pack
 * @param $user_id The user ID of the new user
 * @param $password Password of the new user
 * @param $meta User meta
 * @uses dp_force_add_friend() Force-create a new friend relationship
 * @uses get_option() Selects a site setting from the DB.
 */
function dp_welcomepack_defaultfriend($user_id, $password, $meta) {
	if ( !function_exists( 'friends_install' ) )
		return;

	if ( 0 == (int) get_option( 'dp-welcomepack-friend-enabled' ) ) return;
  dp_force_add_friend( get_option( 'dp-welcomepack-friend-id' ), $user_id );
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
 * @uses dp_force_join_group() Forces the new user to join a group
 * @uses get_option() Selects a site setting from the DB.
 */
function dp_welcomepack_defaultgroup($user_id, $password, $meta) {
	if ( !function_exists( 'groups_install' ) )
		return;

	if ( 0 == (int) get_option( 'dp-welcomepack-group-enabled' ) || 0 == (int) get_option( 'dp-welcomepack-group-id' ) ) return;
  dp_force_join_group( $user_id, get_option( 'dp-welcomepack-group-id' ) );
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

  /* Add "Welcome Pack" under the "Site Admin" tab for site administrators */
	add_submenu_page( 'wpmu-admin.php', __( 'Welcome Pack', 'dp-welcomepack' ), __(' Welcome Pack', 'dp-welcomepack' ), 1, 'dp_welcomepack_settings', 'dp_welcomepack_admin' );
}

/**
 * dp_welcomepack_admin()
 * 
 * HTML for the admin settings page (default friend & default group).
 *
 * @package Welcome Pack
 * @uses check_admin_referer() Makes sure that a user was referred from another admin page
 * @uses update_option() Update the value of an option that was already added
 * @uses wp_nonce_field() Retrieve or display nonce hidden field for forms
 * @uses bp_core_get_userlink() Returns a HTML formatted link for a user with the user's full name as the link text
 * @uses BP_Core_User Class Fetches useful details for any user when provided with a user_id
 * @uses BP_Core_User::get_alphabetical_users() Return array of BP users sorted alphabetically
 * @uses BP_Groups_Group Class Fetches details for groups
 * @uses BP_Groups_Group:get_all() Returns array of BP groups sorted alphabetically
 */
function dp_welcomepack_admin() {
	if ( isset( $_POST['submit'] ) ) {
		check_admin_referer( 'dp-welcomepack' );

		if ( function_exists( 'friends_install' ) ) {
			update_option( 'dp-welcomepack-friend-id', (int) $_POST['df_id'] );
			update_option( 'dp-welcomepack-friend-enabled', (int) $_POST['df_enabled'] );
		}

		if ( function_exists( 'groups_install' ) ) {
			update_option( 'dp-welcomepack-group-id', (int) $_POST['dg_id'] );
			update_option( 'dp-welcomepack-group-enabled', (int) $_POST['dg_enabled'] );
		}

		echo '<div id="message" class="updated fade">Options updated.</div>';
	}
?>
<div class="wrap">
	<h2><?php _e(' Welcome Pack', 'dp-welcomepack' ) ?></h2>
	<br />

	<p><?php _e( 'Welcome Pack provides default friend, default group and welcome mail functionality to a Wordpress MU & Buddypress installation.', 'dp-welcomepack' ) ?></p>

	<form action="<?php echo site_url() . '/wp-admin/admin.php?page=dp_welcomepack_settings' ?>" name="welcomepack-form" id="welcomepack-form" method="post">

	<?php if ( function_exists( 'friends_install' ) ) { ?>
		<h3><?php _e( 'Default friend', 'dp-welcomepack' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="df_enabled"><?php _e( 'Default friend enabled', 'dp-welcomepack' ) ?></label></th>
				<td>
					<input name="df_enabled" type="checkbox" id="df_enabled" value="1"<?php echo( '1' == get_option( 'dp-welcomepack-friend-enabled' ) ? ' checked="checked"' : '' ); ?> />
					<?php _e( 'Turn on the default friend feature.', 'dp-welcomepack' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="df_id"><?php _e( 'Default friend', 'dp-welcomepack' ) ?></label></th>
				<td>
					<select name="df_id" id="df_id">
						<?php
						$users = BP_Core_User::get_alphabetical_users();
						foreach ( (array) $users['users'] as $user ) { $name = bp_core_get_userlink( $user->user_id, true ); ?>
						<option value="<?php echo attribute_escape( $user->user_id ); ?>"<?php echo( $user->user_id == get_option( 'dp-welcomepack-friend-id' ) ? ' selected="selected"' : '' ); ?>><?php echo $name; ?></option>
						<?php } ?>
					</select><br />
					<?php _e( "The user account that becomes a person's first friend.", 'dp-welcomepack' ); ?>
				</td>
			</tr>
		</table>
	<?php } ?>

	<?php if ( function_exists( 'groups_install' ) ) { ?>
		<h3><?php _e( 'Default group', 'dp-welcomepack' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="dg_enabled"><?php _e( 'Default group enabled', 'dp-welcomepack' ) ?></label></th>
				<td>
					<input name="dg_enabled" type="checkbox" id="dg_enabled" value="1"<?php echo( '1' == get_option( 'dp-welcomepack-group-enabled' ) ? ' checked="checked"' : '' ); ?> />
					<?php _e( 'Turn on the default group feature.', 'dp-welcomepack' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="dg_id"><?php _e( 'Default group', 'dp-welcomepack' ) ?></label></th>
				<td>
					<select name="dg_id" id="dg_id">
						<?php
						$groups = BP_Groups_Group::get_all();
						foreach ( (array) $groups as $group ) { ?>
						<option value="<?php echo attribute_escape( $group->group_id ); ?>"<?php echo( $group->group_id == get_option( 'dp-welcomepack-group-id' ) ? ' selected="selected"' : '' ); ?>><?php echo $group->slug; ?></option>
						<?php } ?>
					</select><br />
					<?php _e( "The group that a new user is joined to automatically.", 'dp-welcomepack' ); ?>
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
 * dp_force_add_friend()
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
function dp_force_add_friend( $initiator_user_id, $friend_user_id ) {
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
 * dp_force_join_group()
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
function dp_force_join_group( $user_id, $group_id ) {
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
	groups_record_activity( array( 'item_id' => $group_id, 'component_name' => 'groups', 'component_action' => 'joined_group', 'is_private' => 0 ) );
	
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
 * @uses get_option() Selects a site setting from the DB.
 * @uses update_option() Update the value of an option that was already added.
 */
function dp_welcomepack_setup_globals() {
	// Default friend
	if ( !get_option( 'dp-welcomepack-friend-id' ) )      { update_option( 'dp-welcomepack-friend-id', '1' ); }
	if ( !get_option( 'dp-welcomepack-friend-enabled' ) ) { update_option( 'dp-welcomepack-friend-enabled', '0' ); }

	// Default group
	if ( !get_option( 'dp-welcomepack-group-id' ) )      { update_option( 'dp-welcomepack-group-id', '0' ); }
	if ( !get_option( 'dp-welcomepack-group-enabled' ) ) { update_option( 'dp-welcomepack-group-enabled', '0' ); }
}

/**
 * dp_welcomepack_deactivate()
 *
 * Removes custom variables on plugin deactivation.
 *
 * @package Welcome Pack
 * @uses delete_option() Removes option by name and prevents removal of protected WordPress options.
 */
function dp_welcomepack_deactivate() {
	delete_option( 'dp-welcomepack-friend-id' );
	delete_option( 'dp-welcomepack-friend-enabled' );
	delete_option( 'dp-welcomepack-group-id' );
	delete_option( 'dp-welcomepack-group-enabled' );
}

/**
 * dp_welcomepack_activate()
 *
 * Adds custom variables on plugin activation.
 *
 * @package Welcome Pack
 * @uses dp_welcomepack_setup_globals() Set-up default values for options used by the plugins in case blog admins do not visit the admin panel.
 */
function dp_welcomepack_activate() {
	dp_welcomepack_setup_globals();
}

add_action( 'wpmu_activate_user', 'dp_welcomepack_defaultfriend', 1, 3 );
add_action( 'wpmu_activate_user', 'dp_welcomepack_defaultgroup', 1, 3 );
add_action( 'admin_menu', 'dp_welcomepack_menu' );

add_action( 'plugins_loaded', 'dp_welcomepack_setup_globals', 5 );	
add_action( 'admin_menu', 'dp_welcomepack_setup_globals', 1 );
register_activation_hook( __FILE__, 'dp_welcomepack_activate' );
register_deactivation_hook( __FILE__, 'dp_welcomepack_deactivate' );
?>