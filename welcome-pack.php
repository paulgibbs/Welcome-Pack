<?php
/*
Plugin Name: Welcome Pack
Author: DJPaul
Author URI: djpaul@gmail.com
Description: Automatically add a specified user as friend after SignUp
Plugin URI: http://svn.dangerous-minds.com/djpaul/welcome-pack/
Version: 0.1
Site Wide Only: true

Provides default friend, default group and welcome mail functionality.
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
  dp_force_add_friend( get_option( 'dp-welcomepack-friend-id' ), user_id );
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
	add_submenu_page( 'wpmu-admin.php', __( 'Welcome Pack', 'dp-welcomepack' ), __(' Welcome Pack', 'dp-welcomepack' ), 1, 'dp_welcomepack', 'dp_welcomepack_admin' );
}

/**
 * dp_welcomepack_admin()
 * 
 * HTML for the admin settings page.
 *
 * @package Welcome Pack
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @uses check_admin_referer() Makes sure that a user was referred from another admin page
 * @uses update_option() Update the value of an option that was already added
 * @uses wp_nonce_field() Retrieve or display nonce hidden field for forms
 * @uses bp_core_get_userlink() Returns a HTML formatted link for a user with the user's full name as the link text
 * @uses BP_Core_User Class Fetches useful details for any user when provided with a user_id.
 * @uses BP_Core_User::get_alphabetical_users() Return list of BP users sorted alphabetically.
 */
function dp_welcomepack_admin() {
	global $bp;

	if ( isset($_POST['submit']) ) {
		check_admin_referer( 'dp-welcomepack' );

		update_option( 'dp-welcomepack-friend-id', (int) $_POST['df_id'] );
		echo "<div id=\"message\" class=\"updated fade\"><strong>Options updated.</strong></div>";
	}
?>
<div class="wrap">
	<h2><?php _e(' Welcome Pack', 'dp-welcomepack' ) ?></h2>
	<br />

	<p><?php _e( 'Welcome Pack provides default friend, default group and welcome mail functionality to a Wordpress MU & Buddypress installation.', 'dp-welcomepack' ) ?></p>
	
	<forum action="<?php echo $bp->root_domain . '/wp-admin/admin.php?page=dp_welcomepack' ?>" name="welcomepack-form" id="welcomepack-form" method="post">

		<h3><?php _e( 'Default friend', 'dp-welcomepack' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="df_enabled"><?php _e( 'Default friend enabled', 'dp-welcomepack' ) ?></label></th>
				<td>
					<input name="df_enabled" type="checkbox" id="df_enabled" value="1"<?php echo( $options['df_enabled'] ? ' checked="checked"' : '' ); ?> />
					<?php _e( 'Turn on the default friend feature.', 'buddypress' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="df_id"><?php _e( 'Default friend', 'dp-welcomepack' ) ?></label></th>
				<td>
					<select name="df_id" id="df_id">
						<?php
						$users = BP_Core_User::get_alphabetical_users();
						foreach ( (array) $users['users'] as $user ) { $name = bp_core_get_userlink( $user->user_id, true ); ?>
						<option value="<?php echo $name; ?>" <?php echo( $user->user_id == get_option( 'dp-welcomepack-friend-id' ) ? ' checked="checked"' : '' ); ?>><?php echo $name; ?></option>
						<?php } ?>
					</select><br />
					<?php _e( "The user account that becomes a person's first friend.", 'dp-welcomepack' ); ?>
				</td>
			</tr>
		</table>

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
 * @param $initiator_userid The user ID of the person initiating the friend request
 * @param $friend_userid The user ID of the new friend
 * @uses BP_Friends_Friendship Class Creates a new relationship based on a pair of user IDs
 * @return Boolean represent success or failure creating the new relationship
 */
function dp_force_add_friend( $initiator_userid, $friend_userid ) {
	if ( !class_exists('BP_Friends_Friendship') )
		return false;

	$friendship = new BP_Friends_Friendship;	
	if ( (int)$friendship->is_confirmed )
		return true;
		
	$friendship->initiator_user_id = $initiator_userid;
	$friendship->friend_user_id = $friend_userid;
	$friendship->is_confirmed = 1;
	$friendship->is_limited = 0;
	$friendship->date_created = time();
	
	return ( $friendship->save() );
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
	if (!get_option('dp-welcomepack-friend-id')) { update_option('dp-welcomepack-friend-id', '1'); }
}

add_action( 'wpmu_activate_user', 'dp_welcomepack_defaultfriend', 1, 3 );
add_action( 'admin_menu', 'dp_welcomepack_menu' );

add_action( 'plugins_loaded', 'dp_welcomepack_setup_globals', 5 );	
add_action( 'admin_menu', 'dp_welcomepack_setup_globals', 1 );
?>
