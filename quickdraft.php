<?php

/**
 * Add the QuickDraft widget to the dashboard
 *
 *
 *
 * @since 3.8.0
 *
 */
function add_quickdraft_dashboard_widget() {
	if ( is_blog_admin() && current_user_can( 'edit_posts' ) )
		add_meta_box(
		'dashboard_quick_draft',
		__( 'Quick Draft' ),
		'wp_dashboard_quick_draft',
		'dashboard',
		'side',
		'high'
	);
}
add_action( 'wp_dashboard_setup', 'add_quickdraft_dashboard_widget' );

/**
 * Quick Draft $_POST is handled here
 *
 *
 *
 * @since 3.8.0
 *
 */
function dashboard_plugin_quickdraft_admin_post() {
	$post = get_post( $_REQUEST['post_ID'] );
	check_admin_referer( 'add-' . $post->post_type );
	edit_post();
	return wp_dashboard_quick_draft();
}
add_action( 'admin_post_new-quickdraft-post', 'dashboard_plugin_quickdraft_admin_post' );

/**
 * The Quick Draft widget display and creation of drafts
 *
 *
 *
 * @since 3.8.0
 *
 */
function wp_dashboard_quick_draft() {
	global $post_ID;

	/* Check if a new auto-draft (= no new post_ID) is needed or if the old can be used */
	$last_post_id = (int) get_user_option( 'dashboard_quick_press_last_post_id' ); // Get the last post_ID
	if ( $last_post_id ) {
		$post = get_post( $last_post_id );
		if ( empty( $post ) || $post->post_status != 'auto-draft' ) { // auto-draft doesn't exists anymore
			$post = get_default_post_to_edit( 'post', true );
			update_user_option( get_current_user_id(), 'dashboard_quick_press_last_post_id', (int) $post->ID ); // Save post_ID
		} else {
			$post->post_title = ''; // Remove the auto draft title
		}
	} else {
		$post = get_default_post_to_edit( 'post' , true);
		$user_id = get_current_user_id();
		// Don't create an option if this is a super admin who does not belong to this site.
		if ( ! ( is_super_admin( $user_id ) && ! in_array( get_current_blog_id(), array_keys( get_blogs_of_user( $user_id ) ) ) ) )
			update_user_option( $user_id, 'dashboard_quick_press_last_post_id', (int) $post->ID ); // Save post_ID
	}

	$post_ID = (int) $post->ID;
?>

	<form name="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="quick-press" class="initial-form">
		<div class="input-text-wrap" id="title-wrap">
			<label class="screen-reader-text prompt" for="title" id="title-prompt-text"><?php _e( "What's on your mind?" ); ?></label>
			<input type="text" name="post_title" id="title" autocomplete="off" />
		</div>

		<div class="textarea-wrap" id="description-wrap">
			<label class="screen-reader-text prompt" for="content" id="content-prompt-text"><?php _e( 'Enter a description' ); ?></label>
			<textarea name="content" id="content" class="mceEditor" rows="3" cols="15"></textarea>
		</div>

		<p class="submit">
			<input type="hidden" name="action" id="quickpost-action" value="new-quickdraft-post" />
			<input type="hidden" name="post_ID" value="<?php echo $post_ID; ?>" />
			<input type="hidden" name="post_type" value="post" />
			<?php wp_nonce_field( 'add-post' ); ?>
			<?php submit_button( __( 'Save Draft' ), 'primary', 'save', false, array( 'id' => 'save-post' ) ); ?>
			<br class="clear" />
		</p>

	</form>

<?php
	wp_dashboard_recent_quickdrafts();
}

/**
 * Show `Recent Drafts` below Quick Draft form
 *
 *
 *
 * @since 3.8.0
 *
 */
function wp_dashboard_recent_quickdrafts( $drafts = false ) {
	if ( !$drafts ) {
		$drafts_query = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'draft',
			'author'         => $GLOBALS['current_user']->ID,
			'posts_per_page' => 4,
			'orderby'        => 'modified',
			'order'          => 'DESC'
		) );
		$drafts =& $drafts_query->posts;
	}

	if ( $drafts && is_array( $drafts ) ) {
		$list = array();
		$draft_count = 0;
		foreach ( $drafts as $draft ) {
			if ( 3 == $draft_count )
				break;
			
			$draft_count++;
			
			$url = get_edit_post_link( $draft->ID );
			$title = _draft_or_post_title( $draft->ID );
			$item = '<a href="' . $url . '" title="' . sprintf( __( 'Edit &#8220;%s&#8221;' ), esc_attr( $title ) ) . '">' . esc_html( $title ) . '</a> <time datetime="' . get_the_time( 'c', $draft) . '">' . get_the_time( get_option( 'date_format' ), $draft ) . '</time>';
			if ( $the_content = wp_trim_words( $draft->post_content, 10 ) )
				$item .= '<p>' . $the_content . '</p>';
			$list[] = $item;
		}
?>
	<div class="drafts">
		<?php if ( 3 < count($drafts) ) { ?>
		<p class="view-all"><a href="edit.php?post_status=draft" ><?php _e( 'View all' ); ?></a></p>
		<?php } ?>
		<h4><?php _e('Drafts'); ?></h4>
		<ul id="draft-list">
			<li><?php echo join( "</li>\n<li>", $list ); ?></li>
		</ul>
	</div>
<?php }
}