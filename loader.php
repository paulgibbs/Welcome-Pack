<?php
/**
 * Main loader file; ensures that BuddyPress is loaded first.
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Welcome Pack
 * @subpackage loader
 *
 * $Id$
 */

/*
Plugin Name: Welcome Pack
Plugin URI: http://buddypress.org/community/groups/welcome-pack/
Description: When a user registers on your site, Welcome Pack lets you automatically send them a friend or group invitation, a Welcome Message and can redirect them to a Start Page. You can also customise the default emails sent by BuddyPress to ensure that they match the brand and tone of your site.
Version: 2.3
Requires at least: WordPress 3.1, BuddyPress 1.3
Tested up to: WP 3.1, BuddyPress 1.3
License: General Public License version 2
Author: Paul Gibbs
Author URI: http://byotos.com/
Network: true
Domain Path: /includes/languages/
Text Domain: dpw

"Welcome Pack" for BuddyPress
Copyright (C) 2009-11 Paul Gibbs

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/.
*/

/**
 * Only load the component if BuddyPress is loaded and initialised.
 *
 * @since 1.0
 */
function dpw_init() {
	require( dirname( __FILE__ ) . '/includes/welcome-pack-core.php' );
}
add_action( 'bp_include', 'dpw_init' );
?>