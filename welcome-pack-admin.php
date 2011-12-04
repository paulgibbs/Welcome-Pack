<?php
/**
 * This file contains functions for displaying Welcome Pack's wp-admin menus, and updating the configuration settings.
 *
 * The idea of using the meta boxes came from Joost de Valk (http://yoast.com).
 * The implementation of the above is credited to http://www.code-styling.de/english/how-to-use-wordpress-metaboxes-at-own-plugins.
 * Thanks to both!
 *
 * @package Welcome Pack
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * Setting screens and options
 *
 * @since 3.0
 */
class DP_Welcome_Pack_Admin {
	/**
	 * Constructor.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		add_action( bp_core_admin_hook(), array( 'DP_Welcome_Pack_Admin', 'setup_menu' ) );
	}

	/**
	 * Set up the admin menu
	 *
	 * @since 3.0
	 */
	public function setup_menu() {
		if ( !is_admin() || ( !is_user_logged_in() || !is_super_admin() ) )
			return;

		// Get URL for site admin or network admin, as appropriate (is this running in a network?)
		$page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php'; 
		add_submenu_page( $page, __( 'Welcome Pack', 'dpw' ), __( 'Welcome Pack', 'dpw' ), 'manage_options', 'welcome-pack', array( 'DP_Welcome_Pack_Admin', 'admin_page' ) );

		// Hook in to an early action that fires when our admin screen is being displayed, so we can set some custom javascript and CSS.
		add_action( 'load-settings_page_welcome-pack', array( 'DP_Welcome_Pack_Admin', 'init' ) );

		// Change some of the text on the email cpt screens
		add_filter( 'enter_title_here', array( 'DP_Welcome_Pack_Admin', 'email_subject_placeholder' ) );

		// Save handler for the email cpt screen
		add_action( 'save_post', array( 'DP_Welcome_Pack_Admin', 'email_maybe_save' ), 10, 2 );
	}

	/**
	 * Add link to settings screen on the wp-admin Plugins screen
	 *
	 * @param array $links Item links
	 * @param string $file Plugin's file name
	 * @since 3.0
	 */
	public static function add_settings_link( $links, $file ) {
		// Check we're dealing with Welcome Pack
		if ( 'welcome-pack/welcome-pack.php' != $file )
			return $links;

		// Add Settings link
		$url = add_query_arg( 'page', 'welcome-pack', bp_core_do_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' ) ); 
		array_unshift( $links, sprintf( '<a href="%s">%s</a>', $url, __( 'Settings', 'dpw' ) ) );

		return apply_filters( 'dpw_add_settings_link', $links, $file );
	}

	/**
	 * Initialise common elements for all pages of the admin screen.
	 *
	 * @since 3.0
	 */
	public function init() {
		// Get Welcome Pack's setttings
		$settings = DP_Welcome_Pack::get_settings();

		// Which tab have we been asked to load?
		if ( !empty( $_GET['tab'] ) ) {
			switch ( $_GET['tab'] ) {
				default:
				case 'support':
					$tab       = 'support';
					$help_text = '';
				break;

				case 'groups':
					$tab       = 'groups';
					$help_text = '<p>' . __( 'Choose groups to invite the new user to. You can select as many groups as you want.', 'dpw' ) . '</p>';
				break;

				case 'members':
					$tab       = 'members';
					$help_text = '<p>' . __( 'Choose which members will send a friend invitation to the new user. You can select as many people as you want.', 'dpw' ) . '</p>';
				break;

				case 'startpage':
					$tab       = 'startpage';
					$help_text = '<p>' . __( "When a user logs in to your site for the first time, they'll be redirected to this URL. If you want to take them to their user profile, enter <code>USER_URL</code>.", 'dpw' ) . '</p>';
				break;

				case 'welcomemessage':
					$tab       = 'welcomemessage';
					$help_text = '<p>' . __( 'The subject, sender and message details are mandatory. You can personalise the message by including their <code>NICKNAME</code> or their <code>USERNAME</code> in the subject or message.', 'dpw' ) . '</p>';
				break;
			}

		} else {
			$tab       = 'settings';
			$help_text = '<p>' . __( 'This screen explains the features in Welcome Pack, and lets you enable them individually. Check the boxes, and then save changes.', 'dpw' ) . '</p>';
		}

		// Check that the specified component is active (and, if applicable, its corresponding BuddyPress component)
		if ( 'groups' == $tab && ( !bp_is_active( 'groups' ) || !$settings['dpw_groupstoggle'] ) ||
			'members' == $tab && ( !bp_is_active( 'friends' ) || !$settings['dpw_friendstoggle'] ) ||
			'welcomemessage' == $tab && ( !bp_is_active( 'messages' ) || !$settings['dpw_welcomemsgtoggle'] ) ||
			'startpage' == $tab && !$settings['dpw_startpagetoggle'] )
			$tab = 'settings';

		// How many columns does this page have by default?
		add_screen_option( 'layout_columns', array( 'max' => 2 ) );

		// All tabs
		add_meta_box( 'dpw-paypal', __( 'Give Kudos', 'dpw' ), array( 'DP_Welcome_Pack_Admin', 'paypal' ), 'settings_page_welcome-pack', 'side', 'default' );

		// Support tab
		if ( 'support' == $tab )
			add_meta_box( 'dpw-helpushelpyou', __( 'Help Us Help You', 'dpw' ), array( 'DP_Welcome_Pack_Admin', 'helpushelpyou'), 'settings_page_welcome-pack', 'side', 'high' );
		else
			add_meta_box( 'dpw-likethis', __( 'Love Welcome Pack?', 'dpw' ), array( 'DP_Welcome_Pack_Admin', 'like_this_plugin' ), 'settings_page_welcome-pack', 'side', 'default' );

		// All tabs
		add_meta_box( 'dpw-latest', __( 'Latest News', 'dpw' ), array( 'DP_Welcome_Pack_Admin', 'metabox_latest_news' ), 'settings_page_welcome-pack', 'side', 'default' );

		// Javascripts for meta box drag-and-drop
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'dashboard' );

		// Put the help text into the WordPress help tab
		add_contextual_help( 'settings_page_welcome-pack',
			$help_text .
			'<p><strong>' . __( 'For more information:', 'dpw' ) . '</strong></p>' .
			'<p>' . sprintf( '<a href="http://buddypress.org/community/groups/welcome-pack/" target="_blank">%s</a>', __( 'Support Forum', 'dpw' ) ) . '</p>'
		);
	}

	/**
	 * Outputs admin page HTML
	 *
	 * @global int $screen_layout_columns Number of columns shown on this admin page
	 * @since 3.0
	 */
	public function admin_page() {
		global $screen_layout_columns;

		// Get Welcome Pack's setttings
		$settings = DP_Welcome_Pack::get_settings();

		// Which tab have we been asked to load?
		if ( !empty( $_GET['tab'] ) ) {
			switch ( $_GET['tab'] ) {
				default:
				case 'support':
					$tab = 'support';
				break;

				case 'groups':
					$tab = 'groups';
				break;

				case 'members':
					$tab = 'members';
				break;

				case 'startpage':
					$tab = 'startpage';
				break;

				case 'welcomemessage':
					$tab = 'welcomemessage';
				break;
			}

		} else {
			$tab = 'settings';
		}

		// Check that the specified component is active (and, if applicable, its corresponding BuddyPress component)
		if ( 'groups' == $tab && ( !bp_is_active( 'groups' ) || !$settings['dpw_groupstoggle'] ) ||
			'members' == $tab && ( !bp_is_active( 'friends' ) || !$settings['dpw_friendstoggle'] ) ||
			'welcomemessage' == $tab && ( !bp_is_active( 'messages' ) || !$settings['dpw_welcomemsgtoggle'] ) ||
			'startpage' == $tab && !$settings['dpw_startpagetoggle'] )
			$tab = 'settings';

		// Check if a form was submitted, and if we need to save our settings again.
		$updated = DP_Welcome_Pack_Admin::maybe_save();

		// Build URL back to this screen
		$url = add_query_arg( 'page', 'welcome-pack', bp_core_do_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' ) ); 

		// If admin settings were just updated, fetch the new values from the database so that we aren't using stale data
		if ( $updated )
			$settings = DP_Welcome_Pack::get_settings();

		// Below, we output some custom CSS, Google +1 button javascript, and the tab bar for our admin screen.
	?>

		<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
		  {parsetags: 'explicit'}
		</script>
		<script type="text/javascript">gapi.plusone.go();</script>

		<style type="text/css">
		#dpw-helpushelpyou ul {
			list-style: disc;
			padding-left: 2em;
		}
		#dpw-likethis #___plusone_0,
		#dpw-likethis .fb {
			max-width: 49% !important;
			width: 49% !important;
		}
		#dpw-likethis .fb {
			height: 20px;
		}
		#dpw-paypal .inside {
			text-align: center;
		}
		#dpw_contact_form,
		#dpw_contact_form .button-primary {
			margin-top: 2em;
		}
		#dpw_contact_form textarea,
		#dpw_contact_form input[type="text"]  {
			width: 100%;
		}
		.dpw_friends,
		.dpw_groups,
		.dpw_startpage,
		.dpw_welcomemsg,
		.dpw_emailtoggle {
			margin-right: 2em;
		}
		#wpcontent .welcomepack select {
			height: auto;
			width: 250px;
		}
		#wpcontent .welcomepack input[type="url"],
		#wpcontent .welcomepack input[type="text"],
		#wpcontent .welcomepack select,
		#wpcontent .welcomepack textarea {
			width: 400px;
		}
		</style>

		<div class="wrap">
			<?php screen_icon( 'options-general' ); ?>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_attr( $url ); ?>" class="nav-tab <?php if ( 'settings' == $tab )  : ?>nav-tab-active<?php endif; ?>"><?php _e( 'Settings', 'dpw' );    ?></a>

				<?php if ( $settings['dpw_friendstoggle'] && bp_is_active( 'friends' ) ) : ?>
					<a href="<?php echo esc_attr( $url . '&amp;tab=members' ); ?>" class="nav-tab <?php if ( 'members'  == $tab  ) : ?>nav-tab-active<?php endif; ?>"><?php _e( 'Friends', 'dpw' ); ?></a>
				<?php endif; ?>

				<?php if ( $settings['dpw_groupstoggle'] && bp_is_active( 'groups' ) ) : ?>
					<a href="<?php echo esc_attr( $url . '&amp;tab=groups' ); ?>" class="nav-tab <?php if ( 'groups'  == $tab  ) : ?>nav-tab-active<?php endif; ?>"><?php _e( 'Groups', 'dpw' ); ?></a>
				<?php endif; ?>

				<?php if ( $settings['dpw_startpagetoggle'] ) : ?>
					<a href="<?php echo esc_attr( $url . '&amp;tab=startpage' ); ?>" class="nav-tab <?php if ( 'startpage'  == $tab  ) : ?>nav-tab-active<?php endif; ?>"><?php _e( 'Start Page', 'dpw' ); ?></a>
				<?php endif; ?>

				<?php if ( $settings['dpw_welcomemsgtoggle'] && bp_is_active( 'messages' ) ) : ?>
					<a href="<?php echo esc_attr( $url . '&amp;tab=welcomemessage' ); ?>" class="nav-tab <?php if ( 'welcomemessage'  == $tab  ) : ?>nav-tab-active<?php endif; ?>"><?php _e( 'Welcome Message', 'dpw' ); ?></a>
				<?php endif; ?>

				<a href="<?php echo esc_attr( $url . '&amp;tab=support' ); ?>" class="nav-tab <?php if ( 'support'  == $tab  ) : ?>nav-tab-active<?php endif; ?>"><?php _e( 'Get Support', 'dpw' ); ?></a>
			</h2>

			<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
				<div id="side-info-column" class="inner-sidebar">
					<?php do_meta_boxes( 'settings_page_welcome-pack', 'side', $settings ); ?>
				</div>

				<div id="post-body" class="has-sidebar">
					<div id="post-body-content" class="has-sidebar-content welcomepack">
						<?php if ( $updated ) : ?>
							<div id="message" class="updated below-h2"><p><?php _e( 'Your preferences have been updated.', 'dpw' ); ?></p></div>
						<?php endif; ?>

						<?php
						// This logic decides whether to print the support page, settings page, or one of the component pages.
						if ( 'support' == $tab )
							DP_Welcome_Pack_Admin::admin_page_support();
						elseif ( 'settings' == $tab )
							DP_Welcome_Pack_Admin::admin_page_settings( $settings, $updated );
						else
							DP_Welcome_Pack_Admin::admin_page_component( $tab, $settings, $updated );
						?>
					</div><!-- #post-body-content -->
				</div><!-- #post-body -->

			</div><!-- #poststuff -->
		</div><!-- .wrap -->

	<?php
	}

	/**
	 * Support tab content for the admin page.
	 * Also handles contact form submission.
	 *
	 * @since 3.0
	 */
	protected function admin_page_support() {
		// Handle email contact form submission.
		if ( !empty( $_POST['contact_body'] ) && !empty( $_POST['contact_type'] ) && !empty( $_POST['contact_email'] ) ) {
			$body  = force_balance_tags( wp_filter_kses( stripslashes( $_POST['contact_body'] ) ) );
			$type  = force_balance_tags( wp_filter_kses( stripslashes( $_POST['contact_type'] ) ) );
			$email = sanitize_email( force_balance_tags( wp_filter_kses( stripslashes( $_POST['contact_email'] ) ) ) );

			// Message, request type and email address have been entered; send the email!
			if ( $body && $type && $email && is_email( $email ) )
				$email_sent = wp_mail( array( 'paul@byotos.com', $email ), "Welcome Pack support request: " . $type, $body );
		}

		// Build URL back to this screen
		$url = add_query_arg( array( 'page' => 'welcome-pack', 'tab' => 'support' ), bp_core_do_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' ) ); 
	?>

		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php wp_nonce_field( 'dpw-admin', 'dpw-admin-nonce', false ); ?>

		<p><?php printf( __( "Have you found a bug or do you have a great idea for the next release? Please make a report on <a href='%s'>BuddyPress.org</a>, or use the form below to get in contact. We're listening.", 'dpw' ), 'http://buddypress.org/community/groups/welcome-pack/forum/' ); ?></p>	

		<?php if ( isset( $email_sent ) ) : ?>
			<div class="welcomepack updated below-h2">
				<p><?php _e( "Thanks, we've received your message and have emailed you a copy for your records. We'll be in touch soon!", 'dpw' ); ?></p>
			</div>
		<?php endif; ?>

		<form id="dpw_contact_form" name="contact_form" method="post" action="<?php echo esc_attr( $url ); ?>">
			<p><?php _e( "What type of request do you have?", 'dpw' ); ?></p>
			<select name="contact_type">
				<option value="bug" selected="selected"><?php _e( "Bug report", 'dpw' ); ?></option>
				<option value="idea"><?php _e( "Idea", 'dpw' ); ?></option>
				<option value="suggestion"><?php _e( "Other support request", 'dpw' ); ?></option>
			</select>

			<p><?php _e( "How can we help?", 'dpw' ); ?></p>
			<textarea id="contact_body" name="contact_body"></textarea>

			<p><?php _e( "What's your email address?", 'dpw' ); ?></p>
			<input type="text" name="contact_email" />
			<br />

			<input type="submit" class="button-primary" value="<?php _e( 'Send', 'dpw' ); ?>" />
		</form>
	<?php
	}

	/**
	 * Setting tab's content for the admin page
	 *
	 * @param array $settings Plugin settings (from DB)
	 * @param bool $updated Have settings been updated on the previous page submission?
	 * @since 3.0
	 */
	protected function admin_page_settings( $settings, $updated ) {
		$url = add_query_arg( 'page', 'welcome-pack', bp_core_do_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' ) );
	?>
		<form method="post" action="<?php echo esc_attr( $url ); ?>" id="dpw-form">
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<?php wp_nonce_field( 'dpw-admin', 'dpw-admin-nonce', false ); ?>

			<p style="margin-bottom: 2em;"><?php _e( 'When a user registers on your site, Welcome Pack lets you automatically send them a friend or group invitation, a Welcome Message and can redirect them to a Start Page. You can also customise the emails sent by BuddyPress so that they match your site\'s brand, in plain text or rich HTML versions.', 'dpw' ); ?></p>

			<h4><?php _e( 'Email Customisation', 'dpw' ); ?></h4>
			<p><?php printf( __( "Customise the emails sent by BuddyPress, either in plain text or rich HTML versions. <strong>To change the emails, visit the <a href='%s'>Settings > Emails</a> page.</strong>", 'dpw' ), admin_url( 'edit.php?post_type=dpw_email' ) ); ?></p>
			<label><?php _e( 'On', 'dpw' ); ?> <input type="radio" name="dpw_emailtoggle" class="dpw_emailtoggle" value="on" <?php checked( $settings['dpw_emailtoggle'] ); ?>/></label>
			<label><?php _e( 'Off', 'dpw' ); ?> <input type="radio" name="dpw_emailtoggle" class="dpw_emailtoggle" value="off" <?php checked( $settings['dpw_emailtoggle'], false ); ?>/></label>

			<h4><?php _e( 'Friends', 'dpw' ); ?></h4>
			<?php if ( bp_is_active( 'friends' ) ) : ?>
				<p><?php _e( "Invite the new user to become friends with certain members. It's a great way of teaching people how the friend acceptance process works on your site, and how they can use friendships to filter activity streams.", 'dpw' ); ?></p>
				<label><?php _e( 'On', 'dpw' ); ?> <input type="radio" name="dpw_friendstoggle" class="dpw_friends" value="on" <?php checked( $settings['dpw_friendstoggle'] ); ?>/></label>
				<label><?php _e( 'Off', 'dpw' ); ?> <input type="radio" name="dpw_friendstoggle" class="dpw_friends" value="off" <?php checked( $settings['dpw_friendstoggle'], false ); ?>/></label>
			<?php else: ?>
				<p><?php _e( "BuddyPress' Friend Connections component needs to be enabled for this option to be active.", 'dpa' ); ?></p>
			<?php endif; ?>

			<h4><?php _e( 'Groups', 'dpw' ); ?></h4>
			<?php if ( bp_is_active( 'groups' ) ) : ?>
				<p><?php _e( "Ask the new user if they'd like to join a group. You could use this to invite all new users on your site to join a support group, to keep all of your frequently asked questions in the same place.", 'dpw' ); ?></p>
				<label><?php _e( 'On', 'dpw' ); ?> <input type="radio" name="dpw_groupstoggle" class="dpw_groups" value="on" <?php checked( $settings['dpw_groupstoggle'] ); ?>/></label>
				<label><?php _e( 'Off', 'dpw' ); ?> <input type="radio" name="dpw_groupstoggle" class="dpw_groups" value="off" <?php checked( $settings['dpw_groupstoggle'], false ); ?>/></label>
			<?php else: ?>
				<p><?php _e( "BuddyPress' User Groups component needs to be enabled for this option to be active.", 'dpa' ); ?></p>
			<?php endif; ?>

			<h4><?php _e( 'Start Page', 'dpw' ); ?></h4>
			<p><?php _e( "When the new user logs into your site for the very first time, use Start Page to redirect them anywhere you'd like. This complements the Welcome Message fantastically; create a page or blog post which showcases the features of your site.", 'dpw' ); ?></p>
			<label><?php _e( 'On', 'dpw' ); ?> <input type="radio" name="dpw_startpagetoggle" class="dpw_startpage" value="on" <?php checked( $settings['dpw_startpagetoggle'] ); ?>/></label>
			<label><?php _e( 'Off', 'dpw' ); ?> <input type="radio" name="dpw_startpagetoggle" class="dpw_startpage" value="off" <?php checked( $settings['dpw_startpagetoggle'], false ); ?>/></label>

			<h4><?php _e( 'Welcome Message', 'dpw' ); ?></h4>
			<?php if ( bp_is_active( 'messages' ) ) : ?>
				<p><?php _e( "Send the newly-registered user a private message; use this to welcome people to your site and help them get started.", 'dpw' ); ?></p>
				<label><?php _e( 'On', 'dpw' ); ?> <input type="radio" name="dpw_welcomemsgtoggle" class="dpw_welcomemsg" value="on" <?php checked( $settings['dpw_welcomemsgtoggle'] ); ?>/></label>
				<label><?php _e( 'Off', 'dpw' ); ?> <input type="radio" name="dpw_welcomemsgtoggle" class="dpw_welcomemsg" value="off" <?php checked( $settings['dpw_welcomemsgtoggle'], false ); ?>/></label>
			<?php else: ?>
				<p><?php _e( "BuddyPress' Private Messaging component needs to be enabled for this option to be active.", 'dpa' ); ?></p>
			<?php endif; ?>

			<p><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'dpw' ); ?>" /></p>
		</form>

	<?php
	}

	/**
	 * Method to produce the admin page for a specific component of Welcome Pack (friends, groups, welcome message, and so on)
	 *
	 * @global object $bp BuddyPress settings object
	 * @global wpdb $wpdb WordPress database object
	 * @param string $tab Name of the component (groups, members, welcomemessage, startpage)
	 * @param array $settings Plugin settings (from DB)
	 * @param bool $updated Have settings been updated on the previous page submission?
	 * @since 3.0
	 */
	protected function admin_page_component( $tab, $settings, $updated ) {
		global $bp, $wpdb;

		$data = array();

		// Depending on which $tab we're displaying, maybe fetch some information from the database.
		if ( 'groups' == $tab && bp_is_active( 'groups' ) )
			$data = $wpdb->get_results( $wpdb->prepare( "SELECT id, name FROM {$bp->groups->table_name} ORDER BY name ASC" ) );
		elseif ( ( 'members' == $tab && bp_is_active( 'friends' ) ) || ( 'welcomemessage' == $tab && bp_is_active( 'messages' ) ) )
			$data = get_users( array( 'fields' => array( 'ID', 'display_name' ), 'orderby' => 'display_name' ) );

		$url = add_query_arg( array( 'page' => 'welcome-pack', 'tab' => $tab ), bp_core_do_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' ) );
		?>

		<form method="post" action="<?php echo esc_attr( $url ); ?>" id="dpw-form">
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<?php wp_nonce_field( 'dpw-admin', 'dpw-admin-nonce', false ); ?>

			<!-- Friends tab -->
			<?php if ( 'members' == $tab && bp_is_active( 'friends' ) ) : ?>
				<p><?php _e( 'Invite the new user to become friends with these people:', 'dpw' ); ?></p>

				<select multiple="multiple" name="friends[]" style="overflow-y: hidden">
					<?php foreach ( (array) $data as $member ) : ?>
						<option value="<?php echo esc_attr( $member->ID ); ?>"<?php foreach ( (array) $settings['friends'] as $id ) { if ( $member->ID == $id ) echo " selected='selected'"; } ?>><?php echo apply_filters( 'bp_core_get_user_displayname', $member->display_name, $member->ID ); ?></option>
					<?php endforeach; ?>
				</select>

			<!-- Groups tab -->
			<?php elseif ( 'groups' == $tab && bp_is_active( 'groups' ) ) : ?>
				<p><?php _e( "Ask the new user if they'd like to join these groups:", 'dpw' ); ?></p>

				<select multiple="multiple" name="groups[]">
					<?php foreach( (array) $data as $group ) : ?>
						<option value="<?php echo esc_attr( $group->id ); ?>"<?php foreach ( (array) $settings['groups'] as $id ) { if ( $group->id == $id ) echo " selected='selected'"; } ?>><?php echo apply_filters( 'bp_get_group_name', $group->name ); ?></option>
					<?php endforeach; ?>
				</select>

			<!-- Start Page tab -->
			<?php elseif ( 'startpage' == $tab ) : ?>

				<p><?php _e( "When the new user logs into your site for the very first time, redirect them to this URL:", 'dpw' ); ?></p>
				<input type="url" name="startpage" value="<?php echo esc_attr( $settings['startpage'] ); ?>" />

			<!-- Welcome Message tab -->
			<?php elseif ( 'welcomemessage' == $tab && bp_is_active( 'messages' ) ) : ?>

				<p><?php _e( 'When a user logs in to your site for the first time, send them a message:', 'dpw' ); ?></p>
				<textarea name="welcomemsg"><?php echo esc_textarea( $settings['welcomemsg'] ); ?></textarea>

				<p><?php _e( 'Message subject:', 'dpw' ); ?></p>
				<input type="text" name="welcomemsgsubject" value="<?php echo esc_attr( $settings['welcomemsgsubject'] ); ?>" />

				<p><?php _e( 'Send message from this user:', 'dpw' ); ?></p>
				<select name="welcomemsgsender">
					<?php foreach ( (array) $data as $member ) : ?>
						<option value="<?php echo esc_attr( $member->ID ); ?>"<?php if ( (int) $settings['welcomemsgsender'] && $member->ID == (int) $settings['welcomemsgsender'] ) echo " selected='selected'"; ?>><?php echo apply_filters( 'bp_core_get_user_displayname', $member->display_name, $member->ID ); ?></option>
					<?php endforeach; ?>
				</select>

			<?php endif; ?>
				<p><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'dpw' ); ?>" /></p>
			<?php
		?>

		</form>
		<?php
	}
	

	/**
	 * Check for and handle form submission.
	 *
	 * @return bool Have settings been updated?
	 * @since 3.0
	 * @static
	 */
	protected static function maybe_save() {
		// Fetch existing settings
		$settings = $existing_settings = DP_Welcome_Pack::get_settings();
		$updated  = false;

		// Has the Friend invitation feature been toggled on/off?
		if ( !empty( $_POST['dpw_friendstoggle'] ) ) {
			if ( 'on' == $_POST['dpw_friendstoggle'] )
				$settings['dpw_friendstoggle'] = true;
			else
				$settings['dpw_friendstoggle'] = false;
		}

		// Has the Group invitation feature been toggled on/off?
		if ( !empty( $_POST['dpw_groupstoggle'] ) ) {
			if ( 'on' == $_POST['dpw_groupstoggle'] )
				$settings['dpw_groupstoggle'] = true;
			else
				$settings['dpw_groupstoggle'] = false;
		}

		// Has the Start Page feature been toggled on/off?
		if ( !empty( $_POST['dpw_startpagetoggle'] ) ) {
			if ( 'on' == $_POST['dpw_startpagetoggle'] )
				$settings['dpw_startpagetoggle'] = true;
			else
				$settings['dpw_startpagetoggle'] = false;
		}

		// Has the Welcome Message feature been toggled on/off?
		if ( !empty( $_POST['dpw_welcomemsgtoggle'] ) ) {
			if ( 'on' == $_POST['dpw_welcomemsgtoggle'] )
				$settings['dpw_welcomemsgtoggle'] = true;
			else
				$settings['dpw_welcomemsgtoggle'] = false;
		}

		// Has the email customisation feature been toggled on/off?
		if ( !empty( $_POST['dpw_emailtoggle'] ) ) {
			if ( 'on' == $_POST['dpw_emailtoggle'] )
				$settings['dpw_emailtoggle'] = true;
			else
				$settings['dpw_emailtoggle'] = false;
		}

		// Has the list of friends (to send invites to) been updated?
		if ( !empty( $_POST['friends'] ) )
			$settings['friends'] = array_map( 'absint', (array) $_POST['friends'] );

		// Has the list of groups (to send invites to) been updated?
		if ( !empty( $_POST['groups'] ) )
			$settings['groups'] = array_map( 'absint', (array) $_POST['groups'] );

		// Has the Start Page URL been updated?
		if ( !empty( $_POST['startpage'] ) )
			$settings['startpage'] = sanitize_text_field( wp_kses_data( $_POST['startpage'] ) );

		// Has the Welcome Message body text been updated?
		if ( !empty( $_POST['welcomemsg'] ) )
			$settings['welcomemsg'] = stripslashes( wp_filter_kses( $_POST['welcomemsg'] ) );

		// Has the Welcome Message subject text been updated?
		if ( !empty( $_POST['welcomemsgsubject'] ) )
			$settings['welcomemsgsubject'] = stripslashes( sanitize_text_field( wp_filter_kses( $_POST['welcomemsgsubject'] ) ) );

		// Has the Welcome Message sender (who the message is sent from) been updated?
		if ( !empty( $_POST['welcomemsgsender'] ) )
			$settings['welcomemsgsender'] = absint( $_POST['welcomemsgsender'] );

		// If the new settings are different from the existing settings, then they've been changed. Save them to the database!
		if ( $settings != $existing_settings ) {
			check_admin_referer( 'dpw-admin', 'dpw-admin-nonce' );
			bp_update_option( 'welcomepack', $settings );
			$updated = true;
		}

		return $updated;
	}

	/**
	 * Latest news metabox
	 *
	 * @param array $settings Plugin settings (from DB)
	 * @since 3.0
	 */
	public function metabox_latest_news( $settings) {
		$rss = fetch_feed( 'http://feeds.feedburner.com/BYOTOS' );

		// Check the feed was downloaded (the site could have gone down, or we could have received an error message)
		if ( !is_wp_error( $rss ) ) {
			$content = '<ul>';
			$items = $rss->get_items( 0, $rss->get_item_quantity( 3 ) );

			foreach ( $items as $item )
				$content .= '<li><p><a href="' . esc_url( $item->get_permalink(), null, 'display' ) . '">' . apply_filters( 'dpw_admin_metabox_latest_news', stripslashes( $item->get_title() ) ) . '</a></p></li>';

			echo $content;

		} else {
			echo '<ul><li class="rss">' . __( 'No news; check back later.', 'dpw' ) . '</li></ul>';
		}
	}

	/**
	 * "Help Me Help You" metabox
	 *
	 * @global wpdb $wpdb WordPress database object
	 * @global string $wp_version WordPress version number
	 * @global WP_Rewrite $wp_rewrite WordPress Rewrite object for creating pretty URLs
	 * @global object $wp_rewrite
	 * @param array $settings Plugin settings (from DB)
	 * @since 3.0
	 */
	public function helpushelpyou( $settings ) {
		global $wpdb, $wp_rewrite, $wp_version;

		$active_plugins = array();
		$all_plugins    = apply_filters( 'all_plugins', get_plugins() );

		// Get a list of active plugins
		foreach ( $all_plugins as $filename => $plugin ) {
			if ( 'Welcome Pack' != $plugin['Name'] && 'BuddyPress' != $plugin['Name'] && is_plugin_active( $filename ) )
				$active_plugins[] = $plugin['Name'] . ': ' . $plugin['Version'];
		}
		natcasesort( $active_plugins );

		if ( !$active_plugins )
			$active_plugins[] = __( 'No other plugins are active', 'dpw' );

		// Is multisite active?
		if ( is_multisite() ) {
			if ( is_subdomain_install() )
				$is_multisite = __( 'subdomain', 'dpw' );
			else
				$is_multisite = __( 'subdirectory', 'dpw' );

		} else {
			$is_multisite = __( 'no', 'dpw' );
		}

		// Find out the BuddyPress BP_ROOT_BLOG value
		if ( 1 == constant( 'BP_ROOT_BLOG' ) )
			$is_bp_root_blog = __( 'standard', 'dpw' );
		else
			$is_bp_root_blog = __( 'non-standard', 'dpw' );

		$is_bp_default_child_theme = __( 'no', 'dpw' );
		$theme = current_theme_info();

		// Is the current theme a child theme of BP-Default?
		if ( 'BuddyPress Default' == $theme->parent_theme )
			$is_bp_default_child_theme = __( 'yes', 'dpw' );

		if ( 'BuddyPress Default' == $theme->name )
			$is_bp_default_child_theme = __( 'n/a', 'dpw' );

		// Is the site using pretty permalinks?
	  if ( empty( $wp_rewrite->permalink_structure ) )
			$custom_permalinks = __( 'default', 'dpw' );
		else
			// Or is the site using (almost) pretty permalinks?
			if ( strpos( $wp_rewrite->permalink_structure, 'index.php' ) )
				$custom_permalinks = __( 'almost', 'dpw' );
			else
				$custom_permalinks = __( 'custom', 'dpw' );
	?>

		<p><?php _e( "If you have trouble, a little information about your site goes a long way.", 'dpw' ); ?></p>

		<h4><?php _e( 'Versions', 'dpw' ); ?></h4>
		<ul>
			<li><?php printf( __( 'Welcome Pack: %s', 'dpw' ), WELCOME_PACK_VERSION ); ?></li>
			<li><?php printf( __( 'BP_ROOT_BLOG: %s', 'dpw' ), $is_bp_root_blog ); ?></li>
			<li><?php printf( __( 'BuddyPress: %s', 'dpw' ), BP_VERSION ); ?></li>
			<li><?php printf( __( 'MySQL: %s', 'dpw' ), $wpdb->db_version() ); ?></li>
			<li><?php printf( __( 'Permalinks: %s', 'dpw' ), $custom_permalinks ); ?></li>
			<li><?php printf( __( 'PHP: %s', 'dpw' ), phpversion() ); ?></li>
			<li><?php printf( __( 'WordPress: %s', 'dpw' ), $wp_version ); ?></li>
			<li><?php printf( __( 'WordPress multisite: %s', 'dpw' ), $is_multisite ); ?></li>
		</ul>

		<h4><?php _e( 'Theme', 'dpw' ); ?></h4>
		<ul>
			<li><?php printf( __( 'BP-Default child theme: %s', 'dpw' ), $is_bp_default_child_theme ); ?></li>
			<li><?php printf( __( 'Current theme: %s', 'dpw' ), $theme->name ); ?></li>
		</ul>

		<h4><?php _e( 'Active Plugins', 'dpw' ); ?></h4>
		<ul>
			<?php foreach ( $active_plugins as $plugin ) : ?>
				<li><?php echo esc_html( $plugin ); ?></li>
			<?php endforeach; ?>
		</ul>

	<?php
	}

	/**
	 * Social media sharing metabox
	 *
	 * @param array $settings Plugin settings (from DB)
	 * @since 3.0
	 */
	public function like_this_plugin( $settings ) {
	?>

		<p><?php _e( 'Why not do any or all of the following:', 'dpw' ); ?></p>
		<ul>
			<li><p><a href="http://wordpress.org/extend/plugins/welcome-pack/"><?php _e( 'Give it a five star rating on WordPress.org.', 'dpw' ); ?></a></p></li>
			<li><p><a href="http://buddypress.org/community/groups/welcome-pack/reviews/"><?php _e( 'Write a review on BuddyPress.org.', 'dpw' ); ?></a></p></li>
			<li><p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=P3K7Z7NHWZ5CL&amp;lc=GB&amp;item_name=B%2eY%2eO%2eT%2eO%2eS%20%2d%20BuddyPress%20plugins&amp;currency_code=GBP&amp;bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted"><?php _e( 'Fund development.', 'dpw' ); ?></a></p></li>
			<li>
				<g:plusone size="medium" href="http://wordpress.org/extend/plugins/welcome-pack/"></g:plusone>
				<iframe class="fb" allowTransparency="true" frameborder="0" scrolling="no" src="http://www.facebook.com/plugins/like.php?href=http://wordpress.org/extend/plugins/welcome-pack/&amp;send=false&amp;layout=button_count&amp;width=90&amp;show_faces=false&amp;action=recommend&amp;colorscheme=light&amp;font=arial"></iframe>
			</li>
		</ul>

	<?php
	}

	/**
	 * Paypal donate button metabox
	 *
	 * @param array $settings Plugin settings (from DB)
	 * @since 3.0
	 */ 
	public function paypal( $settings ) {
	?>

		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHJwYJKoZIhvcNAQcEoIIHGDCCBxQCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYAKEgLe2pv19nB47asLSsOP/yLqTfr5+gO16dYtKxmlGS89c/hA+3j6DiUyAkVaD1uSPJ1pnNMHdTd0ApLItNlrGPrCZrHSCb7pJ0v7P7TldOqGf7AitdFdQcecF9dHrY9/hUi2IjUp8Z8Ohp1ku8NMJm8KmBp8kF9DtzBio8yu/TELMAkGBSsOAwIaBQAwgaQGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQI80ZQLMmY6LGAgYBcTZjnEbuPyDT2p6thCPES4nIyAaILWsX0z0UukCrz4fntMXyrzpSS4tLP7Yv0iAvM7IYV34QQZ8USt4wq85AK9TT352yPJzsVN12O4SQ9qOK8Gp+TvCVfQMSMyhipgD+rIQo9xgMwknj6cPYE9xPJiuefw2KjvSgHgHunt6y6EaCCA4cwggODMIIC7KADAgECAgEAMA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTAeFw0wNDAyMTMxMDEzMTVaFw0zNTAyMTMxMDEzMTVaMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAwUdO3fxEzEtcnI7ZKZL412XvZPugoni7i7D7prCe0AtaHTc97CYgm7NsAtJyxNLixmhLV8pyIEaiHXWAh8fPKW+R017+EmXrr9EaquPmsVvTywAAE1PMNOKqo2kl4Gxiz9zZqIajOm1fZGWcGS0f5JQ2kBqNbvbg2/Za+GJ/qwUCAwEAAaOB7jCB6zAdBgNVHQ4EFgQUlp98u8ZvF71ZP1LXChvsENZklGswgbsGA1UdIwSBszCBsIAUlp98u8ZvF71ZP1LXChvsENZklGuhgZSkgZEwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAgV86VpqAWuXvX6Oro4qJ1tYVIT5DgWpE692Ag422H7yRIr/9j/iKG4Thia/Oflx4TdL+IFJBAyPK9v6zZNZtBgPBynXb048hsP16l2vi0k5Q2JKiPDsEfBhGI+HnxLXEaUWAcVfCsQFvd2A1sxRr67ip5y2wwBelUecP3AjJ+YcxggGaMIIBlgIBATCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwCQYFKw4DAhoFAKBdMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTExMDYyNTIzMjkxMVowIwYJKoZIhvcNAQkEMRYEFARFcuDQDlV6K2HZOWBL2WF3dmcTMA0GCSqGSIb3DQEBAQUABIGAoM3lKIbRdureSy8ueYKl8H0cQsMHRrLOEm+15F4TXXuiAbzjRhemiulgtA92OaI3r1w42Bv8Vfh8jISSH++jzynQOn/jwl6lC7a9kn6h5tuKY+00wvIIp90yqUoALkwnhHhz/FoRtXcVN1NK/8Bn2mZ2YVWglnQNSXiwl8Hn0EQ=-----END PKCS7-----">
			<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="<?php esc_attr_e( 'PayPal', 'dpw' ); ?>">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
		</form>

	<?php
	}

	/**
	 * Get an array of email templates
	 *
	 * @return array Key is the template name, value is the filename of the template
	 * @see get_page_templates() WordPress Core
	 * @since 3.0
	 * @static
	 */
	public static function email_get_templates() {
		$themes         = get_themes();
		$theme          = get_current_theme();
		$templates      = $themes[$theme]['Template Files'];

		// Always add the fallback that comes bundled with Welcome Pack
		$page_templates = array( __( 'BP Default', 'dpw' ) => 'welcome_pack_default.php', __( 'Simplicity', 'dpw' ) => 'simplicity.php' );

		if ( is_array( $templates ) ) {
			$base = array( trailingslashit( get_template_directory() ), trailingslashit( get_stylesheet_directory() ) );

			foreach ( $templates as $template ) {
				$basename = str_replace( $base, '', $template );

				// Don't allow template files in subdirectories
				if ( false !== strpos( $basename, '/' ) )
					continue;

				if ( 'functions.php' == $basename )
					continue;

				$template_data = implode( '', file( $template ) );

				$name = '';
				if ( preg_match( '|Email Template:(.*)$|mi', $template_data, $name ) )
					$name = _cleanup_header_comment( $name[1] );

				if ( !empty( $name ) )
					$page_templates[trim( $name )] = $basename;
			}
		}

		return apply_filters( 'dpw_email_get_templates', $page_templates );
	}

	/**
	 * Filter the "enter title here" text on the email CPT screens.
	 *
	 * @param string $title Default text
	 * @return string
	 * @since 3.0
	 */
	public function email_subject_placeholder( $title ) {
		$screen = get_current_screen();

		if ( 'dpw_email' == $screen->post_type )
			$title = __( 'Enter subject here', 'dpw' );

		return apply_filters( 'dpw_email_subject_placeholder', $title );
	}

	/**
	 * Save handler for the email CPT.
	 *
	 * @param int $post_ID
	 * @param WP_Post $post
	 * @since 3.0
	 */
	public function email_maybe_save( $post_ID, $post ) {
		// Check this is an email
		if ( 'dpw_email' != $post->post_type )
			return;

		if ( empty( $_POST['dpw_email_template'] ) && empty( $_POST['dpw_email_for'] ) )
			return;

		// Email template
		if ( !empty( $_POST['dpw_email_template'] ) ) {
			$template = stripslashes( sanitize_text_field( wp_filter_kses( $_POST['dpw_email_template'] ) ) );
			$template = apply_filters( 'dpw_email_maybe_save_template', $template, $post_ID, $post );

			if ( !empty( $template ) )
				update_post_meta( $post_ID, 'welcomepack_template', $template );
		}

		// Email type
		if ( !empty( $_POST['dpw_email_for'] ) ) {
			$type = apply_filters( 'dpw_email_maybe_save_type', absint( $_POST['dpw_email_for'] ), $post_ID, $post );

			if ( $type )
				update_post_meta( $post_ID, 'welcomepack_type', $type );
		}

		do_action( 'dpw_email_maybe_save', $post_ID, $post );
	}

	/**
	 * Function used when setting up the meta boxes for the email post type.
	 *
	 * @since 3.0
	 */
	public function email_meta_box_callback() {
		add_meta_box( 'dpw_template', __( 'Email Attributes', 'dpw' ), array( 'DP_Welcome_Pack_Admin', 'email_meta_box' ), 'dpw_email', 'side' );
	}

	/**
	 * Email template chooser meta box for the email post options
	 *
	 * Based on WordPress core's get_page_templates()
	 *
	 * @global int $post_ID
	 * @since 3.0
	 * @todo The email ID mapping sucks and should be done better; see email_get_types()
	 * @todo Prevent multiple email posts being assigned the same email type
	 */
	public function email_meta_box() {
		global $post_ID;

		$templates = DP_Welcome_Pack_Admin::email_get_templates();
		ksort( $templates );

		// Find out which template this email's using
		$current_template = get_post_meta( $post_ID, 'welcomepack_template', true );
		if ( empty( $current_template ) )
			$current_template = 'welcome_pack_default.php';

		// Find out what email this template is used for
		$current_email_type = get_post_meta( $post_ID, 'welcomepack_type', true );
		if ( empty( $current_email_type ) )
			$current_email_type = 0;
	?>

	<p><strong><?php _e( 'Use this email for:', 'dpw' ); ?></strong></p>
	<label class="screen-reader-text" for="dpw_email_for"><?php _e( 'Use this email for:', 'dpw' ); ?></label>
	<select name="dpw_email_for" id="dpw_email_for">
		<option value="0" <?php selected( $current_email_type, 0 ); ?>><?php _e( '-----', 'dpw' ); ?></option>
		<option value="1" <?php selected( $current_email_type, 1 ); ?>><?php _e( 'Account activation', 'dpw' ); ?></option>
		<option value="2" <?php selected( $current_email_type, 2 ); ?>><?php _e( 'Account and blog activation', 'dpw' ); ?></option>
		<option value="10" <?php selected( $current_email_type, 10 ); ?>><?php _e( 'Friendship request accepted', 'dpw' ); ?></option>
		<option value="4" <?php selected( $current_email_type, 4 ); ?>><?php _e( 'Group details updated', 'dpw' ); ?></option>
		<option value="5" <?php selected( $current_email_type, 5 ); ?>><?php _e( 'Group membership request', 'dpw' ); ?></option>
		<option value="6" <?php selected( $current_email_type, 6 ); ?>><?php _e( 'Group membership request accepted', 'dpw' ); ?></option>
		<option value="7" <?php selected( $current_email_type, 7 ); ?>><?php _e( 'Group membership request rejected', 'dpw' ); ?></option>
		<option value="9" <?php selected( $current_email_type, 9 ); ?>><?php _e( 'Invitation to a group', 'dpw' ); ?></option>
		<option value="12" <?php selected( $current_email_type, 12 ); ?>><?php _e( 'Mentioned in an update', 'dpw' ); ?></option>
		<option value="11" <?php selected( $current_email_type, 11 ); ?>><?php _e( 'New friendship request', 'dpw' ); ?></option>
		<option value="3" <?php selected( $current_email_type, 3 ); ?>><?php _e( 'New private message', 'dpw' ); ?></option>
		<option value="8" <?php selected( $current_email_type, 8 ); ?>><?php _e( 'Promoted in a group', 'dpw' ); ?></option>
		<option value="14" <?php selected( $current_email_type, 14 ); ?>><?php _e( 'Replied to a comment', 'dpw' ); ?></option>
		<option value="13" <?php selected( $current_email_type, 13 ); ?>><?php _e( 'Replied to an update', 'dpw' ); ?></option>

		<?php do_action( 'dpw_email_meta_box', $current_email_type ); ?>
	</select>

	<p><strong><?php _e( 'Template', 'dpw' ); ?></strong></p>
	<label class="screen-reader-text" for="dpw_email_template"><?php _e( 'Email Template', 'dpw' ); ?></label>
	<select name="dpw_email_template" id="dpw_email_template">
		<?php
			foreach ( array_keys( $templates ) as $template ) {
				if ( $current_template == $templates[$template] )
					$selected = " selected='selected'";
				else
					$selected = '';

				echo "\n\t<option value='" . $templates[$template] . "' $selected>$template</option>";
			}
		?>
	</select>

	<?php
	}
}
?>