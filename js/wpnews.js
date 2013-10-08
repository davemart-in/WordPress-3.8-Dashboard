// This can all be removed on merge with core
// simply add 'dashboard_rss' to the ajaxWidgets var
// in /wp-admin/js/dashboard.js

var ajaxWidgets, ajaxPopulateWidgets;

jQuery(document).ready(function ($) {
	ajaxWidgets = ['dashboard_rss'];
	
	ajaxPopulateWidgets = function(el) {
		function show(i, id) {
			var p, e = $('#' + id + ' div.inside:visible').find('.widget-loading');
			if ( e.length ) {
				p = e.parent();
				setTimeout( function(){
					p.load( ajaxurl + '?action=dashboard_news_widget&widget=' + id, '', function() {
						p.hide().slideDown('normal', function(){
							$(this).css('display', '');
						});
					});
				}, i * 500 );
			}
		}
	
		if ( el ) {
			el = el.toString();
			if ( $.inArray(el, ajaxWidgets) != -1 )
				show(0, el);
		} else {
			$.each( ajaxWidgets, show );
		}
	};
	ajaxPopulateWidgets();
});