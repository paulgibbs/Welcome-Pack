<?php
/*
Plugin Name: Welcome Pack
Plugin URI: http://www.twitter.com/pgibbs
Author: DJPaul
Author URI: http://www.twitter.com/pgibbs
Description: When a user registers on your site, you may want to automatically send them a friend or group invitation, or a welcome message. This plugin lets you do that.
Version: 1.62
License: General Public License version 3 
Requires at least: WP/MU 2.9, BuddyPress 1.2
Tested up to: WP/MU 2.9, BuddyPress 1.2


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
if ( defined( 'BP_VERSION' ) )
	dpw_buddypress_loaded();
else
	add_action( 'bp_init', 'dpw_buddypress_loaded' );

function dpw_buddypress_loaded() {
	if ( !function_exists( 'delete_blog_option' ) ) {  // TODO: http://trac.buddypress.org/ticket/1989
		function delete_blog_option( $blog_id, $option_name ) {
			return delete_option( $option_name );
		}
	}

	add_action( 'plugins_loaded', 'dpw_load_textdomain' );

	// register_activation_hook isn't fired in MU
	if ( function_exists( 'wpmu_signup_blog' ) )
		dpw_activation_hook();

	add_action( 'user_register', 'dpw_new_user_registration', 11 );
}

function dpw_activation_hook() {
	if ( '' != get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) )
		return;

	$default_settings = array( 'friends' => array(), 'groups' => array(), 'welcomemsgsubject' => '', 'welcomemsg' => '', 'welcomemsgsender' => 0, 'welcomemsgtoggle' => false, 'friendstoggle' => false, 'groupstoggle' => false );
	update_blog_option( BP_ROOT_BLOG, 'welcomepack', serialize( $default_settings ) );
}
register_activation_hook( __FILE__, 'dpw_activation_hook' );

function dpw_deactivation_hook() {
	delete_blog_option( BP_ROOT_BLOG, 'welcomepack' );
}
register_deactivation_hook( __FILE__, 'dpw_deactivation_hook' );

function dpw_load_textdomain() {
	$locale = apply_filters( 'buddypress_locale', get_locale() );
	$mofile = dirname( __FILE__ ) . "/i18n/$locale.mo";

	if ( file_exists( $mofile ) )
		load_textdomain( 'dpw', $mofile );
}


// *******************************************
// Admin screens
// *******************************************
function dpw_admin_add_css_js() {
	wp_enqueue_style( 'welcomepack', WP_PLUGIN_URL . '/welcome-pack/admin.css' );
}
add_action( 'admin_print_styles-settings_page_welcome-pack', 'dpw_admin_add_css_js' );

function dpw_admin_menu() {
	if ( !is_site_admin() )
		return false;

	add_options_page( __( 'Welcome Pack settings', 'dpw' ), __( 'Welcome Pack', 'dpw' ), 'administrator', 'welcome-pack', 'dpw_admin_screen' );
	add_action( 'admin_init', 'dpw_admin_register_settings' );
}
add_action( 'admin_menu', 'dpw_admin_menu' );

function dpw_admin_register_settings() {
	register_setting( 'dpw-settings-group', 'welcomepack', 'dpw_admin_validate' );
}

function dpw_admin_screen() {
	$settings = unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) );
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2><?php _e( 'Welcome Pack', 'dpw' ) ?></h2>
		<p><?php _e( 'When a user registers on your site, you may want to automatically send them a friend or group invitation, or a welcome message. This plugin lets you do that.', 'dpw' ) ?></p>

		<h3><?php _e( 'Your Welcome Pack', 'dpw' ) ?></h3>
		<a name="welcomepack"></a>
		<form method="post" action="options.php" id="welcomepack">
			<?php settings_fields( 'dpw-settings-group' ) ?>

			<?php if ( function_exists( 'friends_install' ) && bp_has_members( 'type=alphabetical&populate_extras=false&per_page=1000' ) ) : ?>	
				<div class="settingname">
					<p><?php _e( 'Invite the new user to become friends with these people:', 'dpw' ) ?></p>
					<?php dpw_admin_settings_toggle( 'friends', $settings ) ?>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_friends( $settings ) ?>
				</div>
				<div style="clear: left"></div>
			<?php endif ?>

			<?php if ( function_exists( 'groups_install' ) && bp_has_groups( 'type=alphabetically&populate_extras=false&per_page=1000' ) ) : ?>
				<div class="settingname">
					<p><?php _e( 'Ask the new user if they\'d like to join these groups:', 'dpw' ) ?></p>
					<?php dpw_admin_settings_toggle( 'groups', $settings ) ?>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_groups( $settings ) ?>
				</div>
				<div style="clear: left"></div>
			<?php endif ?>

			<?php if ( function_exists( 'messages_install' ) ) : ?>
				<div class="settingname">
					<p><?php _e( 'Send the new user a welcome message&hellip;', 'dpw' ) ?></p>
					<?php dpw_admin_settings_toggle( 'welcomemsg', $settings ) ?>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_welcomemsg( $settings ) ?>
				</div>
				<div style="clear: left"></div>

				<div class="settingname">
					<p><?php _e( '&hellip;with this subject:', 'dpw' ) ?></p>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_welcomemsg_subject( $settings ) ?>
				</div>
				<div style="clear: left"></div>

				<div class="settingname">
					<p><?php _e( '&hellip;from this user:', 'dpw' ) ?></p>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_welcomemsg_sender( $settings ) ?>
				</div>
				<div style="clear: left"></div>
			<?php endif ?>

			<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Welcome Pack Settings', 'dpw' ) ?>"/></p>
		</forum>
	</div> <!-- .wrap -->
<?php
}


// *******************************************
// User registration
// *******************************************
function dpw_new_user_registration( $new_user_id ) {
	$settings = unserialize( get_blog_option( BP_ROOT_BLOG, 'welcomepack' ) );

	if ( $settings['friendstoggle'] && function_exists( 'friends_install' ) ) {

		$default_friends = $settings['friends'];
		foreach ( $default_friends as $friend_id ) {
			friends_add_friend( $friend_id, $new_user_id );
		}
	}

	if ( $settings['groupstoggle'] && function_exists( 'groups_install' ) ) {

		$default_groups = $settings['groups'];
		foreach ( $default_groups as $group_id ) {
			$group = new BP_Groups_Group( $group_id );
			groups_invite_user( array( 'user_id' => $new_user_id, 'group_id' => $group_id, 'inviter_id' => $group->creator_id ) );
			groups_send_invites( $group->creator_id, $group_id );
		}
	}

	if ( $settings['welcomemsgtoggle'] && function_exists( 'messages_install' ) ) {

		if ( empty( $settings['welcomemsgsender'] ) || empty( $settings['welcomemsgsubject'] ) || empty( $settings['welcomemsg'] ) )
			return;

		messages_new_message( array( 'sender_id' => $settings['welcomemsgsender'], 'recipients' => array( $settings['welcomemsgsender'], $new_user_id ), 'subject' => $settings['welcomemsgsubject'], 'content' => $settings['welcomemsg'] ) );
	}
}


// *******************************************
// Convenience functions for admin screen
// *******************************************
// TODO: per_page http://trac.buddypress.org/ticket/1991
function dpw_admin_settings_friends( $settings ) {
	$friend_ids = $settings['friends'];
?>
	<p><select multiple="multiple" name="welcomepack[friends][]">
	<?php while ( bp_members() ) : bp_the_member(); ?>
		<option value="<?php echo esc_attr( bp_get_member_user_id() ) ?>"<?php foreach ( $friend_ids as $id ) { if ( bp_get_member_user_id() == $id ) echo " selected='selected'"; } ?>><?php bp_member_name() ?></option>
	<?php endwhile; ?>
	</select></p>
<?php
}

function dpw_admin_settings_groups( $settings ) {
	$group_ids = $settings['groups'];
?>
	<p><select multiple="multiple" name="welcomepack[groups][]">
	<?php while ( bp_groups() ) : bp_the_group(); ?>
		<option value="<?php echo esc_attr( bp_get_group_id() ) ?>"<?php foreach ( $group_ids as $id ) { if ( bp_get_group_id() == $id ) echo " selected='selected'"; } ?>><?php bp_group_name() ?></option>
	<?php endwhile; ?>
	</select></p>
<?php
}

function dpw_admin_settings_welcomemsg( $settings ) {
	$welcomemsg = esc_html( $settings['welcomemsg'] );
?>
	<textarea name="welcomepack[welcomemsg]"><?php echo $welcomemsg ?></textarea>
<?php
}

function dpw_admin_settings_welcomemsg_subject( $settings ) {
	$subject = esc_html( $settings['welcomemsgsubject'] );
?>
	<input type="text" name="welcomepack[welcomemsgsubject]" value="<?php echo $subject ?>" />
<?php
}

function dpw_admin_settings_welcomemsg_sender( $settings ) {
	$sender_id = $settings['welcomemsgsender'];
?>
	<p><select name="welcomepack[welcomemsgsender]">
	<?php if ( bp_has_members( 'type=alphabetical&populate_extras=false' ) ) : while ( bp_members() ) : bp_the_member(); ?>
		<option value="<?php echo esc_attr( bp_get_member_user_id() ) ?>"<?php if ( bp_get_member_user_id() == $sender_id ) echo " selected='selected'"; ?>><?php bp_member_name() ?></option>
	<?php endwhile; endif; ?>
	</select></p>
<?php
}

function dpw_admin_settings_toggle( $name, $settings ) {
	$checked = $settings[$name . 'toggle'];
?>
	<p><label for="<?php echo $name ?>"><?php _e( 'Enable', 'dpw' ) ?>&nbsp;<input type="checkbox" name=welcomepack[<?php echo $name ?>toggle] <?php if ( $checked ) echo 'checked="checked" ' ?>/></label></p>
<?php
}


// *******************************************
// Validation functions for register_setting
// *******************************************
function dpw_admin_validate( $input ) {
	if ( isset( $input['friends'] ) )
		array_map( 'absint', &$input['friends'] );
	else
		$input['friends'] = array();

	if ( isset( $input['groups'] ) )
		array_map( 'absint', &$input['groups'] );
	else
		$input['groups'] = array();

	if ( isset( $input['welcomemsg'] ) )
		$input['welcomemsg'] = strip_tags( $input['welcomemsg'] );

	if ( isset( $input['welcomemsgsubject'] ) )
		$input['welcomemsgsubject'] = strip_tags( $input['welcomemsgsubject'] );

	if ( isset( $input['welcomemsgsender'] ) )
		$input['welcomemsgsender'] = absint( $input['welcomemsgsender'] );
 
	if ( isset( $input['groupstoggle'] ) )
		$input['groupstoggle'] = ( 'on' == $input['groupstoggle'] ) ? true : false;

	if ( isset( $input['friendstoggle'] ) )
		$input['friendstoggle'] = ( 'on' == $input['friendstoggle'] ) ? true : false;

	if ( isset( $input['welcomemsgtoggle'] ) )
		$input['welcomemsgtoggle'] = ( 'on' == $input['welcomemsgtoggle'] ) ? true : false;

	return serialize( $input );
}
?>