<?php

/**
 * Add the WordPress News widget to the dashboard
 *
 *
 *
 * @since 3.8.0
 *
 */
function add_wpnews_dashboard_widget() {
	if ( is_blog_admin() )
		// Note it would be ideal to have this loaded by default in the right column
		// Currently there is no way to set a $location arg in wp_add_dashboard_widget()
		// Would love to add that in core when merging this in.
		wp_add_dashboard_widget(
			'dashboard_rss',
			__( 'WordPress News' ),
			'wp_dashboard_rss',
			'wp_dashboard_news_feed_control'
		);
}
add_action( 'wp_dashboard_setup', 'add_wpnews_dashboard_widget' );

/**
 * Returns default WordPress News feeds
 *
 *
 *
 * @since 3.8.0
 *
 */
function wp_dashboard_default_feeds() {
	return array(
		'news'   => array(
			'link'         => apply_filters( 'dashboard_primary_link', __( 'http://wordpress.org/news/' ) ),
			'url'          => apply_filters( 'dashboard_primary_feed', __( 'http://wordpress.org/news/feed/' ) ),
			'title'        => '',
			'items'        => 1,
			'show_summary' => 1,
			'show_author'  => 0,
			'show_date'    => 1,
		), 
		'planet' => array(
			'link'         => apply_filters( 'dashboard_secondary_link', __( 'http://planet.wordpress.org/' ) ),
			'url'          => apply_filters( 'dashboard_secondary_feed', __( 'http://planet.wordpress.org/feed/' ) ),
			'title'        => '',
			'items'        => 3,
			'show_summary' => 0,
			'show_author'  => 0,
			'show_date'    => 0,
		), 
		'plugins' => array(
			'link'         => '',
			'url'          => array(
					'popular' => 'http://wordpress.org/plugins/rss/browse/popular/',
					'new'     => 'http://wordpress.org/plugins/rss/browse/new/'
			),
			'title'        => '',
			'items'        => 1,
			'show_summary' => 0,
			'show_author'  => 0,
			'show_date'    => 0,
		)
	);
}

/**
 * Check for chached feeds
 *
 *
 *
 * @since 3.8.0
 *
 */
function wp_dashboard_rss() {
	$default_feeds = wp_dashboard_default_feeds();

	$widget_options = get_option( 'dashboard_widget_options' );

	if ( !$widget_options || !is_array($widget_options) )
		$widget_options = array();

	if ( ! isset( $widget_options['dashboard_rss'] ) ) {
		$widget_options['dashboard_rss'] = $default_feeds;
		update_option( 'dashboard_widget_options', $widget_options );
	}

	foreach( $default_feeds as $key => $value ) {
		$default_urls[] = $value['url'];
	}

	wp_dashboard_cached_rss_widget( 'dashboard_rss', 'wp_dashboard_news_output', $default_urls );
}

/**
 * Display news feeds
 *
 *
 *
 * @since 3.8.0
 *
 */
function wp_dashboard_news_output() {
	$widgets = get_option( 'dashboard_widget_options' );

	foreach( $widgets['dashboard_rss'] as $type => $args ) {
		$args['type'] = $type;
		echo '<div class="rss-widget">';
		wp_widget_news_output( $args['url'], $args );
		echo "</div>";
	}
}

/**
 * Generate code for each news feed
 *
 *
 *
 * @since 3.8.0
 *
 */
function wp_widget_news_output( $rss, $args = array() ) {

	// Regular RSS feeds
	if ( isset( $args['type'] ) && 'plugins' != $args['type'] ) 	
		return wp_widget_rss_output( $rss, $args );

	// Plugin feeds plus link to install them
	$popular = fetch_feed( $args['url']['popular'] );
	$new     = fetch_feed( $args['url']['new'] );

	if ( false === $plugin_slugs = get_transient( 'plugin_slugs' ) ) {
		$plugin_slugs = array_keys( get_plugins() );
		set_transient( 'plugin_slugs', $plugin_slugs, DAY_IN_SECONDS );
	}

	echo '<ul>';

	foreach ( array(
		'popular' => __( 'Popular Plugin' ),
		'new'     => __( 'Newest Plugin' )
	) as $feed => $label ) {
		if ( is_wp_error($$feed) || !$$feed->get_item_quantity() )
			continue;

		$items = $$feed->get_items(0, 5);

		// Pick a random, non-installed plugin
		while ( true ) {
			// Abort this foreach loop iteration if there's no plugins left of this type
			if ( 0 == count($items) )
				continue 2;

			$item_key = array_rand($items);
			$item = $items[$item_key];

			list($link, $frag) = explode( '#', $item->get_link() );

			$link = esc_url($link);
			if ( preg_match( '|/([^/]+?)/?$|', $link, $matches ) )
				$slug = $matches[1];
			else {
				unset( $items[$item_key] );
				continue;
			}

			// Is this random plugin's slug already installed? If so, try again.
			reset( $plugin_slugs );
			foreach ( $plugin_slugs as $plugin_slug ) {
				if ( $slug == substr( $plugin_slug, 0, strlen( $slug ) ) ) {
					unset( $items[$item_key] );
					continue 2;
				}
			}

			// If we get to this point, then the random plugin isn't installed and we can stop the while().
			break;
		}

		// Eliminate some common badly formed plugin descriptions
		while ( ( null !== $item_key = array_rand($items) ) && false !== strpos( $items[$item_key]->get_description(), 'Plugin Name:' ) )
			unset($items[$item_key]);

		if ( !isset($items[$item_key]) )
			continue;

		$title = esc_html( $item->get_title() );

		$description = esc_html( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) ) ) );

		$ilink = wp_nonce_url('plugin-install.php?tab=plugin-information&plugin=' . $slug, 'install-plugin_' . $slug) . '&amp;TB_iframe=true&amp;width=600&amp;height=800';

		echo "<li><span>$label:</span> <a href='$link'>$title</a></h5>&nbsp;<span>(<a href='$ilink' class='thickbox' title='$title'>" . __( 'Install' ) . "</a>)</span></li>";

		$$feed->__destruct();
		unset( $$feed );
	}
	
	echo '</ul>';
}

/**
 * Adds control option to news feed
 *
 *
 *
 * @since 3.8.0
 *
 */
function wp_dashboard_news_feed_control() {
	wp_dashboard_news_control( 'dashboard_rss' );
}

/**
 * Generates code to show news feed control
 *
 *
 *
 * @since 3.8.0
 *
 */
function wp_dashboard_news_control( $widget_id, $form_inputs = array() ) {

	// Single feed
	if ( isset( $widget_options[$widget_id]['url'] ) )
		return wp_dashboard_rss_control( $widget_id, $form_inputs );

	// Array of multiple feeds
	if ( !$widget_options = get_option( 'dashboard_widget_options' ) )
		$widget_options = array();

	if ( !isset($widget_options[$widget_id]) )
		$widget_options[$widget_id] = array();

	foreach ( $widget_options[$widget_id] as $name => $options ) {
		$options['number'] = $name;
		
		if ( 'dashboard_rss' == $widget_id )
			$form_inputs = array(
				'title'        => false,
				'title'        => false,
				'show_summary' => false,
				'show_author'  => false,
				'show_date'    => false,
				'items'        => false
		);
		
		if ( 'plugins' == $name )
			$form_inputs['url'] = false;
		
		wp_widget_rss_form( $options, $form_inputs );
	}

	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['widget-rss']) ) {
		$_POST['widget-rss'] = wp_unslash( $_POST['widget-rss'] );
		
		$widget_options[$widget_id] = wp_dashboard_default_feeds();
		
		foreach ( $widget_options[$widget_id] as $name => $options ) {
			if ( isset( $_POST['widget-rss'][$name]['url'] ) )
				$widget_options[$widget_id][$name]['url'] = esc_url_raw( strip_tags( $_POST['widget-rss'][$name]['url'] ) );
		}
		update_option( 'dashboard_widget_options', $widget_options );
		$cache_key = 'dash_' . md5( $widget_id );
		delete_transient( $cache_key );
	}
}

/**
 * Returns reset form plus new draft via AJAX call
 *
 *
 *
 * @since 3.8.0
 *
 */
function wp_dashboard_news_widget() {
	require_once ABSPATH . 'wp-admin/includes/dashboard.php';
	wp_dashboard_rss();
	wp_die();
}
add_action( 'wp_ajax_dashboard_news_widget', 'wp_dashboard_news_widget' );