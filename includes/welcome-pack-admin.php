<?php
function dpw_admin_add_css_js() {
	wp_enqueue_style( 'welcomepack', plugins_url( $path = '/css/admin.css', __FILE__ ) );
}
add_action( 'admin_print_styles-settings_page_welcome-pack', 'dpw_admin_add_css_js' );

function dpw_admin_register_settings() {
	register_setting( 'dpw-settings-group', 'welcomepack', 'dpw_admin_validate' );
}

// TODO: per_page http://trac.buddypress.org/ticket/1991
function dpw_admin_settings_friends( $settings ) {
?>
	<p><select multiple="multiple" name="welcomepack[friends][]">
	<?php while ( bp_members() ) : bp_the_member(); ?>
		<option value="<?php bp_member_user_id() ?>"<?php foreach ( $settings['friends'] as $id ) { if ( bp_get_member_user_id() == $id ) echo " selected='selected'"; } ?>><?php bp_member_name() ?></option>
	<?php endwhile; ?>
	</select></p>
<?php
}

function dpw_admin_settings_groups( $settings ) {
?>
	<p><select multiple="multiple" name="welcomepack[groups][]">
	<?php while ( bp_groups() ) : bp_the_group(); ?>
		<option value="<?php bp_group_id() ?>"<?php foreach ( $settings['groups'] as $id ) { if ( bp_get_group_id() == $id ) echo " selected='selected'"; } ?>><?php bp_group_name() ?></option>
	<?php endwhile; ?>
	</select></p>
<?php
}

function dpw_admin_settings_welcomemsg( $settings ) {
?>
	<textarea name="welcomepack[welcomemsg]"><?php echo apply_filters( 'dpw_admin_settings_welcomemsg', $settings['welcomemsg'] ) ?></textarea>
<?php
}

function dpw_admin_settings_welcomemsg_subject( $settings ) {
?>
	<input type="text" name="welcomepack[welcomemsgsubject]" value="<?php echo apply_filters( 'dpw_admin_settings_welcomemsg_subject', $settings['welcomemsgsubject'] ) ?>" />
<?php
}

function dpw_admin_settings_welcomemsg_sender( $settings ) {
?>
	<p><select name="welcomepack[welcomemsgsender]">
	<?php while ( bp_members() ) : bp_the_member(); ?>
		<option value="<?php bp_member_user_id() ?>"<?php if ( bp_get_member_user_id() == $settings['welcomemsgsender'] ) echo " selected='selected'"; ?>><?php bp_member_name() ?></option>
	<?php endwhile; ?>
	</select></p>
<?php
}

function dpw_admin_settings_toggle( $name, $settings ) {
	$checked = $settings["{$name}toggle"];
?>
	<p><label for="<?php echo $name ?>"><?php _e( 'Enable', 'dpw' ) ?>&nbsp;<input type="checkbox" name=welcomepack[<?php echo $name ?>toggle] <?php if ( $checked ) echo 'checked="checked" ' ?>/></label></p>
<?php
}

function dpw_admin_validate( $input ) {
	if ( is_string( $input ) )  // wpmu-edit.php
		return get_site_option( 'welcomepack' );

	if ( isset( $input['friends'] ) ) {
		foreach ( $input['friends'] as $friend_id )
			$friend_id = apply_filters( 'dpw_admin_validate_friend_id', $friend_id );
	} else {
		$input['friends'] = array();
	}

	if ( isset( $input['groups'] ) ) {
		foreach ( $input['groups'] as $group_id )
			$group_id = apply_filters( 'dpw_admin_validate_group_id', $group_id );
	} else {
		$input['groups'] = array();
	}

	if ( isset( $input['welcomemsg'] ) )
		$input['welcomemsg'] = apply_filters( 'dpw_admin_settings_welcomemsg', $input['welcomemsg'] );

	if ( isset( $input['welcomemsgsubject'] ) )
		$input['welcomemsgsubject'] = apply_filters( 'dpw_admin_settings_welcomemsg_subject', $input['welcomemsgsubject'] );

	if ( isset( $input['welcomemsgsender'] ) )
		$input['welcomemsgsender'] = apply_filters( 'dpw_admin_validate_friend_id', $input['welcomemsgsender'] );
 
	if ( isset( $input['groupstoggle'] ) )
		$input['groupstoggle'] = ( 'on' == $input['groupstoggle'] ) ? true : false;

	if ( isset( $input['friendstoggle'] ) )
		$input['friendstoggle'] = ( 'on' == $input['friendstoggle'] ) ? true : false;

	if ( isset( $input['welcomemsgtoggle'] ) )
		$input['welcomemsgtoggle'] = ( 'on' == $input['welcomemsgtoggle'] ) ? true : false;

	return serialize( $input );
}
/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */

function dpw_admin_screen() {
	$settings = get_site_option( 'welcomepack' );
	if ( !$settings )
		$settings = array( 'friends' => array(), 'groups' => array(), 'welcomemsgsubject' => '', 'welcomemsg' => '', 'welcomemsgsender' => 0, 'welcomemsgtoggle' => false, 'friendstoggle' => false, 'groupstoggle' => false );

	$settings = maybe_unserialize( $settings );
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2><?php _e( 'Welcome Pack', 'dpw' ) ?></h2>
		<p><?php _e( 'When a user registers on your site, you may want to automatically send them a friend or group invitation, or a welcome message. This plugin lets you do that.', 'dpw' ) ?></p>

		<h3><?php _e( 'Your Welcome Pack', 'dpw' ) ?></h3>
		<a name="welcomepack"></a>
		<form method="post" action="options.php" id="welcomepack">
			<?php settings_fields( 'dpw-settings-group' ) ?>

			<?php if ( function_exists( 'friends_install' ) && bp_has_members( 'type=alphabetical&populate_extras=false&per_page=10000' ) ) : ?>	
				<div class="settingname">
					<p><?php _e( 'Invite the new user to become friends with these people:', 'dpw' ) ?></p>
					<?php dpw_admin_settings_toggle( 'friends', $settings ) ?>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_friends( $settings ) ?>
				</div>
				<div style="clear: left"></div>
			<?php endif ?>

			<?php if ( function_exists( 'groups_install' ) && bp_has_groups( 'type=alphabetically&populate_extras=false&per_page=10000' ) ) : ?>
				<div class="settingname">
					<p><?php _e( 'Ask the new user if they\'d like to join these groups:', 'dpw' ) ?></p>
					<?php dpw_admin_settings_toggle( 'groups', $settings ) ?>
				</div>
				<div class="settingvalue">
					<?php dpw_admin_settings_groups( $settings ) ?>
				</div>
				<div style="clear: left"></div>
			<?php endif ?>

			<?php if ( function_exists( 'messages_install' ) && bp_has_members( 'type=alphabetical&populate_extras=false&per_page=10000' ) ) : ?>
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
?>