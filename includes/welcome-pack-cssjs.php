<?php
function dpw_admin_add_css_js() {
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'welcomepack-js', plugins_url( '/js/admin.js', __FILE__ ) );
	wp_enqueue_style( 'welcomepack', plugins_url( '/css/admin.css', __FILE__ ) );
	wp_enqueue_style( 'welcomepack-bpstyles', plugins_url( '/css/bpstyles.css', __FILE__ ) );
}
add_action( 'admin_print_styles-buddypress_page_welcome-pack', 'dpw_admin_add_css_js' );
?>