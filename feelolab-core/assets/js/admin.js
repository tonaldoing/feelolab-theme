/**
 * Pantallas de FeeloLab en el panel: confirmación de formularios (volver a una versión) y la
 * importación de productos por tandas (Importer.php).
 */
( function () {
	'use strict';

	var i18n = window.feeloAdmin || {};

	// Formularios con data-feelo-confirm: piden confirmación antes de enviarse.
	document.addEventListener( 'submit', function ( e ) {
		var form = e.target.closest( 'form[data-feelo-confirm]' );
		if ( form && ! window.confirm( form.getAttribute( 'data-feelo-confirm' ) ) ) {
			e.preventDefault();
		}
	} );

	// Importación por tandas: el navegador pide cada tanda hasta terminar.
	var box = document.querySelector( '[data-feelo-import]' );
	if ( ! box ) {
		return;
	}
	var start = box.querySelector( '.feelo-import-start' );
	var bar = box.querySelector( '.feelo-progress' );
	var status = box.querySelector( '.feelo-import-status' );

	function paint( done, total ) {
		var pct = total ? Math.round( done * 100 / total ) : 100;
		bar.hidden = false;
		bar.setAttribute( 'aria-valuenow', pct );
		bar.firstElementChild.style.width = pct + '%';
		status.textContent = ( i18n.progress || '%1$d / %2$d' ).replace( '%1$d', done ).replace( '%2$d', total );
	}
	function step() {
		var body = new FormData();
		body.append( 'action', 'feelo_import_batch' );
		body.append( '_ajax_nonce', box.getAttribute( 'data-nonce' ) );
		fetch( window.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' } )
			.then( function ( r ) {
				if ( ! r.ok ) {
					throw new Error( 'HTTP ' + r.status );
				}
				return r.json();
			} )
			.then( function ( res ) {
				if ( ! res.success ) {
					throw new Error( res.data || 'error' );
				}
				paint( res.data.done, res.data.total );
				if ( res.data.finished ) {
					window.location.reload();
				} else {
					step();
				}
			} )
			.catch( function ( err ) {
				window.console.error( 'feelolab import:', err.message );
				status.textContent = i18n.error || '';
				start.disabled = false;
				start.textContent = i18n.resume || start.textContent;
			} );
	}
	start.addEventListener( 'click', function () {
		start.disabled = true;
		paint( 0, parseInt( box.getAttribute( 'data-total' ), 10 ) );
		step();
	} );
} )();
