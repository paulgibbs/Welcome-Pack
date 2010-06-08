<?php
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

add_filter( 'dpw_admin_settings_firstloginurl', 'wp_filter_kses', 1 );
add_filter( 'dpw_admin_settings_firstloginurl', 'wptexturize' );
add_filter( 'dpw_admin_settings_firstloginurl', 'convert_chars' );
add_filter( 'dpw_admin_settings_firstloginurl', 'stripslashes_deep' );

add_filter( 'dpw_admin_validate_group_id', 'absint' );
add_filter( 'dpw_admin_validate_friend_id', 'absint' );
add_filter( 'dpw_admin_validate_email_id', 'absint' );

add_filter( 'dpw_admin_rss_feed', 'wp_filter_kses', 1 );
add_filter( 'dpw_admin_rss_feed', 'wptexturize' );
add_filter( 'dpw_admin_rss_feed', 'convert_chars' );
add_filter( 'dpw_admin_rss_feed', 'stripslashes_deep' );

add_filter( 'dpw_admin_settings_email', 'wp_filter_kses', 1 );
add_filter( 'dpw_admin_settings_email', 'wptexturize' );
add_filter( 'dpw_admin_settings_email', 'convert_chars' );
add_filter( 'dpw_admin_settings_email', 'stripslashes_deep' );
?>