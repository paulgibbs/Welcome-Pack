<?php
/**
 * The WP Admin area pages for Welcome Pack.
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Welcome Pack
 * @see dpw_add_admin_menu()
 * @subpackage admin
 *
 * The idea of using the meta boxes came from Joost de Valk (http://yoast.com).
 * The implementation of the above is credited to http://www.code-styling.de/english/how-to-use-wordpress-metaboxes-at-own-plugins.
 * Big thanks to both!
 *
 * $Id$
 */

/**
 * Produces the main admin page (/admin.php?page=welcome-pack)
 *
 * @global int $screen_layout_columns Number of columns to display
 * @see dpw_add_admin_menu()
 * @since 2.0
 */
function dpw_admin_screen() {
	global $screen_layout_columns;

	$settings = get_site_option( 'welcomepack' );
?>
<div id="bp-admin">
	<div id="dpw-admin-metaboxes-general" class="wrap">

		<div id="bp-admin-header">
			<h3><?php _e( 'BuddyPress', 'dpw' ) ?></h3>
			<h4><?php _e( 'Welcome Pack', 'dpw' ) ?></h4>
		</div>

		<div id="bp-admin-nav">
			<ol>
				<li <?php echo 'class="current"' ?>><a href="<?php echo admin_url( 'admin.php?page=welcome-pack' ) ?>"><?php _e( 'Friends, Groups <span class="ampersand">&amp;</span> Welcome Message', 'dpw' ) ?></a></li>
				<li><a href="<?php echo admin_url( 'edit.php?post_type=dpw_email' ) ?>"><?php _e( 'Emails', 'dpw' ) ?></a></li>
			</ol>
		</div>

		<?php if ( isset( $_GET['updated'] ) ) : ?>
			<div id="message" class="updated">
				<p><?php _e( 'Your Welcome Pack settings have been saved.', 'dpw' ) ?></p>
			</div>
		<?php endif; ?>

		<div class="dpw-spacer">
			<p><?php _e( 'When a user registers on your site, Welcome Pack lets you automatically send them a friend or group invitation, a Welcome Message and can redirect them to a Start Page.', 'dpw' ) ?></p>
		</div>

		<form method="post" action="options.php" id="welcomepack">
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ) ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ) ?>
			<?php settings_fields( 'dpw-settings-group' ) ?>

			<div id="poststuff" class="metabox-holder<?php echo ( 2 == $screen_layout_columns ) ? ' has-right-sidebar' : '' ?>">
				<div id="side-info-column" class="inner-sidebar">
					<?php do_meta_boxes( 'buddypress_page_welcome-pack', 'side', $settings ) ?>
				</div>

				<div id="post-body" class="has-sidebar">
					<div id="post-body-content" class="has-sidebar-content">
						<?php do_meta_boxes( 'buddypress_page_welcome-pack', 'normal', $settings ) ?>
					</div>

					<p><input type="submit" class="button-primary" value="<?php _e( 'Save Welcome Pack Settings', 'dpw' ) ?>" /></p>
				</div>
			</div><!-- #poststuff -->
		</form>

	</div><!-- #dpw-admin-metaboxes-general -->
</div><!-- #bp-admin -->
<?php
}

/**
 * Tell admin.php that this page supports multiple columns
 *
 * @param array $columns
 * @param string $screen Internal name of the page
 * @since 2.0
 */
function dpw_admin_screen_layout_columns( $columns, $screen ) {
	if ( 'buddypress_page_welcome-pack' == $screen )
		$columns['buddypress_page_welcome-pack'] = 2;

	return $columns;
}
add_filter( 'screen_layout_columns', 'dpw_admin_screen_layout_columns', 10, 2 );

/**
 * This function is hooked in to an action that fires near the top of admin.php, giving a convenient location
 * in which to set the contextual help and other defaults.
 *
 * @see dpw_add_admin_menu()
 * @since 2.0
 */
function dpw_admin_screen_on_load() {
	add_meta_box( 'dpw-admin-metaboxes-sidebox-1',        __( 'Like this plugin?', 'dpw' ),           'dpw_admin_screen_socialmedia',      'buddypress_page_welcome-pack', 'side',   'core' );
	add_meta_box( 'dpw-admin-metaboxes-sidebox-2',        __( 'Need support?', 'dpw' ),               'dpw_admin_screen_support',          'buddypress_page_welcome-pack', 'side',   'core' );
	add_meta_box( 'dpw-admin-metaboxes-sidebox-3',        __( 'Latest news from the author', 'dpw' ), 'dpw_admin_screen_news',             'buddypress_page_welcome-pack', 'side',   'core' );
	add_meta_box( 'dpw-admin-metaboxes-settingsbox',      __( 'Settings', 'dpw' ),                    'dpw_admin_screen_settingsbox',      'buddypress_page_welcome-pack', 'normal', 'core' );
	add_meta_box( 'dpw-admin-metaboxes-configurationbox', __( 'Configuration', 'dpw' ),               'dpw_admin_screen_configurationbox', 'buddypress_page_welcome-pack', 'normal', 'core' );

	$help  = '<p>' . __( 'If you are changing a setting that allows text entry, you can use the following placeholder tags:', 'dpw' ) . '</p>';
	$help .= '<dl>';
	$help .= "<dt>USERNAME</dt><dd>" . __( "Replaced with the person's username.", 'dpw' ) . "</dd>";
	$help .= "<dt>NICKNAME</dt><dd>" . __( "Replaced with the person's name from their user profile.", 'dpw' ) . "</dd>";
	$help .= "<dt>USER_URL</dt><dd>" . __( "Replaced with the link to their user profile.", 'dpw' ) . "</dd>";
	$help .= '</dl><br />';
	$help .= '<p>' . __( "The default behaviour for Friends and Groups is for invitations to be sent. If you would prefer to suppress those invitations and have them automatically accepted on the user's behalf, set <code>define( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS', true );</code> in wp-config.php.", 'dpw' ) .'</p><br />';

	add_contextual_help( 'buddypress_page_welcome-pack', $help );
	add_filter( 'default_contextual_help', 'dpw_admin_screen_contextual_help' );
}

/**
 * Replace the default contextual help text.
 *
 * @param string $default_text
 * @since 2.0
 */
function dpw_admin_screen_contextual_help( $default_text ) {
	return '<a href="http://buddypress.org/community/groups/welcome-pack/">' . __( 'Support forum', 'dpw' ) . '</a>';
}

/**
 * Tells the options.php settings API that we have a variable in the database
 * and what validation function to use with it.
 *
 * @see dpw_add_admin_menu()
 */
function dpw_admin_register_settings() {
	register_setting( 'dpw-settings-group', 'welcomepack', 'dpw_admin_validate' );
}

/**
 * Social media metabox
 *
 * @param array $settings get_site_option( 'welcomepack' )
 * @since 2.0
 */
function dpw_admin_screen_socialmedia( $settings ) {
?>
<p><?php _e( 'Why not do any or all of the following:', 'dpw' ) ?></p>
<ul>
	<li><p><?php _e( 'Tell your friends!', 'dpw' ) ?></a></p></li>
	<li><p><a href="http://wordpress.org/extend/plugins/welcome-pack/"><?php _e( 'Give it a good rating on WordPress.org', 'dpw' ) ?></a>.</p></li>
	<li><p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=P3K7Z7NHWZ5CL&amp;lc=GB&amp;item_name=B%2eY%2eO%2eT%2eO%2eS%20%2d%20BuddyPress%20plugins&amp;currency_code=GBP&amp;bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted"><?php _e( 'Thank me by donating towards future development', 'dpw' ) ?></a>.</p></li>
	<li><p><a href="mailto:paul@byotos.com"><?php _e( 'Hire me to create a custom plugin for your site.', 'dpw' ) ?></a></p></li>
</ul>
<p><?php _e( 'Or share on one of these social networks:', 'dpw' ) ?></p>
<ul class="menu">
	<li><a href="http://twitter.com/home?status=Check%20out%20Welcome%20Pack%20for%20%23buddypress%20http://wordpress.org/extend/plugins/welcome-pack/"><img src="<?php echo plugins_url( '/images/twitter_32.png', __FILE__ ) ?>" alt="<?php _e( 'Twitter', 'dpw' ) ?>" /></a></li>
	<li><a href="http://www.facebook.com/sharer.php?u=http://wordpress.org/extend/plugins/welcome-pack/"><img src="<?php echo plugins_url( '/images/facebook_32.png', __FILE__ ) ?>" alt="<?php _e( 'Facebook', 'dpw' ) ?>" /></a></li>
	<li><a href="http://del.icio.us/post?url=http://wordpress.org/extend/plugins/welcome-pack/&amp;title=Welcome%20Pack%20is%20a%20BuddyPress%20plugin%20that%20enhances%20the%20new%20user%20experience.%20When%20a%20user%20registers%20on%20your%20site,%20Welcome%20Pack%20lets%20you%20automatically%20send%20them%20a%20friend%20or%20group%20invitation,%20or%20a%20welcome%20message.%20You%20can%20also%20customise%20the%20default%20emails%20sent%20by%20BuddyPress%20to%20ensure%20that%20they%20match%20the%20brand%20and%20tone%20of%20your%20site."><img src="<?php echo plugins_url( '/images/delicious_32.png', __FILE__ ) ?>" alt="<?php _e( 'Delicious - social bookmarking', 'dpw' ) ?>" /></a></li>
	<li><a href="http://www.stumbleupon.com/submit?url=http://wordpress.org/extend/plugins/welcome-pack/&amp;title=Welcome%20Pack%20is%20a%20BuddyPress%20plugin%20that%20enhances%20the%20new%20user%20experience.%20When%20a%20user%20registers%20on%20your%20site,%20Welcome%20Pack%20lets%20you%20automatically%20send%20them%20a%20friend%20or%20group%20invitation,%20or%20a%20welcome%20message.%20You%20can%20also%20customise%20the%20default%20emails%20sent%20by%20BuddyPress%20to%20ensure%20that%20they%20match%20the%20brand%20and%20tone%20of%20your%20site."><img src="<?php echo plugins_url( '/images/stumbleupon_32.png', __FILE__ ) ?>" alt="<?php _e( 'Stumble Upon', 'dpw' ) ?>" /></a></li>
</ul>
<?php
}

/**
 * RSS news metabox
 *
 * @param array $settings get_site_option( 'welcomepack' )
 * @since 2.0
 */
function dpw_admin_screen_news( $settings ) {
	$rss = fetch_feed( 'http://feeds.feedburner.com/BYOTOS' );
	if ( !is_wp_error( $rss ) ) {
		$content = '<ul>';
		$items = $rss->get_items( 0, $rss->get_item_quantity( 3 ) );

		foreach ( $items as $item )
			$content .= '<li><p><a href="' . esc_url( $item->get_permalink(), null, 'display' ) . '">' . apply_filters( 'dpw_admin_rss_feed', $item->get_title() ) . '</a></p></li>';

		$content .= '<li class="rss"><p><a href="http://feeds.feedburner.com/BYOTOS">' . __( 'Subscribe with RSS', 'dpw' ) . '</a></p></li></ul>';
		echo $content;
	} else {
		echo '<ul><li>' . __( 'No news!', 'dpw' ) . '</li></ul>';
	}
}

/**
 * Support metabox
 *
 * @param array $settings get_site_option( 'welcomepack' )
 * @since 2.0
 */
function dpw_admin_screen_support( $settings ) {
?>
	<p><?php printf( __( "If you need help and support using this plugin, or have ideas for new features, please visit the <a href='%s'>support forums</a>.", 'dpw' ), 'http://buddypress.org/community/groups/welcome-pack/' ) ?></p>
<?php
}

function dpw_admin_screen_emailsconfigurationbox( $settings ) {
	$emails = $settings['emails'];
	wp_nonce_field( 'dpw-emails', '_ajax_nonce_dpw_emails' );
?>
	<div class="setting wide setting-emails <?php if ( !$settings["emailstoggle"] ) echo 'initially-hidden' ?>">
		<div class="settingname">
			<p><?php _e( 'Choose an email:', 'dpw' ) ?></p>
		</div>

		<div class="settingvalue">
			<select id="emailpicker">
				<?php for ( $i=0; $i<count( $emails ); $i++ ) : ?>
				<option value="<?php echo $i ?>"><?php echo apply_filters( 'dpw_admin_settings_email_name', $emails[$i]['name'] ) ?></option>
				<?php endfor; ?>
			</select>
		</div>
		<div style="clear: left"></div>

		<div id="email"></div>
	</div>
<?php
}

/* TODO: need to figure out how to dynamically set bottom-margin = 0 of the last div.setting-group */
function dpw_admin_screen_configurationbox( $settings ) {
	global $wpdb;

	if ( function_exists( 'friends_install' ) || function_exists( 'messages_install' ) ) {
		if ( is_multisite() )
			$column = "spam";
		else
			$column = "user_status";

		$members = $wpdb->get_results( $wpdb->prepare( "SELECT ID, display_name FROM $wpdb->users WHERE $column = 0 ORDER BY display_name ASC" ) );
	}
?>
<?php if ( function_exists( 'friends_install' ) ) : ?>
	<div class="setting setting-group setting-friends <?php if ( !$settings["friendstoggle"] ) echo 'initially-hidden' ?>">
		<div class="settingname">
			<p><?php _e( 'Invite the new user to become friends with these people:', 'dpw' ) ?></p>
		</div>
		<div class="settingvalue">
			<select multiple="multiple" name="welcomepack[friends][]" style="overflow-y: hidden">
			<?php foreach ( $members as $member ) : ?>
				<option value="<?php echo esc_attr( apply_filters( 'bp_get_member_user_id', $member->ID ) ) ?>"<?php foreach ( $settings['friends'] as $id ) { if ( $member->ID == $id ) echo " selected='selected'"; } ?>><?php echo apply_filters( 'bp_core_get_user_displayname', $member->display_name, $member->ID ) ?></option>
			<?php endforeach; ?>
			</select>
		</div>
		<div style="clear: left"></div>
	</div>
<?php endif ?>

<?php if ( function_exists( 'groups_install' ) ) : $groups = $wpdb->get_results( $wpdb->prepare( "SELECT id, name FROM {$bp->groups->table_name} ORDER BY name ASC" ) ); ?>
	<div class="setting setting-group setting-groups <?php if ( !$settings["groupstoggle"] ) echo 'initially-hidden' ?>">
		<div class="settingname">
			<p><?php _e( "Ask the new user if they'd like to join these groups:", 'dpw' ) ?></p>
		</div>
		<div class="settingvalue">
			<select multiple="multiple" name="welcomepack[groups][]">
			<?php foreach( $groups as $group ) : ?>
				<option value="<?php echo esc_attr( apply_filters( 'bp_get_group_id', $group->id ) ) ?>"<?php foreach ( $settings['groups'] as $id ) { if ( $group->id == $id ) echo " selected='selected'"; } ?>><?php echo apply_filters( 'bp_get_group_name', $group->name ) ?></option>
			<?php endforeach; ?>
			</select>
		</div>
		<div style="clear: left"></div>
	</div>
<?php endif ?>

	<div class="setting-group setting-startpage <?php if ( !$settings["startpagetoggle"] ) echo 'initially-hidden' ?>">
		<div class="setting wide">
			<div class="settingname">
				<p><?php _e( "When the new user logs into your site for the very first time, redirect them to this URL. It has to be on the same domain as this site.", 'dpw' ) ?></p>
			</div>
			<div class="settingvalue">
				<input type="url" name="welcomepack[firstloginurl]" value="<?php echo esc_attr( apply_filters( 'dpw_admin_settings_startpage', $settings['firstloginurl'] ) ) ?>" />
			</div>
			<div style="clear: left"></div>
		</div>
	</div>

<?php if ( function_exists( 'messages_install' ) ) : ?>
	<div class="setting-welcomemsg setting-group <?php if ( !$settings["welcomemsgtoggle"] ) echo 'initially-hidden' ?>">
		<div class="setting wide">
			<div class="settingname">
				<p><?php _e( 'Send the new user a Welcome Message&hellip;', 'dpw' ) ?></p>
			</div>
			<div class="settingvalue">
				<textarea name="welcomepack[welcomemsg]"><?php echo apply_filters( 'dpw_admin_settings_welcomemsg', $settings['welcomemsg'] ) ?></textarea>
			</div>
			<div style="clear: left"></div>
		</div>

		<div class="setting">
			<div class="settingname">
				<p><?php _e( '&hellip;with this subject:', 'dpw' ) ?></p>
			</div>
			<div class="settingvalue">
				<input type="text" name="welcomepack[welcomemsgsubject]" value="<?php echo esc_attr( apply_filters( 'dpw_admin_settings_welcomemsg_subject', $settings['welcomemsgsubject'] ) ) ?>" />
			</div>
			<div style="clear: left"></div>
		</div>

		<div class="setting">
			<div class="settingname">
				<p><?php _e( '&hellip;from this user:', 'dpw' ) ?></p>
			</div>
			<div class="settingvalue">
				<select name="welcomepack[welcomemsgsender]">
				<?php foreach ( $members as $member ) : ?>
					<option value="<?php echo esc_attr( apply_filters( 'bp_get_member_user_id', $member->ID ) ) ?>"<?php if ( $member->ID == $settings['welcomemsgsender'] ) echo " selected='selected'"; ?>><?php echo apply_filters( 'bp_core_get_user_displayname', $member->display_name, $member->ID ) ?></option>
				<?php endforeach; ?>
				</select>
			</div>
			<div style="clear: left"></div>
		</div>
	</div>
<?php endif;
}

function dpw_admin_screen_settingsbox( $settings ) {
?>
<div class="component">
	<h5><?php _e( "Friends", 'dpw' ) ?>
		<div class="radio">
			<?php dpw_admin_settings_toggle( 'friends', $settings ) ?>
		</div>
	</h5>

	<p><?php _e( "Invite the new user to become friends with certain members. It's a great way of teaching people how the friend acceptance process works on your site, and how they can use friendships to filter activity streams.", 'dpw' ) ?></p>
</div>

<div class="component">
	<h5><?php _e( "Groups", 'dpw' ) ?>
		<div class="radio">
			<?php dpw_admin_settings_toggle( 'groups', $settings ) ?>
		</div>
	</h5>

	<p><?php _e( "Ask the new user if they'd like to join a group. You could use this to invite all new users on your site to join a support group, to keep all of your frequently asked questions in the same place.", 'dpw' ) ?></p>
</div>

<div class="component">
	<h5><?php _e( "Start Page", 'dpw' ) ?>
		<div class="radio">
			<?php dpw_admin_settings_toggle( 'startpage', $settings ) ?>
		</div>
	</h5>
	<p><?php _e( "When the new user logs into your site for the very first time, use Start Page to redirect them anywhere you'd like. This complements the Welcome Message fantastically; create a page or blog post which showcases the features of your site.", 'dpw' ) ?></p>
</div>

<div class="component">
	<h5><?php _e( "Welcome Message", 'dpw' ) ?>
		<div class="radio">
			<?php dpw_admin_settings_toggle( 'welcomemsg', $settings ) ?>
		</div>
	</h5>
	<p><?php _e( "Send the newly-registered user a private message; use this to welcome people to your site and help them get started.", 'dpw' ) ?></p>
</div>
<?php
}

/**
 * Convenience function for the main settings panel for the friends, groups, start page and welcome message toggle boxes.
 *
 * @param array $settings get_site_option( 'welcomepack' )
 * @since 2.0
 */
function dpw_admin_settings_toggle( $name, $settings ) {
	$checked = $settings["{$name}toggle"];
?>
	<input type="radio" class="<?php echo esc_attr( $name ) ?>" name="welcomepack[<?php echo esc_attr( $name ) ?>toggle]" value="1" <?php if (  $checked ) echo 'checked="checked" ' ?>/> <?php _e( 'Enabled', 'dpw' ) ?> &nbsp;
	<input type="radio" class="<?php echo esc_attr( $name ) ?>" name="welcomepack[<?php echo esc_attr( $name ) ?>toggle]" value="0" <?php if ( !$checked ) echo 'checked="checked" ' ?>/> <?php _e( 'Disabled', 'dpw' ) ?>
<?php
}

/**
 * Validation function for the options.php settings API.
 *
 * @param array $input form input
 * @see dpw_admin_register_settings()
 * @since 2.0
 */
function dpw_admin_validate( $input ) {
	$current_settings = get_site_option( 'welcomepack' )

	if ( is_string( $input ) )  // wpmu-edit.php
		return $current_settings;

	if ( isset( $input['friends'] ) )
		foreach ( $input['friends'] as $friend_id )
			$friend_id = apply_filters( 'dpw_admin_validate_friend_id', $friend_id );

	if ( isset( $input['groups'] ) )
		foreach ( $input['groups'] as $group_id )
			$group_id = apply_filters( 'dpw_admin_validate_group_id', $group_id );

	if ( isset( $input['startpage'] ) )
		$input['startpage'] = esc_url_raw( apply_filters( 'dpw_admin_settings_startpage', $input['startpage'] ) );

	if ( isset( $input['welcomemsg'] ) )
		$input['welcomemsg'] = apply_filters( 'dpw_admin_settings_welcomemsg', $input['welcomemsg'] );

	if ( isset( $input['welcomemsgsubject'] ) )
		$input['welcomemsgsubject'] = apply_filters( 'dpw_admin_settings_welcomemsg_subject', $input['welcomemsgsubject'] );

	if ( isset( $input['welcomemsgsender'] ) )
		$input['welcomemsgsender'] = apply_filters( 'dpw_admin_validate_sender_id', $input['welcomemsgsender'] );

	if ( isset( $input['groupstoggle'] ) )
		$input['groupstoggle'] = ( $input['groupstoggle'] ) ? true : false;

	if ( isset( $input['friendstoggle'] ) )
		$input['friendstoggle'] = ( $input['friendstoggle'] ) ? true : false;

	if ( isset( $input['startpagetoggle'] ) )
		$input['startpagetoggle'] = ( $input['startpagetoggle'] ) ? true : false;

	if ( isset( $input['welcomemsgtoggle'] ) )
		$input['welcomemsgtoggle'] = ( $input['welcomemsgtoggle'] ) ? true : false;

	if ( isset( $input['emailstoggle'] ) )
		$input['emailstoggle'] = ( $input['emailstoggle'] ) ? true : false;

	return serialize( wp_parse_args( $input, $current_settings ) );
}
?>