;( function( $, window, document, undefined ) {

	'use strict';

	$(document).ready( function() {

		var $future_posts = $( '#future-posts' );

		$future_posts.find( '.show-more' ).on( 'click', function() {
			$future_posts.find( 'li.hidden' ).fadeIn().removeClass( 'hidden' );
			$( this ).fadeOut();
		})

	});



})( jQuery, window, document );