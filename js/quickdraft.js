(function ($) {
	var quickPressLoad;
	/* QuickPress */
	quickPressLoad = function() {
		var act = $('#quickpost-action'), t;
		t = $('#quick-press').submit( function() {
			$('#dashboard_quick_draft #publishing-action .spinner').show();
			$('#quick-press .submit input[type="submit"], #quick-press .submit input[type="reset"]').prop('disabled', true);

			$.post( t.attr( 'action' ), t.serializeArray(), function( data ) {
				// Replace the form, and prepend the published post.
				$('#dashboard_quick_draft .inside').html( data );
				$('#quick-press').removeClass('initial-form');
				quickPressLoad();
				highlightLatestPost();
				$('#title').focus();
			});
			
			function highlightLatestPost () {
				var latestPost = $('#draft-list li').first();
				latestPost.css('background', '#fffbe5');
				setTimeout(function () {
					latestPost.css('background', 'none');
				}, 1000);
			}
			
			return false;
		} );
	
		$('#publish').click( function() { act.val( 'post-quickpress-publish' ); } );

		$('#title, #tags-input, #content').each( function() {
			var input = $(this), prompt = $('#' + this.id + '-prompt-text');

			if ( '' === this.value )
				prompt.removeClass('screen-reader-text');

			prompt.click( function() {
				$(this).addClass('screen-reader-text');
				input.focus();
			});

			input.blur( function() {
				if ( '' === this.value )
					prompt.removeClass('screen-reader-text');
			});

			input.focus( function() {
				prompt.addClass('screen-reader-text');
			});
		});

		$('#quick-press').on( 'click focusin', function() {
			$(this).addClass("quickpress-open");
			$("#description-wrap, p.submit").slideDown(200);
			wpActiveEditor = 'content';
		});
	};
	quickPressLoad();
}(jQuery));
