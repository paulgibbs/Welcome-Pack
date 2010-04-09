<?php
define( 'WELCOME_PACK_IS_INSTALLED', 1 );

if ( !defined( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) )
	define( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS', false );


/* The notifications file should contain functions to send email notifications on specific user actions */
require( dirname( __FILE__ ) . '/welcome-pack-notifications.php' );

/* The ajax file should hold all functions used in AJAX queries */
require ( dirname( __FILE__ ) . '/welcome-pack-ajax.php' );

/* The cssjs file should set up and enqueue all CSS and JS files used by the component */
require ( dirname( __FILE__ ) . '/welcome-pack-cssjs.php' );

/* The filters file should create and apply filters to component output functions. */
require( dirname( __FILE__ ) . '/welcome-pack-filters.php' );

if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) )
	load_textdomain( 'dpw', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );

function dpw_add_admin_menu() {
	global $bp;

	if ( !$bp->loggedin_user->is_site_admin )
		return false;

	require ( dirname( __FILE__ ) . '/welcome-pack-admin.php' );

	add_submenu_page( 'bp-general-settings',__( 'Welcome Pack', 'dpw' ), __( 'Welcome Pack', 'dpw' ), 'manage_options', 'welcome-pack', 'dpw_admin_screen' );
	add_action( 'load-buddypress_page_welcome-pack', 'dpw_admin_screen_on_load' );
	add_action( 'admin_init', 'dpw_admin_register_settings' );
}
add_action( 'admin_menu', 'dpw_add_admin_menu' );

function dpw_on_user_registration( $user_id ) {
	$settings = maybe_unserialize( get_site_option( 'welcomepack' ) );

	if ( $settings['friendstoggle'] && function_exists( 'friends_install' ) )
		foreach ( $settings['friends'] as $friend_id )
			friends_add_friend( $friend_id, $user_id, constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) );

	if ( $settings['groupstoggle'] && function_exists( 'groups_install' ) ) {
		foreach ( $settings['groups'] as $group_id ) {
			$group = new BP_Groups_Group( $group_id );
			groups_invite_user( array( 'user_id' => $user_id, 'group_id' => $group_id, 'inviter_id' => $group->creator_id, 'is_confirmed' => constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) ) );
			groups_send_invites( $group->creator_id, $group_id );
		}
	}

	if ( $settings['welcomemsgtoggle'] && function_exists( 'messages_install' ) ) {
		if ( !$settings['welcomemsgsender'] || !$settings['welcomemsgsubject'] || !$settings['welcomemsg'] )
			return;

		messages_new_message( array( 'sender_id' => $settings['welcomemsgsender'], 'recipients' => array( $settings['welcomemsgsender'], $user_id ), 'subject' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsgsubject'], $user_id ), 'content' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsg'], $user_id ) ) );
	}
}
add_action( 'bp_core_activated_user', 'dpw_on_user_registration' );

function dpw_do_keyword_replacement( $text, $user_id ) {
	$text = str_replace( "USERNAME", bp_core_get_username( $user_id ), $text );
	$text = str_replace( "NICKNAME", bp_core_get_user_displayname( $user_id ), $text );
	$text = str_replace( "USER_URL", bp_core_get_user_domain( $user_id ), $text );

	return $text;
}
add_filter( 'dpw_keyword_replacement', 'dpw_do_keyword_replacement', 10, 2 );

function dpw_load_dynamic_i18n() {
	global $l10n;

	if ( isset( $l10n['buddypress'] ) && isset( $l10n['buddypress']->entries['%s posted an update:'] ) ) {
		$l10n['buddypress']->entries['%s posted an update:']->translations[0] = '%s posted a monkey:';

	} else {
		$mo = new MO();
		$mo->add_entry( array( 'singular' => 'Spongebob', 'translations' => array( 'Squarepants' ) ) );
		if ( isset( $l10n['buddypress'] ) )
			$mo->merge_with( $l10n['buddypress'] );

		$l10n['buddypress'] = &$mo;
	}
}
add_action( 'wp', 'dpw_load_dynamic_i18n', 20 );

function dpw_get_default_email_data() {
	$emails = maybe_unserialize( get_site_option( 'welcomepack' ) );
	$emails = $emails['emails'];

	$emails = array( 
		array( 'name' => '----', 'values' => array() ),
		array( 'name' => __( 'Signup validation email', 'dpw' ), 'values' => array( 'This is box one', 'This ought to be box two' ) )
	);

	return $emails;
}
?>