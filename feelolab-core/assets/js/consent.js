/**
 * Banner de cookies (Consent Mode v2). El HTML lo imprime Consent::banner(); el "todo denegado"
 * por defecto va en el <head>, antes de las etiquetas de Google (Consent::default_script()).
 */
( function () {
	'use strict';

	var box = document.getElementById( 'feelo-consent' );
	if ( ! box ) {
		return;
	}
	var name = box.getAttribute( 'data-cookie' ) || 'feelo_consent';
	var lastOpener = null;

	function saved() {
		var m = document.cookie.match( new RegExp( '(?:^|; )' + name + '=(granted|denied)' ) );
		return m ? m[ 1 ] : '';
	}
	function show() {
		var h = document.documentElement;
		box.hidden = false;
		h.classList.add( 'feelo-consent-open' );
		h.style.setProperty( '--feelo-consent-h', box.offsetHeight + 'px' );
	}
	function hide() {
		box.hidden = true;
		document.documentElement.classList.remove( 'feelo-consent-open' );
	}
	function choose( value, opener ) {
		document.cookie = name + '=' + value + '; max-age=15552000; path=/; SameSite=Lax' + ( location.protocol === 'https:' ? '; Secure' : '' );
		if ( typeof window.gtag === 'function' ) {
			var s = value === 'granted' ? 'granted' : 'denied';
			window.gtag( 'consent', 'update', { ad_storage: s, ad_user_data: s, ad_personalization: s, analytics_storage: s } );
		}
		hide();
		if ( opener ) {
			opener.focus();
		}
	}

	box.addEventListener( 'click', function ( e ) {
		var b = e.target.closest( '[data-feelo-consent]' );
		if ( b ) {
			choose( b.getAttribute( 'data-feelo-consent' ), lastOpener );
			lastOpener = null;
		}
	} );
	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( '[data-feelo-consent-open]' );
		if ( ! link ) {
			return;
		}
		e.preventDefault();
		lastOpener = link;
		show();
		box.querySelector( 'button' ).focus();
	} );
	if ( ! saved() ) {
		show();
	}
} )();
