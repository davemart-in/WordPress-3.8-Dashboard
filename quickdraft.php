<?php

// Add the QuickDraft widget to the dashboard
add_action( 'wp_dashboard_setup', 'add_quickdraft_dashboard_widget' );

function add_quickdraft_dashboard_widget() {
	if ( is_blog_admin() && current_user_can( 'edit_posts' ) )
		add_meta_box('dashboard_quick_draft', __( 'Quick Draft' ), 'wp_dashboard_quick_draft', 'dashboard', 'side', 'high');
}

// The QuickDraft widget display and creation of drafts
function wp_dashboard_quick_draft() {
	global $post_ID;

	if ( 'post' === strtolower( $_SERVER['REQUEST_METHOD'] ) && isset( $_POST['action'] ) && 0 === strpos( $_POST['action'], 'post-quickpress' ) && (int) $_POST['post_ID'] ) {
		$view = get_permalink( $_POST['post_ID'] );
		$edit = esc_url( get_edit_post_link( $_POST['post_ID'] ) );
		if ( 'post-quickpress-publish' == $_POST['action'] ) {
			if ( current_user_can('publish_posts') )
				printf( '<div class="updated"><p>' . __( 'Post published. <a href="%s">View post</a> | <a href="%s">Edit post</a>' ) . '</p></div>', esc_url( $view ), $edit );
			else
				printf( '<div class="updated"><p>' . __( 'Post submitted. <a href="%s">Preview post</a> | <a href="%s">Edit post</a>' ) . '</p></div>', esc_url( add_query_arg( 'preview', 1, $view ) ), $edit );
		} else {
			printf( '<div class="updated"><p>' . __( 'Draft saved. <a href="%s">Preview post</a> | <a href="%s">Edit post</a>' ) . '</p></div>', esc_url( add_query_arg( 'preview', 1, $view ) ), $edit );
		}
		printf('<p class="easy-blogging">' . __('You can also try %s, easy blogging from anywhere on the Web.') . '</p>', '<a href="' . esc_url( admin_url( 'tools.php' ) ) . '">' . __('Press This') . '</a>' );
		$_REQUEST = array(); // hack for get_default_post_to_edit()
	}

	/* Check if a new auto-draft (= no new post_ID) is needed or if the old can be used */
	$last_post_id = (int) get_user_option( 'dashboard_quick_press_last_post_id' ); // Get the last post_ID
	if ( $last_post_id ) {
		$post = get_post( $last_post_id );
		if ( empty( $post ) || $post->post_status != 'auto-draft' ) { // auto-draft doesn't exists anymore
			$post = get_default_post_to_edit('post', true);
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

	$media_settings = array(
		'id' => $post->ID,
		'nonce' => wp_create_nonce( 'update-post_' . $post->ID ),
	);

	if ( current_theme_supports( 'post-thumbnails', $post->post_type ) && post_type_supports( $post->post_type, 'thumbnail' ) ) {
		$featured_image_id = get_post_meta( $post->ID, '_thumbnail_id', true );
		$media_settings['featuredImageId'] = $featured_image_id ? $featured_image_id : -1;
	}
?>

	<form name="post" action="<?php echo esc_url( admin_url( 'post.php' ) ); ?>" method="post" id="quick-press">
		<div class="input-text-wrap" id="title-wrap">
			<label class="screen-reader-text prompt" for="title" id="title-prompt-text"><?php _e( 'Enter title here' ); ?></label>
			<input type="text" name="post_title" id="title" autocomplete="off" value="<?php echo esc_attr( $post->post_title ); ?>" />
		</div>

		<?php if ( current_user_can( 'upload_files' ) ) : ?>
		<div id="wp-content-wrap" class="wp-editor-wrap hide-if-no-js wp-media-buttons">
			<?php do_action( 'media_buttons', 'content' ); ?>
		</div>
		<?php endif; ?>

		<div class="textarea-wrap">
			<label class="screen-reader-text" for="content"><?php _e( 'Content' ); ?></label>
			<textarea name="content" id="content" class="mceEditor" rows="3" cols="15"><?php echo esc_textarea( $post->post_content ); ?></textarea>
		</div>

		<script type="text/javascript">
		edCanvas = document.getElementById('content');
		edInsertContent = null;
		<?php if ( $_POST ) : ?>
		wp.media.editor.remove('content');
		wp.media.view.settings.post = <?php echo json_encode( $media_settings ); // big juicy hack. ?>;
		wp.media.editor.add('content');
		<?php endif; ?>
		</script>

		<div class="input-text-wrap" id="tags-input-wrap">
			<label class="screen-reader-text prompt" for="tags-input" id="tags-input-prompt-text"><?php _e( 'Tags (separate with commas)' ); ?></label>
			<input type="text" name="tags_input" id="tags-input" value="<?php echo get_tags_to_edit( $post->ID ); ?>" />
		</div>

		<p class="submit">
			<span id="publishing-action">
				<input type="submit" name="publish" id="publish" accesskey="p" class="button-primary" value="<?php current_user_can('publish_posts') ? esc_attr_e('Publish') : esc_attr_e('Submit for Review'); ?>" />
				<span class="spinner"></span>
			</span>
			<input type="hidden" name="action" id="quickpost-action" value="post-quickpress-save" />
			<input type="hidden" name="post_ID" value="<?php echo $post_ID; ?>" />
			<input type="hidden" name="post_type" value="post" />
			<?php wp_nonce_field('add-post'); ?>
			<?php submit_button( __( 'Save Draft' ), 'button', 'save', false, array( 'id' => 'save-post' ) ); ?>
			<input type="reset" value="<?php esc_attr_e( 'Reset' ); ?>" class="button" />
			<br class="clear" />
		</p>

	</form>

<?php
	wp_dashboard_recent_quickdrafts();
}

function wp_dashboard_recent_quickdrafts( $drafts = false ) {
	if ( !$drafts ) {
		$drafts_query = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'draft',
			'author'         => $GLOBALS['current_user']->ID,
			'posts_per_page' => 5,
			'orderby'        => 'modified',
			'order'          => 'DESC'
		) );
		$drafts =& $drafts_query->posts;
	}

	if ( $drafts && is_array( $drafts ) ) {
		$list = array();
		foreach ( $drafts as $draft ) {
			$url = get_edit_post_link( $draft->ID );
			$title = _draft_or_post_title( $draft->ID );
			$item = "<h4><a href='$url' title='" . sprintf( __( 'Edit &#8220;%s&#8221;' ), esc_attr( $title ) ) . "'>" . esc_html($title) . "</a> <abbr title='" . get_the_time(__('Y/m/d g:i:s A'), $draft) . "'>" . get_the_time( get_option( 'date_format' ), $draft ) . '</abbr></h4>';
			if ( $the_content = wp_trim_words( $draft->post_content, 10 ) )
				$item .= '<p>' . $the_content . '</p>';
			$list[] = $item;
		}
?>
	<div class="drafts">
		<p class="view-all"><a href="edit.php?post_status=draft" ><?php _e('View all'); ?></a></p>
		<p class="title"><?php _e('Drafts'); ?></p>
		<ul id="draft-list">
			<li><?php echo join( "</li>\n<li>", $list ); ?></li>
		</ul>
	</div>
<?php } else { ?>
	<div class="drafts"><p><?php _e('There are no drafts at the moment'); ?></p></div>
<?php }
}
