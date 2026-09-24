/**
 * Meta box de galería: media modal, orden por arrastre y quitar.
 */
( function ( $, wp, i18n ) {
	'use strict';

	$( function () {
		var $box = $( '[data-feelo-gallery]' );
		if ( ! $box.length ) {
			return;
		}
		var $list = $box.find( '[data-feelo-gallery-list]' );
		var $input = $box.find( '[data-feelo-gallery-input]' );
		var template = wp.template( 'feelo-gallery-item' );
		var frame;

		function sync() {
			$input.val(
				$list
					.children()
					.map( function () {
						return $( this ).data( 'id' );
					} )
					.get()
					.join( ',' )
			);
		}

		$list.sortable( { items: '> li', placeholder: 'feelo-gallery__placeholder', update: sync } );

		$box.on( 'click', '[data-feelo-gallery-remove]', function () {
			var $item = $( this ).closest( 'li' );
			var $next = $item.next().find( 'button' ).add( $item.prev().find( 'button' ) ).first();
			$item.remove();
			sync();
			// El foco no se pierde: pasa a la imagen vecina o al botón de agregar.
			( $next.length ? $next : $box.find( '[data-feelo-gallery-add]' ) ).trigger( 'focus' );
		} );

		$box.on( 'click', '[data-feelo-gallery-add]', function () {
			if ( ! frame ) {
				frame = wp.media( {
					title: i18n.title,
					button: { text: i18n.button },
					library: { type: 'image' },
					multiple: 'add',
				} );
				frame.on( 'select', function () {
					var existing = $input.val() ? $input.val().split( ',' ) : [];
					frame
						.state()
						.get( 'selection' )
						.each( function ( attachment ) {
							var a = attachment.toJSON();
							if ( existing.indexOf( String( a.id ) ) !== -1 ) {
								return;
							}
							var url = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
							$list.append( template( { id: a.id, url: url, alt: a.alt } ) );
						} );
					sync();
				} );
			}
			frame.open();
		} );
	} );
} )( jQuery, window.wp, window.feeloGallery );
