(function ($) {
	jQuery(document).ready(function ($) {
		// Update main column count on load
		updateColumnCount();
		
		// Show dashed box only on sort
		var emptyContainers = $('.postbox-container .empty-container');
		$('.meta-box-sortables').on('sortstart', function(event, ui) {
			emptyContainers.css('border', '3px dashed #CCCCCC');	
		}).on( 'sortbeforestop', function(event, ui) {
			emptyContainers.css('border', 'none');
		}).on( 'sortover', function(event, ui) {
			$(event.target).find(emptyContainers).css('border', 'none');
		}).on( 'sortout', function(event, ui) {
			$(event.target).find(emptyContainers).css('border', '3px dashed #CCCCCC');
		});
	});
	
	jQuery(window).resize( _.debounce( function(){
		updateColumnCount();
	}, 30) );
	
	function updateColumnCount() {
		var cols = 1,
			windowWidth = parseInt(jQuery(window).width());
		if (799 < windowWidth && 1299 > windowWidth)
			cols = 2;
		if (1300 < windowWidth && 1799 > windowWidth)
			cols = 3;
		if (1800 < windowWidth)
			cols = 4;
		jQuery('.metabox-holder').attr('class', jQuery('.metabox-holder').attr('class').replace(/columns-\d+/, 'columns-' + cols));
	}
}(jQuery));