/**
 * Feelolab — menú accesible (patrón disclosure). ~1 KB, sin dependencias.
 *
 * - Botón de menú mobile con aria-expanded.
 * - Submenús con su propio botón: clic, Enter o Espacio (nativo del <button>).
 * - Escape cierra y devuelve el foco al botón que abrió.
 * - Clic afuera o foco que sale del menú cierra los submenús.
 */
( function () {
	'use strict';

	var nav = document.getElementById( 'site-nav' );
	if ( ! nav ) {
		return;
	}
	var toggle = nav.querySelector( '.nav-toggle' );
	var panel = document.getElementById( 'site-nav-menu' );

	function setMenu( open ) {
		if ( ! toggle || ! panel ) {
			return;
		}
		toggle.setAttribute( 'aria-expanded', String( open ) );
		panel.classList.toggle( 'is-open', open );
	}

	function setSubmenu( button, open ) {
		var submenu = button.parentElement.querySelector( '.sub-menu' );
		button.setAttribute( 'aria-expanded', String( open ) );
		if ( submenu ) {
			submenu.classList.toggle( 'is-open', open );
		}
	}

	function closeSubmenus( except ) {
		nav.querySelectorAll( '.submenu-toggle[aria-expanded="true"]' ).forEach( function ( b ) {
			if ( b !== except ) {
				setSubmenu( b, false );
			}
		} );
	}

	if ( toggle ) {
		toggle.addEventListener( 'click', function () {
			setMenu( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );
	}

	nav.addEventListener( 'click', function ( e ) {
		var button = e.target.closest( '.submenu-toggle' );
		if ( ! button ) {
			return;
		}
		var open = button.getAttribute( 'aria-expanded' ) !== 'true';
		closeSubmenus( button );
		setSubmenu( button, open );
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'Escape' ) {
			return;
		}
		var openSub = nav.querySelector( '.submenu-toggle[aria-expanded="true"]' );
		if ( openSub ) {
			setSubmenu( openSub, false );
			openSub.focus();
		} else if ( toggle && toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
			setMenu( false );
			toggle.focus();
		}
	} );

	document.addEventListener( 'click', function ( e ) {
		if ( ! nav.contains( e.target ) ) {
			closeSubmenus();
			setMenu( false );
		}
	} );

	nav.addEventListener( 'focusout', function ( e ) {
		if ( e.relatedTarget && ! nav.contains( e.relatedTarget ) ) {
			closeSubmenus();
		}
	} );

	// Al pasar a desktop, el panel mobile no queda abierto por detrás.
	var desktop = window.matchMedia( '(min-width: 960px)' );
	desktop.addEventListener( 'change', function () {
		setMenu( false );
		closeSubmenus();
	} );
} )();
