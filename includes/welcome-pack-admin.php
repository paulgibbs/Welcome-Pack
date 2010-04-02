<?php
function dpw_admin_screen_on_load() {
	add_meta_box('dpw-admin-metaboxes-sidebox-1', __( 'Like this plugin?', 'dpw' ), 'dpw_admin_screen_socialmedia', 'settings_page_welcome-pack', 'side', 'core');
	add_meta_box('dpw-admin-metaboxes-sidebox-2', __( 'Need support?', 'dpw' ), 'on_sidebox_1_content', 'settings_page_welcome-pack', 'side', 'core');
	add_meta_box('dpw-admin-metaboxes-sidebox-3', __( 'Latest news from BYOTOS', 'dpw' ), 'on_sidebox_1_content', 'settings_page_welcome-pack', 'side', 'core');
	add_meta_box('dpw-admin-metaboxes-settingsbox', __( 'Settings', 'dpw' ), 'dpw_admin_screen_settingsbox', 'settings_page_welcome-pack', 'normal', 'core');
}

function dpw_admin_add_css_js() {
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_style( 'welcomepack', plugins_url( '/css/admin.css', __FILE__ ) );
}
add_action( 'admin_print_styles-settings_page_welcome-pack', 'dpw_admin_add_css_js' );

// Tells WP that we support two columns
function dpw_admin_screen_layout_columns( $columns, $screen ) {
	if ( 'settings_page_welcome-pack' == $screen )
		$columns['settings_page_welcome-pack'] = 2;

	return $columns;
}
add_filter( 'screen_layout_columns', 'dpw_admin_screen_layout_columns', 10, 2 );

// Add "Settings" link on plugins menu
function dpw_admin_add_action_link( $links, $file ) {
	if ( 'welcome-pack/loader.php' != $file )
		return $links;

	$settings_link = '<a href="' . admin_url( 'options-general.php?page=welcome-pack' ) . '">' . __('Settings') . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'dpw_admin_add_action_link', 10, 2 );

function dpw_admin_register_settings() {
	register_setting( 'dpw-settings-group', 'welcomepack', 'dpw_admin_validate' );
}

function dpw_admin_screen_socialmedia( $settings ) {
?>
<p><?php _e( 'Why not do any or all of the following:', 'dpw' ) ?></p>
<ul>
	<li><?php _e( 'Tell your friends!', 'dpw' ) ?></a></li>
	<li><a href="http://wordpress.org/extend/plugins/welcome-pack/"><?php _e( 'Give it a good rating on WordPress.org.', 'dpw' ) ?></a></li>
	<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=P3K7Z7NHWZ5CL&amp;lc=GB&amp;item_name=B%2eY%2eO%2eT%2eO%2eS%20%2d%20BuddyPress%20plugins&amp;currency_code=GBP&amp;bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted"><?php _e( 'Thank me by donating towards future development', 'dpw' ) ?></a>.</li>
</ul>
<p><?php _e( 'Or share on one of these social networks:', 'dpw' ) ?></p>
<ul class="menu">
	<li><a target="_new" href="http://twitter.com/home?status=Check%20out%20Welcome%20Pack%20for%20http://wordpress.org/extend/plugins/welcome-pack/"><img src="<?php echo plugins_url( '/images/twitter_32.png', __FILE__ ) ?>" alt="Twitter" /></a></li>
	<li><a target="_new" href="http://www.facebook.com/sharer.php?u=http://wordpress.org/extend/plugins/welcome-pack/"><img src="<?php echo plugins_url( '/images/facebook_32.png', __FILE__ ) ?>" alt="Facebook" /></a></li>
	<li><a target="_new" href="http://del.icio.us/post?url=http://wordpress.org/extend/plugins/welcome-pack/&amp;title=When%20a%20user%20registers%20on%20your%20BuddyPress-powered%20site,%20you%20may%20want%20to%20automatically%20send%20them%20a%20friend%20or%20group%20invitation,%20or%20a%20welcome%20message.%20Welcome%20Pack%20lets%20you%20do%20that."><img src="<?php echo plugins_url( '/images/delicious_32.png', __FILE__ ) ?>" alt="Delicious - social bookmarking" /></a></li>
	<li><a target="_new" href="http://www.stumbleupon.com/submit?url=http://wordpress.org/extend/plugins/welcome-pack/&amp;title=When%20a%20user%20registers%20on%20your%20BuddyPress-powered%20site,%20you%20may%20want%20to%20automatically%20send%20them%20a%20friend%20or%20group%20invitation,%20or%20a%20welcome%20message.%20Welcome%20Pack%20lets%20you%20do%20that."><img src="<?php echo plugins_url( '/images/stumbleupon_32.png', __FILE__ ) ?>" alt="Stumble Upon" /></a></li>
</ul>
<?php
}

function on_sidebox_1_content( $settings ) {
?>
<ul style="list-style-type:disc;margin-left:20px;">
<li>hi</li>
</ul>
<?php
}

function dpw_admin_screen_settingsbox( $settings ) {
?>
<?php if ( function_exists( 'friends_install' ) && bp_has_members( 'type=alphabetical&populate_extras=false&per_page=10000' ) ) : ?>	
	<div class="setting">
		<div class="settingname">
			<p><?php _e( 'Invite the new user to become friends with these people:', 'dpw' ) ?></p>
			<?php dpw_admin_settings_toggle( 'friends', $settings ) ?>
		</div>
		<div class="settingvalue">
			<?php dpw_admin_settings_friends( $settings ) ?>
		</div>
		<div style="clear: left"></div>
	</div>
<?php endif ?>

<?php if ( function_exists( 'groups_install' ) && bp_has_groups( 'type=alphabetically&populate_extras=false&per_page=10000' ) ) : ?>
	<div class="setting">
		<div class="settingname">
			<p><?php _e( 'Ask the new user if they\'d like to join these groups:', 'dpw' ) ?></p>
			<?php dpw_admin_settings_toggle( 'groups', $settings ) ?>
		</div>
		<div class="settingvalue">
			<?php dpw_admin_settings_groups( $settings ) ?>
		</div>
		<div style="clear: left"></div>
	</div>
<?php endif ?>

<?php if ( function_exists( 'messages_install' ) && bp_has_members( 'type=alphabetical&populate_extras=false&per_page=10000' ) ) : ?>
	<div class="setting">
		<div class="settingname">
			<p><?php _e( 'Send the new user a welcome message&hellip;', 'dpw' ) ?></p>
			<?php dpw_admin_settings_toggle( 'welcomemsg', $settings ) ?>
		</div>
		<div class="settingvalue">
			<?php dpw_admin_settings_welcomemsg( $settings ) ?>
		</div>
		<div style="clear: left"></div>
	</div>

	<div class="setting">
		<div class="settingname">
			<p><?php _e( '&hellip;with this subject:', 'dpw' ) ?></p>
		</div>
		<div class="settingvalue">
			<?php dpw_admin_settings_welcomemsg_subject( $settings ) ?>
		</div>
		<div style="clear: left"></div>
	</div>

	<div class="setting">
		<div class="settingname">
			<p><?php _e( '&hellip;from this user:', 'dpw' ) ?></p>
		</div>
		<div class="settingvalue">
			<?php dpw_admin_settings_welcomemsg_sender( $settings ) ?>
		</div>
		<div style="clear: left"></div>
	</div>
<?php endif;
}

// TODO: per_page http://trac.buddypress.org/ticket/1991
function dpw_admin_settings_friends( $settings ) {
?>
	<select multiple="multiple" name="welcomepack[friends][]" style="overflow-y: hidden">
	<?php while ( bp_members() ) : bp_the_member(); ?>
		<option value="<?php bp_member_user_id() ?>"<?php foreach ( $settings['friends'] as $id ) { if ( bp_get_member_user_id() == $id ) echo " selected='selected'"; } ?>><?php bp_member_name() ?></option>
	<?php endwhile; ?>
	</select>
<?php
}

function dpw_admin_settings_groups( $settings ) {
?>
	<select multiple="multiple" name="welcomepack[groups][]">
	<?php while ( bp_groups() ) : bp_the_group(); ?>
		<option value="<?php bp_group_id() ?>"<?php foreach ( $settings['groups'] as $id ) { if ( bp_get_group_id() == $id ) echo " selected='selected'"; } ?>><?php bp_group_name() ?></option>
	<?php endwhile; ?>
	</select>
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
	<select name="welcomepack[welcomemsgsender]">
	<?php while ( bp_members() ) : bp_the_member(); ?>
		<option value="<?php bp_member_user_id() ?>"<?php if ( bp_get_member_user_id() == $settings['welcomemsgsender'] ) echo " selected='selected'"; ?>><?php bp_member_name() ?></option>
	<?php endwhile; ?>
	</select>
<?php
}

function dpw_admin_settings_toggle( $name, $settings ) {
	$checked = $settings["{$name}toggle"];
?>
	<label for="<?php echo $name ?>"><?php _e( 'Enable', 'dpw' ) ?>&nbsp;<input type="checkbox" name=welcomepack[<?php echo $name ?>toggle] <?php if ( $checked ) echo 'checked="checked" ' ?>/></label>
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
	global $screen_layout_columns;

	$settings = get_site_option( 'welcomepack' );
	if ( !$settings )
		$settings = array( 'friends' => array(), 'groups' => array(), 'welcomemsgsubject' => '', 'welcomemsg' => '', 'welcomemsgsender' => 0, 'welcomemsgtoggle' => false, 'friendstoggle' => false, 'groupstoggle' => false );

	$settings = maybe_unserialize( $settings );
?>
<script type="text/javascript">
	jQuery(document).ready( function($) {
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		postboxes.add_postbox_toggles('settings_page_welcome-pack');
	});
</script>

<div id="dpw-admin-metaboxes-general" class="wrap">

<div id="icon-options-general" class="icon32"><br /></div>
<h2><?php _e( 'Welcome Pack', 'dpw' ) ?></h2>
<p><?php _e( 'When a user registers on your site, you may want to automatically send them a friend or group invitation, or a welcome message. This plugin lets you do that.', 'dpw' ) ?></p>

	<form method="post" action="options.php" id="welcomepack">
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ) ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ) ?>
		<?php settings_fields( 'dpw-settings-group' ) ?>

		<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
			<div id="side-info-column" class="inner-sidebar">
				<?php do_meta_boxes( 'settings_page_welcome-pack', 'side', $settings ) ?>
			</div>
			<div id="post-body" class="has-sidebar">
				<div id="post-body-content" class="has-sidebar-content">
					<?php do_meta_boxes( 'settings_page_welcome-pack', 'normal', $settings ) ?>
				</div>

				<input type="submit" class="button-primary" value="<?php _e( 'Save Welcome Pack Settings', 'dpw' ) ?>"/>
			</div>
			<br class="clear"/>
		</div>
	</form>

</div><!-- .wrap -->
<?php
}
?>