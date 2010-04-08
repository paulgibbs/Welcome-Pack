<?php
/*
Plugin Name: Welcome Pack
Plugin URI: http://www.twitter.com/pgibbs
Author: DJPaul
Author URI: http://www.twitter.com/pgibbs
Description: When a user registers on your site, you may want to automatically send them a friend or group invitation, or a welcome message. This plugin lets you do that.
Version: 2.0
License: General Public License version 3 
Requires at least: WP/MU 2.9, BuddyPress 1.2
Tested up to: WP/MU 2.9.2, BuddyPress 1.2.3
Site Wide Only: true
Network: true

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
	require( dirname( __FILE__ ) . '/includes/welcome-pack-core.php' );
}
add_action( 'bp_init', 'dpw_init' );
?>