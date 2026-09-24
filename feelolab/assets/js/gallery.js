/**
 * Visor de galería: la miniatura cambia la imagen principal sin navegar.
 * Sin este script, cada miniatura abre la imagen grande (mejora progresiva).
 */
( function () {
	'use strict';

	document.querySelectorAll( '[data-gallery]' ).forEach( function ( gallery ) {
		var main = gallery.querySelector( '[data-gallery-main]' );
		var status = gallery.querySelector( '[data-gallery-status]' );
		if ( ! main ) {
			return;
		}
		gallery.addEventListener( 'click', function ( e ) {
			var thumb = e.target.closest( '.gallery__thumb' );
			if ( ! thumb || e.metaKey || e.ctrlKey || e.shiftKey ) {
				return; // Cmd/Ctrl+clic sigue abriendo la imagen en otra pestaña.
			}
			e.preventDefault();
			main.src = thumb.dataset.src;
			main.srcset = thumb.dataset.srcset || '';
			main.width = thumb.dataset.width;
			main.height = thumb.dataset.height;
			main.alt = thumb.dataset.alt || '';
			gallery.querySelectorAll( '.gallery__thumb[aria-current]' ).forEach( function ( t ) {
				t.removeAttribute( 'aria-current' );
			} );
			thumb.setAttribute( 'aria-current', 'true' );
			if ( status ) {
				status.textContent = thumb.textContent.trim();
			}
		} );
	} );
} )();
