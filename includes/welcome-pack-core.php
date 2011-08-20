<?php
/**
 * This file is included when BuddyPress is active, and after the plugins_loaded action.
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Welcome Pack
 * @subpackage core
 *
 * $Id$
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * Constant for third-party plugins to check if Welcome Pack is active
 */
define( 'WELCOME_PACK_IS_INSTALLED', 1 );

/**
 * Set this to automatically accept friend and group invitations on behalf of the user
 */
if ( !defined( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) )
	define( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS', false );

load_plugin_textdomain( 'dpw', false, '/welcome-pack/includes/languages/' );

// The cssjs file sets up and enqueue all CSS and JS files
require ( dirname( __FILE__ ) . '/welcome-pack-cssjs.php' );

// The filters file creates and apply filters to component output functions
require ( dirname( __FILE__ ) . '/welcome-pack-filters.php' );

/**
 * Register email post type
 *
 * @since 2.3
 */
function dpw_register_post_types() {
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
add_action( 'init', 'dpw_register_post_types' );

/**
 * Add "Settings" link on plugins menu
 *
 * @param array $links
 * @param string $file Name of main plugin file
 * @since 2.0
 */
function dpw_admin_add_action_link( $links, $file ) {
	if ( 'welcome-pack/loader.php' != $file )
		return $links;

	$settings_link = '<a href="' . admin_url( 'admin.php?page=welcome-pack' ) . '">' . __( 'Settings', 'dpw' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'dpw_admin_add_action_link', 10, 2 );

/**
 * This function adds a wp-admin menu item under "BuddyPress."
 *
 * @global object $bp BuddyPress global settings
 * @since 2.0
 */
function dpw_add_admin_menu() {
	global $bp;

	if ( !$bp->loggedin_user->is_super_admin )
		return false;

	// This file holds everything that goes into /wp-admin/
	require ( dirname( __FILE__ ) . '/welcome-pack-admin.php' );

	add_submenu_page( 'bp-general-settings', __( 'Welcome Pack', 'dpw' ), __( 'Welcome Pack', 'dpw' ), 'manage_options', 'welcome-pack', 'dpw_admin_screen' );
	add_action( 'load-buddypress_page_welcome-pack', 'dpw_admin_screen_on_load' );
	add_action( 'admin_init', 'dpw_admin_register_settings' );
}
add_action( 'admin_menu', 'dpw_add_admin_menu',12 );

/**
 * The main workhorse where the friends, groups and welcome message features happen.
 *
 * @param int $user_id ID of the new user
 * @since 2.0
 */
function dpw_user_registration( $user_id ) {
	$settings = get_site_option( 'welcomepack' );

	// Friends
	if ( !empty( $settings['friendstoggle'] ) && bp_is_active( 'friends' ) ) {
		if ( empty( $settings['friends'] ) )
			break;

		foreach ( (array)$settings['friends'] as $friend_id )
			friends_add_friend( (int)$friend_id, $user_id, constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) );
	}

	// Groups
	if ( !empty( $settings['groupstoggle'] ) && bp_is_active( 'groups' ) ) {
		if ( empty( $settings['groups'] ) )
			break;

		foreach ( (array)$settings['groups'] as $group_id ) {
			$group = new BP_Groups_Group( (int)$group_id );
			groups_invite_user( array( 'user_id' => $user_id, 'group_id' => (int)$group_id, 'inviter_id' => $group->creator_id, 'is_confirmed' => constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) ) );
			groups_send_invites( $group->creator_id, (int)$group_id );
		}
	}

	// Welcome message
	if ( !empty( $settings['welcomemsgtoggle'] ) && bp_is_active( 'messages' ) ) {
		if ( empty( $settings['welcomemsgsender'] ) || empty( $settings['welcomemsgsubject'] ) || empty( $settings['welcomemsg'] ) )
			break;

		messages_new_message( array( 'sender_id' => $settings['welcomemsgsender'], 'recipients' => $user_id, 'subject' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsgsubject'], $user_id ), 'content' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsg'], $user_id ) ) );
	}
}
add_action( 'bp_core_activated_user', 'dpw_user_registration' );

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
 * @param WP_User $WP_User WordPress user object
 * @since 2.3
 */
function dpw_user_login_redirect( $redirect_to, $not_used, $WP_User ) {
	global $bp;

	if ( !is_user_logged_in() || is_wp_error( $WP_User ) )
		return $redirect_to;

	$settings = get_site_option( 'welcomepack' );
	if ( empty( $settings['startpagetoggle'] ) )
		return $redirect_to;

	if ( get_user_meta( $bp->loggedin_user->id, 'last_activity', true ) )
		return $redirect_to;

	// This is the user's first log in
	$url = apply_filters( 'dpw_keyword_replacement', $settings['startpage'] );
	if ( empty( $url ) )
		return $redirect_to;

	return esc_url( apply_filters( 'dpw_user_login_redirect', $url, $redirect_to, $WP_User ) );
}
add_filter( 'login_redirect', 'dpw_user_login_redirect', 20, 3 );

/**
 * Implements the start page feature for those using the S2Member plugin.
 *
 * @param string $url Redirect to URL
 * @param array $login_info See wp_get_current_user()
 * @since 2.3
 */
function dpw_user_login_redirect_s2member( $url, $login_info ) {
  if ( empty( $login_info['current_user'] ) )
    return $url;

  return dpw_user_login_redirect( $url, '', $login_info['current_user'] );
}
add_filter( 'ws_plugin__s2member_fill_login_redirect_rc_vars', 'dpw_user_login_redirect_s2member', 10, 2 );
?>