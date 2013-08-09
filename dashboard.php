<?php
/*
Plugin Name: Dashboard Redux
Plugin URI: http://wordpress.org/plugins/dashboard
Description: Feature dev plugin for an action-oriented dashboard. Potentially a failure.
Author: Helen Hou-Sandi
Version: 0.1
Author URI: http://profiles.wordpress.org/helen/
*/

function dddd_admin_menu() {
	add_dashboard_page( 'Dashboard Redux', 'Dashboard Redux', 'read', 'dddd-dashboard-redux', 'dddd_admin_page' );
}
add_action( 'admin_menu', 'dddd_admin_menu' );

function dddd_admin_page() {
?>
<div class="wrap">

	<?php screen_icon(); ?>

	<h2><?php esc_html_e( 'Dashboard Redux', 'dddd' ); ?></h2>

</div><!-- .wrap -->
<?php
}
