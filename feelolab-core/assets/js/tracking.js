/**
 * Eventos de conversión para GA4 / Tag Manager (ver Tracking.php): click_whatsapp, click_phone,
 * click_email, share, generate_lead y sign_up, con feelo_location. Con GA4 directo usa gtag;
 * con GTM empuja al dataLayer.
 */
( function () {
	'use strict';

	function send( name, params ) {
		if ( typeof window.gtag === 'function' ) {
			window.gtag( 'event', name, params );
		} else {
			( window.dataLayer = window.dataLayer || [] ).push( Object.assign( { event: name }, params ) );
		}
	}
	function where( el ) {
		if ( el.closest( '.feelo-wa-wrap' ) ) {
			return 'flotante';
		}
		if ( el.closest( '.topbar' ) ) {
			return 'barra-superior';
		}
		if ( el.closest( '.site-header' ) ) {
			return 'encabezado';
		}
		if ( el.closest( '.site-footer' ) ) {
			return 'pie';
		}
		var s = el.closest( 'section[id], [id].section, main [id]' );
		return s ? s.id : 'contenido';
	}
	// Una vez por envío: el parámetro queda en la URL al recargar.
	function once( key, name, params ) {
		try {
			if ( sessionStorage.getItem( key ) ) {
				return;
			}
			sessionStorage.setItem( key, '1' );
		} catch ( err ) {
			// Sin sessionStorage (modo privado estricto): se manda igual.
		}
		send( name, params );
	}

	document.addEventListener( 'click', function ( e ) {
		var share = e.target.closest( '[data-feelo-share]' );
		if ( share ) {
			send( 'share', { method: share.getAttribute( 'data-feelo-share' ), content_type: 'article', item_id: location.pathname } );
			return;
		}
		var a = e.target.closest( 'a[href]' );
		if ( ! a ) {
			return;
		}
		var href = a.getAttribute( 'href' ) || '';
		var name = /^https:\/\/(wa\.me|api\.whatsapp\.com)\//.test( href ) ? 'click_whatsapp' : ( /^tel:/.test( href ) ? 'click_phone' : ( /^mailto:/.test( href ) ? 'click_email' : '' ) );
		if ( name ) {
			send( name, { feelo_location: where( a ), link_text: ( a.textContent || '' ).trim().slice( 0, 80 ), page_path: location.pathname } );
		}
	}, { capture: true } );

	if ( /[?&]feelo_form=ok\b/.test( location.search ) ) {
		once( 'feelo_lead_' + location.pathname + location.search, 'generate_lead', { feelo_location: 'formulario', page_path: location.pathname } );
	}
	if ( /[?&]feelo_news=ok\b/.test( location.search ) ) {
		once( 'feelo_signup_' + location.pathname + location.search, 'sign_up', { method: 'newsletter', page_path: location.pathname } );
	}
} )();
