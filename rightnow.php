<?php
/**
 * Add a new Right Now widget
 *
 */

/**
 * Display file upload quota on dashboard.
 *
 * Runs on the activity_box_end hook in wp_dashboard_right_now().
 *
 * @since 3.0.0
 *
 * @return bool True if not multisite, user can't upload files, or the space check option is disabled.
 */
function dash_new_dashboard_quota() {
	if ( !is_multisite() || !current_user_can( 'upload_files' ) || get_site_option( 'upload_space_check_disabled' ) )
		return true;

	$quota = get_space_allowed();
	$used = get_space_used();

	if ( $used > $quota )
		$percentused = '100';
	else
		$percentused = ( $used / $quota ) * 100;
		$used_class = ( $percentused >= 70 ) ? ' warning' : '';
		$used = round( $used, 2 );
		$percentused = number_format( $percentused );

	?>
	<h4 class="mu-storage"><?php _e( 'Storage Space' ); ?></h4>
	<div class="mu-storage">
	<ul>
		<li class="storage-count">
			<?php printf(
				'<a href="%1$s" title="%3$s">%2$sMB %4$s</a>',
				esc_url( admin_url( 'upload.php' ) ),
				number_format_i18n( $quota ),
				__( 'Manage Uploads' ),
				__( 'Space Allowed' )
			); ?>
		</li><li class="storage-count <?php echo $used_class; ?>">
			<?php printf(
				'<a href="%1$s" title="%4$s" class="musublink">%2$sMB (%3$s%%) %5$s</a>',
				esc_url( admin_url( 'upload.php' ) ),
				number_format_i18n( $used, 2 ),
				$percentused,
				__( 'Manage Uploads' ),
				__( 'Space Used' )
			); ?>
		</li>
	</ul>
	</div>
	<?php
}

/**
 * Add `Right Now` widget to the dashboard
 *
 *
 *
 * @since 3.8.0
 *
 */
function dash_add_new_right_now() {
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'side' );
	add_meta_box(
		'dash-right-now',
		'Site Content',
		'dash_new_right_now',
		'dashboard',
		'normal',
		'high'
	);
	remove_action( 'activity_box_end', 'wp_dashboard_quota' );
	add_action( 'activity_box_end', 'dash_new_dashboard_quota' );
}
add_action( 'wp_dashboard_setup', 'dash_add_new_right_now' );

/**
 * Renders new slimmed down Right Now widget
 *
 *
 *
 * @since 3.8.0
 *
 */
function dash_new_right_now() { 
	$theme = wp_get_theme();
	if ( current_user_can( 'switch_themes' ) )
		$theme_name = sprintf( '<a href="themes.php">%1$s</a>', $theme->display('Name') );
	else
		$theme_name = $theme->display('Name');
?>
	<div class="main">
	<ul>
	<?php
	do_action( 'rightnow_list_start' );
	// Using show_in_nav_menus as my arg for grabbing what post types should show, is there better?
	$post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'objects' );
	$post_types = (array) apply_filters( 'rightnow_post_types', $post_types );
	foreach ( $post_types as $post_type => $post_type_obj ){
		$num_posts = wp_count_posts( $post_type );
		if ( $num_posts ) {
			printf(
				'<li class="%1$s-count"><a href="edit.php?post_type=%1$s">%2$s %3$s</a></li>', 
				$post_type,
				number_format_i18n( $num_posts->publish ), 
				$post_type_obj->label 
			);
		}
	}
	// Comments
	$num_comm = wp_count_comments();
	if ( $num_comm ) {
		$text = _n( 'comment', 'comments', $num_comm->total_comments );
		printf(
			'<li class="comment-count"><a href="edit-comments.php">%1$s %2$s</a></li>', 
			number_format_i18n( $num_comm->total_comments ), 
			$text
		);
		if ( $num_comm->moderated ) {
			$text = _n( 'in moderation', 'in moderation', $num_comm->total_comments );
			printf(
				'<li class="comment-mod-count"><a href="edit-comments.php?comment_status=moderated">%1$s %2$s</a></li>', 
				number_format_i18n( $num_comm->moderated ), 
				$text
			);
		}
	}
	do_action( 'rightnow_list_end' );
	?>
	</ul>
	<p><?php printf( __( 'WordPress %1$s running %2$s theme.' ), get_bloginfo( 'version', 'display' ), $theme_name ); ?></p>
	</div>

	<?php
	// activity_box_end has a core action, but only prints content when multisite. 
	// Using an output buffer is the only way to really check if anything's displayed here.
	ob_start();
	do_action( 'rightnow_end' );
	do_action( 'activity_box_end' );
	$actions = ob_get_clean();

	if ( !empty( $actions ) ) : ?>
	<div class="sub">
		<?php echo $actions; ?>
	</div>
	<?php endif; ?>
<?php }