<?php
/**
 * Plugin Name: Welcome Pack
 * Plugin URI: http://buddypress.org/community/groups/welcome-pack/
 * Description: Automatically send friend/group invites and a welcome message to new users, and redirect them to a custom page. Also provides email customisation options.
 * Version: 3.0
 * Requires at least: WordPress 3.2, BuddyPress 1.5
 * Tested up to: WP 3.2, BuddyPress 1.5
 * License: GPL3
 * Author: Paul Gibbs
 * Author URI: http://byotos.com/
 * Network: true
 * Domain Path: /languages/
 * Text Domain: dpw
 */

/**
 * Automatically send friend/group invites and a welcome message to new users, and redirect them to a custom page. Also provides email customisation options.
 *
 * "Welcome Pack"
 * Copyright (C) 2009-11 Paul Gibbs
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/.
 *
 * @package Welcome Pack
 * @subpackage Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * Version number
 */
define ( 'WELCOME_PACK_VERSION', 3 );

/**
 * Constant for third-party plugins to check if Welcome Pack is active (for backpat - deprecated).
 *
 * @deprecated 3.0
 */
define( 'WELCOME_PACK_IS_INSTALLED', 1 );

/**
 * Set this to true to automatically accept friend and group invitations on behalf of the user
 *
 * @since 2.0
 */
if ( !defined( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) )
	define( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS', false );

/**
 * Where the magic happens
 *
 * @since 3.0
 */
class DP_Welcome_Pack {
	/**
	 * Creates an instance of the DP_Welcome_Pack class, and loads i18n.
	 *
	 * @return DP_Welcome_Pack object
	 * @since 3.0
	 * @static
	 */
	public static function &init() {
		static $instance = false;

		if ( !$instance ) {
			// Look in /plugins/welcome-pack/languages/ for translations (.mo files)
			load_plugin_textdomain( 'dpw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			$instance = new DP_Welcome_Pack;
		}

		return $instance;
	}

	/**
	 * Constructor.
	 *
	 * Register actions and filters, and set up the admin screen.
	 *
	 * @global object $bp BuddyPress global settings
	 * @since 3.0
	 */
	public function __construct() {
		// Load globally shared filters
		require( dirname( __FILE__ ) . '/welcome-pack-filters.php' );

		// Set up the admin menu object
		add_action( 'bp_init', array( 'DP_Welcome_Pack', 'setup_admin' ), 9 );

		// Register an install/upgrade handler
		add_action( 'dpw_register_post_types', array( 'DP_Welcome_Pack', 'check_installed' ) );

		// Register admin menu pages, and a settings link on the Plugins page
		add_filter( 'plugin_action_links', array( 'DP_Welcome_Pack_Admin', 'add_settings_link' ), 10, 2 );

		// Register post type
		add_action( 'bp_init', array( 'DP_Welcome_Pack', 'register_post_types' ) );

		// Start page
		add_filter( 'login_redirect', array( 'DP_Welcome_Pack', 'redirect_login' ), 20, 3 );
		add_filter( 'ws_plugin__s2member_fill_login_redirect_rc_vars', array( 'DP_Welcome_Pack', 'redirect_s2member_login' ), 10, 2 );

		// And finally, things that happen when a user's account is activated (e.g. everything else).
		add_action( 'bp_core_activated_user', array( 'DP_Welcome_Pack', 'user_activated' ) );


		/**
		 * Email customisation
		 */
		$subjects = apply_filters( 'dpw_raw_email_subjects', array( 'bp_activity_at_message_notification_subject', 'bp_activity_new_comment_notification_subject', 'bp_activity_new_comment_notification_comment_author_subject', 'bp_core_activation_signup_blog_notification_subject', 'bp_core_activation_signup_user_notification_subject', 'groups_at_message_notification_subject', 'friends_notification_new_request_subject', 'friends_notification_accepted_request_subject', 'groups_notification_group_updated_subject', 'groups_notification_new_membership_request_subject', 'groups_notification_membership_request_completed_subject', 'groups_notification_promoted_member_subject', 'groups_notification_group_invites_subject', 'bp_core_signup_send_validation_email_subject', 'messages_notification_new_message_subject' ) );
		foreach ( (array) $subjects as $filter_name )
			add_filter( $filter_name, array( 'DP_Welcome_Pack', 'email_subject' ), 14 );

		$messages = apply_filters( 'dpw_raw_email_messages', array( 'bp_activity_at_message_notification_message', 'bp_activity_new_comment_notification_message', 'bp_activity_new_comment_notification_comment_author_message', 'bp_core_activation_signup_blog_notification_message', 'bp_core_activation_signup_user_notification_message', 'groups_at_message_notification_message', 'friends_notification_new_request_message', 'friends_notification_accepted_request_message', 'groups_notification_group_updated_message', 'groups_notification_new_membership_request_message', 'groups_notification_membership_request_completed_message', 'groups_notification_promoted_member_message', 'groups_notification_group_invites_message', 'bp_core_signup_send_validation_email_message', 'messages_notification_new_message_message' ) );
 		foreach ( (array) $messages as $filter_name )
			add_filter( $filter_name, array( 'DP_Welcome_Pack', 'email_message' ), 14 );
	}

	/**
	 * Filter subject line for BuddyPress' emails.
	 *
	 * Fetches relevant email details from database, store in global
	 * Sets HTML email type
	 * Return new subject
	 *
	 * @global object $bp
	 * @param string $original_subject
	 * @return string Email subject
	 * @since 3.0
	 */
	public function email_subject( $original_subject ) {
		global $bp;

		// Strip [site name] from the front of all the emails' subject lines
		$sitename = '[' . wp_specialchars_decode( get_blog_option( bp_get_root_blog_id(), 'blogname' ), ENT_QUOTES ) . ']';
		$subject  = str_replace( $sitename, '', $original_subject );

		// Fetch relevant email details from database, if not done previously
		if ( !isset( $bp->welcome_pack ) || !isset( $bp->welcome_pack[$subject] ) ) {
		}

		// Check that a new subject is set
		if ( empty( $bp->welcome_pack[$subject]->subject ) )
			return $original_subject;

		// Set the content type to HTML
		if ( !has_filter( 'wp_mail_content_type', array( 'DP_Welcome_Pack', 'email_get_content_type' ) ) )
			add_filter( 'wp_mail_content_type', array( 'DP_Welcome_Pack', 'email_get_content_type' ) );

		return apply_filters( 'dpw_email_subject', $bp->welcome_pack[$subject]->subject );
	}

	/**
	 * Filter email message for BuddyPress' emails.
	 *
	 * @global object $bp
	 * @param string $message
	 * @return string Email message
	 * @see email_subject()
	 * @since 3.0
	 */
	public function email_message( $message ) {
		global $bp;

		// Check that a new message is set; see email_subject()
		if ( empty( $bp->welcome_pack[$subject]->message ) )
			return $message;

		return apply_filters( 'dpw_email_message', $message );
	}

	/**
	 * Install/upgrade handler.
	 *
	 * Put the default emails into the database; intentionally uses the BuddyPress text domain in parts.
	 *
	 * @since 3.0
	 */
	public function check_installed() {
		global $bp;

		// Only run this for super admins, so our emails aren't inserted into the database by a non-admin.
		if ( empty( $bp->loggedin_user->is_super_admin ) )
			return;

		// Is this first run of Welcome Pack? Check if default emails have been added to the database.
		$version = bp_get_option( 'welcomepack-db-version', 0 );
		if ( !$version && WELCOME_PACK_VERSION == $version )
			return;

		if ( $version != WELCOME_PACK_VERSION ) {
			for ( $i=$version; $i<WELCOME_PACK_VERSION; $i++ ) {
				switch ( $i ) {
					case 0:
					case 1:
					default:
						break;

					case 2:
						$emails = DP_Welcome_Pack::get_default_emails();

						// Add the emails to the database
						foreach ( $emails as $email ) {
							// [0] is the subject, [1] is the email body
							$subject = __( array_shift( $email ), 'buddypress' );
							$body    = __( array_shift( $email ), 'buddypress' );

							// Further parts are optional and are added on to the end of the email body
							foreach ( (array) $email as $email_part )
								$body .= __( $email_part, 'buddypress' );

							wp_insert_post( array( 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_title' => $subject, 'post_content' => $body, 'post_status' => 'publish', 'post_type' => 'dpw_email' ) );
						}
					break;
				}
			}

			bp_update_option( 'welcomepack-db-version', WELCOME_PACK_VERSION );
		}
	}

	/**
	 * Register email post type
	 *
	 * @since 3.0
	 */
	public function register_post_types() {
		// Is email customisation enabled?
		$settings = DP_Welcome_Pack::get_settings();
		if ( !$settings['dpw_emailtoggle'] )
			return;

		// Labels
		$email_labels = array(
			'name'               => __( 'Emails',                   'dpw' ),
			'singular_name'      => __( 'Email',                    'dpw' ),
			'add_new'            => __( 'New email',                'dpw' ),
			'add_new_item'       => __( 'Create new email',         'dpw' ),
			'all_items'          => __( 'Emails',                   'dpw' ),
			'edit'               => __( 'Edit',                     'dpw' ),
			'edit_item'          => __( 'Edit email',               'dpw' ),
			'new_item'           => __( 'New email',                'dpw' ),
			'view'               => __( 'View email',               'dpw' ),
			'view_item'          => __( 'View email',               'dpw' ),
			'search_items'       => __( 'Search emails',            'dpw' ),
			'not_found'          => __( 'No emails found',          'dpw' ),
			'not_found_in_trash' => __( 'No emails found in Trash', 'dpw' ),
		);

		// Which standard post type features do we support?
		$email_supports = array(
			'editor',
			'revisions',
			'title',
		);

		// Configure the post type - show it in the admin menu, but restrict front-end access.
		$email_cpt = array(
			'labels'               => $email_labels,
			'public'               => false,
			'register_meta_box_cb' => array( 'DP_Welcome_Pack_Admin', 'email_meta_box_callback' ),
			'show_in_menu'         => true,
			'show_ui'              => true,
			'supports'             => $email_supports,
		);

		// Register the post type
		register_post_type( 'dpw_email', $email_cpt );

		// Call an action for third-parties to hook into
		do_action( 'dpw_register_post_types' );
	}

	/**
	 * Instantiate the admin menu object
	 *
	 * @since 3.0
	 */
	public function setup_admin() {
		require( dirname( __FILE__ ) . '/welcome-pack-admin.php' );
		new DP_Welcome_Pack_Admin();

		do_action( 'dpw_setup_admin_menu' );
	}

	/**
	 * Convenience function to return default email content
	 *
	 * @return array Multi-dimensional array; [0] is subject, [1] is first part of message body, [...] are additional parts of the body (optional)
	 * @since 3.0
	 * @static
	*/
	public static function get_default_emails() {
		// Intentionally not passed to gettext.
		$emails = array(
array( 'Activate Your Account', "Thanks for registering! To complete the activation of your account please click the following link:\n\n%s\n\n" ),
array( 'Activate %s', "Thanks for registering! To complete the activation of your account and blog, please click the following link:\n\n%1\$s\n\n\n\nAfter you activate, you can visit your blog here:\n\n%2\$s" ),
array( 'New message from %s', '%s sent you a new message:

Subject: %s

"%s"

To view and read your messages please log in and visit: %s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( 'Group Details Updated', 'Group details for the group "%1$s" were updated:

To view the group: %2$s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( 'Membership request for group: %s', '%1$s wants to join the group "%2$s".

Because you are the administrator of this group, you must either accept or reject the membership request.

To view all pending membership requests for this group, please visit:
%3$s

To view %4$s\'s profile: %5$s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( 'Membership request for group "%s" accepted', 'Your membership request for the group "%1$s" has been accepted.

To view the group please login and visit: %2$s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( 'Membership request for group "%s" rejected', 'Your membership request for the group "%1$s" has been rejected.

To submit another request please log in and visit: %2$s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( 'You have been promoted in the group: "%s"', 'You have been promoted to %1$s for the group: "%2$s".

To view the group please visit: %3$s

---------------------
', 'To disable these notifications please log in and go to: %s', 'an administrator', 'a moderator' ),
array( 'You have an invitation to the group: "%s"', 'One of your friends %1$s has invited you to the group: "%2$s".

To view your group invites visit: %3$s

To view the group visit: %4$s

To view %5$s\'s profile visit: %6$s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( '%s mentioned you in an update', '%1$s mentioned you in the group "%2$s":

"%3$s"

To view and respond to the message, log in and visit: %4$s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( '%s accepted your friendship request', '%1$s accepted your friend request.

To view %2$s\'s profile: %3$s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( 'New friendship request from %s', "%1\$s wants to add you as a friend.

To view all of your pending friendship requests: %2\$s

To view %3\$s\'s profile: %4\$s

---------------------
", 'To disable these notifications please log in and go to: %s' ),
array( '%s mentioned you in an update', '%1$s mentioned you in an update:

"%2$s"

To view and respond to the message, log in and visit: %3$s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( '%s replied to one of your updates', '%1$s replied to one of your updates:

"%2$s"

To view your original update and all comments, log in and visit: %3$s

---------------------
', 'To disable these notifications please log in and go to: %s' ),
array( '%s replied to one of your comments', '%1$s replied to one of your comments:

"%2$s"

To view the original activity, your comment and all replies, log in and visit: %3$s

---------------------
', 'To disable these notifications please log in and go to: %s' )
		);

		return apply_filters( 'dpw_get_default_emails', $emails );
	}

	/**
	 * Convenience function to retrieve the plugin's setting
	 *
	 * Supplies default values if they are missing from the database, or haven't been set.
	 * This avoids a lot of checks elsewhere in the plugin.
	 *
	 * @since 3.0
	 * @static
	 */
	public static function get_settings() {
		return bp_get_option( 'welcomepack', array( 'dpw_welcomemsgtoggle' => false, 'dpw_friendstoggle' => false, 'dpw_groupstoggle' => false, 'dpw_startpagetoggle' => false, 'dpw_emailtoggle' => false, 'friends' => array(), 'groups' => array(), 'startpage' => '', 'welcomemsg' => '', 'welcomemsgsender' => 0, 'welcomemsgsubject' => '' ) );
	}

	/**
	 * Implements the start page feature.
	 *
	 * This function detects when the user has logged in to the website after they have activated their
	 * account by looking for the absence of BuddyPress' last_activity user meta record. If this record
	 * is present, it means they've previously logged into the site.
	 *
	 * @global object $bp BuddyPress global settings
	 * @param string $redirect_to URL
	 * @param unknown $not_used unknown
	 * @param WP_User $user WordPress user object
	 * @since 3.0
	 */
	function redirect_login( $redirect_to, $not_used, $user ) {
		global $bp;

		// Check that we haven't been passed an error object
		if ( is_wp_error( $user ) || empty( $user->ID ) )
			return $redirect_to;

		// Is Start Page enabled?
		$settings = DP_Welcome_Pack::get_settings();
		if ( !$settings['dpw_startpagetoggle'] )
			return $redirect_to;

		// If the last_activity meta is set, then this is *not* the user's first log in
		if ( get_user_meta( $user->ID, 'last_activity', true ) )
			return $redirect_to;

		// Filter the URL for sanitisation and to allow keyword replacement
		$url = apply_filters( 'dpw_keyword_replacement', $settings['startpage'], $user->ID );
		if ( empty( $url ) )
			return $redirect_to;

		return apply_filters( 'dpw_redirect_login', esc_url( $url ), $url, $redirect_to, $user );
	}

	/**
	 * Implements the start page feature for those using the S2Member plugin.
	 *
	 * @param string $redirect_to URL
	 * @param array $login_info See wp_get_current_user()
	 * @since 3.0
	 */
	function redirect_s2member_login( $redirect_to, $login_info ) {
		// Check that we haven't been passed an error object
	  if ( is_wp_error( $login_info ) || empty( $login_info['current_user'] ) )
	    return $redirect_to;

		$new_redirect = redirect_login( $redirect_to, array(), array() );
	  return apply_filters( 'dpw_redirect_s2member_login', $new_redirect, $redirect_to, $login_info );
	}

	/**
	 * The main workhorse where the friends, groups and welcome message features happens.
	 * Triggers when a user account is activated.
	 *
	 * @param int $user_id ID of the new user
	 * @since 3.0
	 */
	function user_activated( $user_id ) {
		$settings = DP_Welcome_Pack::get_settings();

		// Is the Friend invitations component enabled?
		if ( !empty( $settings['dpw_friendstoggle'] ) && bp_is_active( 'friends' ) ) {
			if ( empty( $settings['friends'] ) )
				break;

			// Send friend requests
			foreach ( (array) $settings['friends'] as $friend_id )
				friends_add_friend( (int) $friend_id, $user_id, constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) );
		}

		// Is the Group invitations component enabled?
		if ( !empty( $settings['dpw_groupstoggle'] ) && bp_is_active( 'groups' ) ) {
			if ( empty( $settings['groups'] ) )
				break;

			foreach ( (array) $settings['groups'] as $group_id ) {
				$group = new BP_Groups_Group( (int) $group_id );

				// Send group invites
				groups_invite_user( array( 'user_id' => $user_id, 'group_id' => (int) $group_id, 'inviter_id' => $group->creator_id, 'is_confirmed' => constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) ) );
				groups_send_invites( $group->creator_id, (int) $group_id );
			}
		}

		// Is the Welcome Message component enabled?
		if ( !empty( $settings['dpw_welcomemsgtoggle'] ) && bp_is_active( 'messages' ) ) {
			if ( empty( $settings['welcomemsgsender'] ) || empty( $settings['welcomemsgsubject'] ) || empty( $settings['welcomemsg'] ) )
				break;

			// Send private messages
			messages_new_message( array( 'sender_id' => $settings['welcomemsgsender'], 'recipients' => $user_id, 'subject' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsgsubject'], $user_id ), 'content' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsg'], $user_id ) ) );
		}

		// Call an action for third-parties to hook into
		do_action( 'dpw_user_activated', $user_id );
	}

	/**
	 * Get list of email templates.
	 *
	 * This is so we can map BuddyPress' emails (via subject line) to one of our email posts.
	 * Parts of this function intentionally use the BuddyPress text domain.
	 *
	 * @return array Associative array like ['BP Email Subject' => 'Welcome Pack Email ID']
	 * @since 3.0
	 * @static
	 * @todo The email ID mapping sucks and should be done better; see email_meta_box()
	 */
	public static function email_get_types() {
		$emails = array(
			__( 'Activate Your Account', 'buddypress' )                      => 1,
			__( '%s mentioned you in an update', 'buddypress' )              => 2,
			__( '%s replied to one of your updates', 'buddypress' )          => 3,
			__( '%s replied to one of your comments', 'buddypress' )         => 4,
			__( 'Activate %s', 'buddypress' )                                => 5,
			__( '%1$s mentioned you in the group "%2$s"', 'buddypress' )     => 6,  // Deprecated in BuddyPress 1.5
			__( 'New friendship request from %s', 'buddypress' )             => 7,
			__( '%s accepted your friendship request', 'buddypress' )        => 8,
			__( 'Group Details Updated', 'buddypress' )                      => 9,
			__( 'Membership request for group: %s', 'buddypress' )           => 10,
			__( 'Membership request for group "%s" accepted', 'buddypress' ) => 11,
			__( 'Membership request for group "%s" rejected', 'buddypress' ) => 12,
			__( 'You have been promoted in the group: "%s"', 'buddypress' )  => 13,
			__( 'You have an invitation to the group: "%s"', 'buddypress' )  => 14,
			__( 'New message from %s', 'buddypress' )                        => 15,
		);

		return apply_filters( 'dpw_email_get_types', $emails );
	}

	/**
	 * Send emails as HTML
	 *
	 * @return string Email content type
	 * @since 3.0
	 * @static
	 */
	public static function email_get_content_type() {
		return apply_filters( 'dpw_email_get_content_type', 'text/html' );
	}
}
add_action( 'bp_include', array( 'DP_Welcome_Pack', 'init' ) );
?>