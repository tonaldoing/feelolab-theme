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

/**
 * Buscador del encabezado (disclosure): el botón abre el campo y le da foco; Escape o clic afuera
 * lo cierran y devuelven el foco al botón. Sin JS, el campo queda visible.
 */
( function () {
	'use strict';

	var toggle = document.querySelector( '.site-search__toggle' );
	var panel = document.getElementById( 'site-search-panel' );
	if ( ! toggle || ! panel ) {
		return;
	}
	function set( open, restoreFocus ) {
		toggle.setAttribute( 'aria-expanded', String( open ) );
		panel.classList.toggle( 'is-open', open );
		if ( open ) {
			panel.querySelector( 'input' ).focus();
		} else if ( restoreFocus ) {
			toggle.focus();
		}
	}
	toggle.addEventListener( 'click', function () {
		set( toggle.getAttribute( 'aria-expanded' ) !== 'true', true );
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
			set( false, true );
		}
	} );
	document.addEventListener( 'click', function ( e ) {
		if ( toggle.getAttribute( 'aria-expanded' ) === 'true' && ! e.target.closest( '.site-search' ) ) {
			set( false, false );
		}
	} );
} )();

/**
 * Encabezado transparente sobre la portada: toma su color al hacer scroll o al abrir el menú.
 * --header-h es su alto real, para que la portada deje ese espacio arriba del título.
 */
( function () {
	'use strict';

	var header = document.querySelector( '.site-header.is-transparent' );
	if ( ! header ) {
		return;
	}
	var root = document.documentElement;
	function measure() {
		root.style.setProperty( '--header-h', header.offsetHeight + 'px' );
	}
	function update() {
		var open = !! header.querySelector( '[aria-expanded="true"]' );
		header.classList.toggle( 'is-solid', open || window.scrollY > 8 );
	}
	measure();
	update();
	window.addEventListener( 'resize', measure, { passive: true } );
	window.addEventListener( 'scroll', update, { passive: true } );
	header.addEventListener( 'click', function () {
		window.setTimeout( update, 0 );
	} );
	document.addEventListener( 'keydown', function () {
		window.setTimeout( update, 0 );
	} );
} )();

/**
 * Compartir: "Compartir" usa el menú nativo del teléfono y "Copiar link" el portapapeles. Cada
 * botón aparece solo si el navegador lo soporta (lo decide un script en línea en share.php, antes
 * de pintar); los links a cada red funcionan siempre.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.share' ).forEach( function ( box ) {
		var status = box.querySelector( '.share__status' );
		box.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( 'button[data-feelo-share]' );
			if ( ! btn ) {
				return;
			}
			var url = btn.getAttribute( 'data-share-url' );
			if ( btn.getAttribute( 'data-feelo-share' ) === 'nativo' ) {
				// Si la persona cierra el menú nativo la promesa se rechaza: no es un error.
				navigator.share( { title: btn.getAttribute( 'data-share-title' ), url: url } ).catch( function () {} );
				return;
			}
			navigator.clipboard.writeText( url ).then( function () {
				status.textContent = btn.getAttribute( 'data-copied' );
				btn.classList.add( 'is-done' );
				window.setTimeout( function () {
					status.textContent = '';
					btn.classList.remove( 'is-done' );
				}, 2500 );
			} );
		} );
	} );
} )();
