<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

 /**
  * Some WP filters you may want to use:
  *  - wp_filter_kses() VERY IMPORTANT see below.
  *  - wptexturize()
  *  - convert_smilies()
  *  - convert_chars()
  *  - wpautop()
  *  - stripslashes_deep()
  *  - make_clickable()
  */
add_filter( 'dpw_admin_settings_welcomemsg', 'wp_filter_kses', 1 );
add_filter( 'dpw_admin_settings_welcomemsg', 'wptexturize' );
add_filter( 'dpw_admin_settings_welcomemsg', 'convert_chars' );
add_filter( 'dpw_admin_settings_welcomemsg', 'stripslashes_deep' );

add_filter( 'dpw_admin_settings_welcomemsg_subject', 'wp_filter_kses', 1 );
add_filter( 'dpw_admin_settings_welcomemsg_subject', 'wptexturize' );
add_filter( 'dpw_admin_settings_welcomemsg_subject', 'convert_chars' );
add_filter( 'dpw_admin_settings_welcomemsg_subject', 'stripslashes_deep' );

add_filter( 'dpw_admin_settings_startpage', 'wp_filter_kses', 1 );
add_filter( 'dpw_admin_settings_startpage', 'wptexturize' );
add_filter( 'dpw_admin_settings_startpage', 'convert_chars' );
add_filter( 'dpw_admin_settings_startpage', 'stripslashes_deep' );

add_filter( 'dpw_admin_settings_email_name', 'wp_filter_kses', 1 );
add_filter( 'dpw_admin_settings_email_name', 'wptexturize' );
add_filter( 'dpw_admin_settings_email_name', 'convert_chars' );
add_filter( 'dpw_admin_settings_email_name', 'stripslashes_deep' );

add_filter( 'dpw_admin_validate_group_id', 'absint' );
add_filter( 'dpw_admin_validate_friend_id', 'absint' );
add_filter( 'dpw_admin_validate_sender_id', 'absint' );
add_filter( 'dpw_admin_validate_email_id', 'absint' );

add_filter( 'dpw_admin_rss_feed', 'wp_filter_kses', 1 );
add_filter( 'dpw_admin_rss_feed', 'wptexturize' );
add_filter( 'dpw_admin_rss_feed', 'convert_chars' );
add_filter( 'dpw_admin_rss_feed', 'stripslashes_deep' );

add_filter( 'dpw_admin_settings_email', 'wp_filter_kses', 1 );
add_filter( 'dpw_admin_settings_email', 'wptexturize' );
add_filter( 'dpw_admin_settings_email', 'convert_chars' );
add_filter( 'dpw_admin_settings_email', 'stripslashes_deep' );

/**
 * Basic keyword substitution routine for welcome message and start page data.
 *
 * @param string $text
 * @param int $user_id
 * @since 2.0
 */
function dpw_do_keyword_replacement( $text, $user_id ) {
	$text = str_replace( "USERNAME", bp_core_get_username( $user_id ), $text );
	$text = str_replace( "NICKNAME", bp_core_get_user_displayname( $user_id ), $text );
	$text = str_replace( "USER_URL", bp_core_get_user_domain( $user_id ), $text );

	return $text;
}
add_filter( 'dpw_keyword_replacement', 'dpw_do_keyword_replacement', 10, 2 );
?>