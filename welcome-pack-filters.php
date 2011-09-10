<?php
/**
 * @package Welcome Pack
 * @subpackage Filters
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

add_filter( 'dpw_admin_metabox_latest_news', 'wp_kses_data', 1 );  // From an external source
add_filter( 'dpw_admin_metabox_latest_news', 'wptexturize'     );
add_filter( 'dpw_admin_metabox_latest_news', 'convert_chars'   );

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