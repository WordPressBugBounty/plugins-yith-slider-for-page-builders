( function( $ ) {
	$( function() {
		$( '.yith-slider[data-slick]' ).each( function() {
			var $slider = $( this );
			if ( ! $slider.hasClass( 'slick-initialized' ) ) {
				$slider.slick();
			}
		} );
	} );
}( jQuery ) );
