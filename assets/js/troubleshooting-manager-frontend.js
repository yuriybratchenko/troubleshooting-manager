( function( $ ) {

	'use strict';

	var TroubleshootingManager = {

		init: function() {

			TroubleshootingManager.stickySidebarInit();
			TroubleshootingManager.embedVideoInit();
		},

		stickySidebarInit: function() {

			if ( $( '.troubleshooting-manager__single-article-sidebar' )[0] ) {
				var stickySidebar = new StickySidebar( '.troubleshooting-manager__single-article-sidebar', { topSpacing: 20 } );
			}
		},

		embedVideoInit: function() {

			var $mediaFrame = $( '.troubleshooting-manager__single-media-frame' );

			if ( ! $mediaFrame[0] ) {
				return false;
			}

			var $videoIframe = $( '.troubleshooting-manager-video-iframe', $mediaFrame ),
				$overlay     = $( '.video-embed-image-overlay', $mediaFrame );

			$overlay.on( 'click.TroubleshootingManager', function( event ) {
				var newSourceUrl = $videoIframe[0].src.replace('&autoplay=0', '');

				$videoIframe[0].src = newSourceUrl + '&autoplay=1';

				$overlay.remove();

			} );

		}

	};

	$( document ).on( 'ready.TroubleshootingManager', TroubleshootingManager.init );


}( jQuery ) );
