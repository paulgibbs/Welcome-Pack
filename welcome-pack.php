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
define ( 'WELCOME_PACK_VERSION', 3.0 );

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
		require( dirname( __FILE__ ) . '/welcome-pack-filters.php' );

		add_action( bp_core_admin_hook(), array( $this, 'setup_admin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );

		// Emails
		add_action( 'bp_init', array( $this, 'register_post_types' ) );

		// Start page
		add_filter( 'login_redirect', array( $this, 'redirect_login' ), 20, 3 );
		add_filter( 'ws_plugin__s2member_fill_login_redirect_rc_vars', array( $this, 'redirect_s2member_login' ), 10, 2 );

		// Things that happen when a user's account is activated
		add_action( 'bp_core_activated_user', array( $this, 'user_activated' ) );
	}

	/**
	 * Register email post type
	 *
	 * @since 3.0
	 */
	function register_post_types() {
		$email_labels = array(
			'name'               => __( 'Emails',                   'dpw' ),
			'singular_name'      => __( 'Email',                    'dpw' ),
			'add_new'            => __( 'New email',                'dpw' ),
			'add_new_item'       => __( 'Create new email',         'dpw' ),
			'edit'               => __( 'Edit',                     'dpw' ),
			'edit_item'          => __( 'Edit email',               'dpw' ),
			'new_item'           => __( 'New email',                'dpw' ),
			'view'               => __( 'View email',               'dpw' ),
			'view_item'          => __( 'View email',               'dpw' ),
			'search_items'       => __( 'Search emails',            'dpw' ),
			'not_found'          => __( 'No emails found',          'dpw' ),
			'not_found_in_trash' => __( 'No emails found in Trash', 'dpw' )
		);

		$email_supports = array(
			'editor',
			'page-attributes',
			'revisions',
			'title'
		);

		$email_cpt = array(
			'labels'          => $email_labels,
			'public'          => false,
			'show_in_menu'    => false,
			'show_ui'         => true,
			'supports'        => $email_supports
		);

		register_post_type( 'dpw_email', $email_cpt );
	}

	/**
	 * Load the admin menu if current user is an admin
	 *
	 * @since 3.0
	 */
	public function setup_admin_menu() {
		if ( !is_admin() || ( !is_user_logged_in() || !is_super_admin() ) )
			return;

		require( dirname( __FILE__ ) . '/welcome-pack-admin.php' );
		do_action( 'dpw_admin_menu' );
	}

	/**
	 * Add link to settings screen on the WP Admin 'plugins' page
	 *
	 * @param array $links Item links
	 * @param string $file Plugin's file name
	 * @since 3.0
	 */
	public function add_settings_link( $links, $file ) {
		if ( 'welcome-pack/welcome-pack.php' != $file )
			return $links;

		array_unshift( $links, sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=welcome-pack' ), __( 'Settings', 'dpw' ) ) );
		do_action( 'dpw_add_settings_link' );

		return $links;
	}

	/**
	 * Convenience function to retrieve the plugin's setting
	 *
	 * @since 3.0
	 * @static
	 */
	public static function get_settings() {
		return get_site_option( 'welcomepack', array( 'dpw_welcomemsgtoggle' => false, 'dpw_friendstoggle' => false, 'dpw_groupstoggle' => false, 'dpw_startpagetoggle' => false, 'dpw_emailtoggle' => false, 'friends' => array(), 'groups' => array(), 'startpage' => '', 'welcomemsg' => '', 'welcomemsgsender' => 0, 'welcomemsgsubject' => '' ) );
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

		if ( is_wp_error( $user ) || empty( $user->ID ) )
			return $redirect_to;

		$settings = DP_Welcome_Pack::get_settings();
		if ( !$settings['dpw_startpagetoggle'] )
			return $redirect_to;

		// If the last_activity meta is not set, then this is the user's first log in
		if ( get_user_meta( $user->ID, 'last_activity', true ) )
			return $redirect_to;

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
	  if ( empty( $login_info['current_user'] ) )
	    return $redirect_to;

	  return apply_filters( 'dpw_redirect_s2member_login', $this->redirect_login( $redirect_to, array(), array() ), $redirect_to, $login_info );
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

		// Friends
		if ( !empty( $settings['dpw_friendstoggle'] ) && bp_is_active( 'friends' ) ) {
			if ( empty( $settings['friends'] ) )
				break;

			foreach ( (array) $settings['friends'] as $friend_id )
				friends_add_friend( (int) $friend_id, $user_id, constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) );
		}

		// Groups
		if ( !empty( $settings['dpw_groupstoggle'] ) && bp_is_active( 'groups' ) ) {
			if ( empty( $settings['groups'] ) )
				break;

			foreach ( (array) $settings['groups'] as $group_id ) {
				$group = new BP_Groups_Group( (int) $group_id );
				groups_invite_user( array( 'user_id' => $user_id, 'group_id' => (int) $group_id, 'inviter_id' => $group->creator_id, 'is_confirmed' => constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) ) );
				groups_send_invites( $group->creator_id, (int) $group_id );
			}
		}

		// Welcome message
		if ( !empty( $settings['dpw_welcomemsgtoggle'] ) && bp_is_active( 'messages' ) ) {
			if ( empty( $settings['welcomemsgsender'] ) || empty( $settings['welcomemsgsubject'] ) || empty( $settings['welcomemsg'] ) )
				break;

			messages_new_message( array( 'sender_id' => $settings['welcomemsgsender'], 'recipients' => $user_id, 'subject' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsgsubject'], $user_id ), 'content' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsg'], $user_id ) ) );
		}
	}
}
add_action( 'bp_include', array( 'DP_Welcome_Pack', 'init' ) );
?>