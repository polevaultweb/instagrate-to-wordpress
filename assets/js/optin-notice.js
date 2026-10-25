/**
 * Intagrate Lite: the coupon notice shown after the first post from Instagram.
 *
 * Closing the notice, clicking "No thanks" or submitting the form dismisses it
 * for the current user. The form itself posts to the mailing list in a new tab.
 */
( function( $ ) {
	$( function() {
		var $notice = $( '.itw-optin-notice' );
		if ( ! $notice.length ) {
			return;
		}

		function dismiss() {
			$.post( window.ajaxurl, {
				action: 'itw_dismiss_optin',
				_wpnonce: $notice.data( 'nonce' )
			} );
		}

		// WordPress adds the close button after the page loads
		$notice.on( 'click', '.notice-dismiss', dismiss );

		$notice.on( 'click', '.itw-optin-dismiss', function( event ) {
			event.preventDefault();
			dismiss();
			$notice.slideUp( 100, function() {
				$notice.remove();
			} );
		} );

		// Only fires once the browser has validated the email address
		$notice.on( 'submit', '.itw-optin-form', function() {
			dismiss();
			var $form = $( this );
			// Hide the form after it has been sent, not before
			setTimeout( function() {
				$form.hide();
				$notice.find( '.itw-optin-thanks' ).prop( 'hidden', false );
			}, 0 );
		} );
	} );
} )( jQuery );
