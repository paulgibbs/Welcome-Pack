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
 * Domain Path: /includes/languages/
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