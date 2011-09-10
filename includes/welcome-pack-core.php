<?php

/**
 * Register email post type
 *
 * @since 3.0
 */
function dpw_register_post_types() {
	$email_labels = array(
		'name'               => __( 'Emails',                   'dpw' ),
		'singular_name'      => __( 'Email',                    'dpw' ),
		'add_new'            => __( 'New email',                'dpw' ),
		'add_new_item'       => __( 'Create new email',         'dpw' ),
		'edit'               => __( 'Edit',                     'dpw' ),
		'edit_item'          => __( 'Edit email',               'dpw' ),
		'new_item'           => __( 'New email',                'dpw' ),
		'view'               => __( 'View email',               'dpw' ),
		'view_item'          => __( 'View email',               'dpw' ),
		'search_items'       => __( 'Search emails',            'dpw' ),
		'not_found'          => __( 'No emails found',          'dpw' ),
		'not_found_in_trash' => __( 'No emails found in Trash', 'dpw' )
	);

	$email_supports = array(
		'editor',
		'page-attributes',
		'revisions',
		'title'
	);

	$email_cpt = array(
		'labels'          => $email_labels,
		'public'          => false,
		'show_in_menu'    => false,
		'show_ui'         => true,
		'supports'        => $email_supports
	);

	register_post_type( 'dpw_email', $email_cpt );
}
add_action( 'init', 'dpw_register_post_types' );

/**
 * The main workhorse where the friends, groups and welcome message features happen.
 *
 * @param int $user_id ID of the new user
 * @since 2.0
 */
function dpw_user_registration( $user_id ) {
	$settings = get_site_option( 'welcomepack' );

	// Friends
	if ( !empty( $settings['friendstoggle'] ) && bp_is_active( 'friends' ) ) {
		if ( empty( $settings['friends'] ) )
			break;

		foreach ( (array)$settings['friends'] as $friend_id )
			friends_add_friend( (int)$friend_id, $user_id, constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) );
	}

	// Groups
	if ( !empty( $settings['groupstoggle'] ) && bp_is_active( 'groups' ) ) {
		if ( empty( $settings['groups'] ) )
			break;

		foreach ( (array)$settings['groups'] as $group_id ) {
			$group = new BP_Groups_Group( (int)$group_id );
			groups_invite_user( array( 'user_id' => $user_id, 'group_id' => (int)$group_id, 'inviter_id' => $group->creator_id, 'is_confirmed' => constant( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS' ) ) );
			groups_send_invites( $group->creator_id, (int)$group_id );
		}
	}

	// Welcome message
	if ( !empty( $settings['welcomemsgtoggle'] ) && bp_is_active( 'messages' ) ) {
		if ( empty( $settings['welcomemsgsender'] ) || empty( $settings['welcomemsgsubject'] ) || empty( $settings['welcomemsg'] ) )
			break;

		messages_new_message( array( 'sender_id' => $settings['welcomemsgsender'], 'recipients' => $user_id, 'subject' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsgsubject'], $user_id ), 'content' => apply_filters( 'dpw_keyword_replacement', $settings['welcomemsg'], $user_id ) ) );
	}
}
add_action( 'bp_core_activated_user', 'dpw_user_registration' );
?>