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


?>