<?php

// add the `Activity` widget to the dashboard
add_action( 'wp_dashboard_setup', 'add_activity_dashboard_widget' );
function add_activity_dashboard_widget() {
	add_meta_box( 'dashboard_activity', __( 'Activity' ), 'wp_dashboard_activity', 'dashboard', 'normal', 'high' );
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}

// callback function for `Activity` widget
function wp_dashboard_activity() {

	echo '<div id="activity-widget">';

	dash_publishing_soon();
	//dash_comments();

	echo '</div>';

}

// show `Publishing Soon` section
function dash_publishing_soon( $display = 2, $max = 5 ) {

	$future_posts = new WP_Query(array(
		'post_type' => 'post',
		'post_status' => 'future',
		'orderby' => 'date',
		'order' => 'ASC',
		'posts_per_page' => intval( $max )
	));

	if ( $future_posts->have_posts() ) {

		echo '<div id="future-posts">';
		echo '<strong>' . __( 'Publishing Soon' ) . '</strong>';

		if ( $future_posts->post_count > $display ) {
			echo '<small class="show-more"><a href="#">' . sprintf( __( 'See %s more&hellip;'), $future_posts->post_count - intval( $display ) ) . '</a></small>';
		}

		echo '<ul>';

		$i = 0;
		while ( $future_posts->have_posts() ) {
			$future_posts->the_post();
			printf(
				'<li%s><span>%s, %s</span> <a href="%s">%s</a></li>',
				( $i >= intval ( $display ) ? ' class="hidden"' : '' ),
				dash_relative_date( get_the_time( 'U' ) ),
				get_the_time(),
				get_edit_post_link(),
				get_the_title()
			);
			$i++;
		}

		echo '</ul>';
		echo '</div>';

	}

	wp_reset_postdata();

}

// show `Comments` section
function dash_comments( $display = 5 ) {

	$comments = new WP_Comment_Query(array(
		'status' => '',
		'number' => intval( $display )
	));

	echo '<div id="latest-comments">';
	echo '<strong>' . __( 'Comments' ) . '</strong>';

	echo '</ul>';
	echo '</div>';

}

// return relative date for given timestamp
function dash_relative_date( $time ) {

	$diff = floor( ( $time - time() ) / DAY_IN_SECONDS );

	if ( $diff == 0 ) {
		return __( 'Today' );
	}

	if ( $diff == 1 ) {
		return __( 'Tomorrow' );
	}

	return date( 'M jS', $time);

}
