(function ($) {
	$(document).ready( function() {
		$( '.show-more a' ).on( 'click', function(e) {
			$( this ).fadeOut().closest('.activity-block').find( 'li.hidden' ).fadeIn().removeClass( 'hidden' );
			e.preventDefault();
		})
	});
}(jQuery));