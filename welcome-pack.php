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
		//add_action( 'dpw_register_post_types', array( 'DP_Welcome_Pack', 'check_installed' ) );

		// Register admin menu pages, and a settings link on the Plugins page
		add_filter( 'plugin_action_links', array( 'DP_Welcome_Pack_Admin', 'add_settings_link' ), 10, 2 );

		// Email customisation...
		add_action( 'bp_init', array( 'DP_Welcome_Pack', 'register_post_types' ) );
		add_filter( 'wp_mail_content_type', array( 'DP_Welcome_Pack', 'email_get_content_type' ) );

		// Start page...
		add_filter( 'login_redirect', array( 'DP_Welcome_Pack', 'redirect_login' ), 20, 3 );
		add_filter( 'ws_plugin__s2member_fill_login_redirect_rc_vars', array( 'DP_Welcome_Pack', 'redirect_s2member_login' ), 10, 2 );

		// And finally, things that happen when a user's account is activated (e.g. everything else).
		add_action( 'bp_core_activated_user', array( 'DP_Welcome_Pack', 'user_activated' ) );
	}

	/**
	 * This is an install/upgrade handler; we use this to put the default emails in to the database.
	 *
	 * @since 3.0
	 */
	public function check_installed() {
		// Is this first run of Welcome Pack? Check if default emails have been added to the database.
		$version = get_site_option( 'welcomepack-db-version', 0 );
		if ( !$version && WELCOME_PACK_VERSION == $version )
			return;

		if ( $version != WELCOME_PACK_VERSION ) {
			for ( $i=$version; $i<WELCOME_PACK_VERSION; $i++ ) {
				switch ( $i ) {
					case 0:
					case 1:
					case 2:
					case 3:
					break;
				}
			}

			update_site_option( 'welcomepack-db-version', WELCOME_PACK_VERSION );
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
			_( 'New message from %s', 'buddypress' )                         => 15,
		);

		return apply_filters( 'dpw_email_get_types', $emails );
	}

	/**
	 * Send HTML emails
	 *
	 * @since 3.0
	 * @static
	 */
	public static function email_get_content_type() {
		return 'text/html';
	}
}
add_action( 'bp_include', array( 'DP_Welcome_Pack', 'init' ) );
?>