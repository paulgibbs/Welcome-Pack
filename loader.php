<?php
/*
Plugin Name: Welcome Pack
Plugin URI: http://byotos.com/plugins/welcome-pack/
Author: Paul Gibbs
Author URI: http://byotos.com/
Description: When a user registers on your site, Welcome Pack lets you automatically send them a friend or group invitation, or a welcome message. You can also customise the default emails sent by BuddyPress to ensure that they match the brand and tone of your site.
Version: 2.0
License: General Public License version 2
Requires at least: WP/MU 2.9, BuddyPress 1.2
Tested up to: WP/MU 2.9.2, BuddyPress 1.2.3
Site Wide Only: true
Network: true
Domain Path: /includes/languages/
Text Domain: dpw

"Welcome Pack" for BuddyPress
Copyright (C) 2009-10 Paul Gibbs

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License version 3 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/.
*/


function dpw_init() {
	__( 'When a user registers on your site, Welcome Pack lets you automatically send them a friend or group invitation, or a welcome message. You can also customise the default emails sent by BuddyPress to ensure that they match the brand and tone of your site.', 'dpw' );  // Metadata description translation
	require( dirname( __FILE__ ) . '/includes/welcome-pack-core.php' );

	if ( !get_site_option( 'welcomepack' ) )
		update_site_option( 'welcomepack', serialize( array( 'friends' => array(), 'groups' => array(), 'welcomemsgsubject' => '', 'welcomemsg' => '', 'welcomemsgsender' => 0, 'welcomemsgtoggle' => false, 'friendstoggle' => false, 'groupstoggle' => false, 'emails' => dpw_get_default_email_data(), 'emailstoggle' => false ) ) );
}
add_action( 'bp_init', 'dpw_init' );
?>