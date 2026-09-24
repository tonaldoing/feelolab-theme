/**
 * Avisos de contraste en vivo en el Personalizador (misma fórmula WCAG que inc/colors.php).
 *
 * - Botón vs. fondo de página < 3:1 → aviso informativo: el sitio le agrega un borde.
 * - Texto del botón elegido vs. fondo del botón < 4.5:1 → el sitio usa blanco o negro.
 * - Links (o el color del botón, si no se eligió uno para links) vs. fondo < 4.5:1 → versión más oscura.
 * - Texto vs. fondo < 7:1 → se oscurece.
 */
( function ( api, i18n ) {
	'use strict';

	var ID = 'feelolab_contrast';

	function rgb( hex ) {
		hex = ( hex || '' ).replace( '#', '' );
		if ( hex.length === 3 ) {
			hex = hex.replace( /(.)/g, '$1$1' );
		}
		return [ 0, 2, 4 ].map( function ( i ) {
			return parseInt( hex.substr( i, 2 ), 16 ) / 255;
		} );
	}

	function luminance( hex ) {
		var c = rgb( hex ).map( function ( v ) {
			return v <= 0.03928 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 );
		} );
		return 0.2126 * c[ 0 ] + 0.7152 * c[ 1 ] + 0.0722 * c[ 2 ];
	}

	function contrast( a, b ) {
		var la = luminance( a ),
			lb = luminance( b );
		return ( Math.max( la, lb ) + 0.05 ) / ( Math.min( la, lb ) + 0.05 );
	}

	function val( id ) {
		var s = api( 'feelolab_color_' + id );
		return s ? s.get() : '';
	}

	function notify( id, show, type, message, ratio ) {
		var setting = api( 'feelolab_color_' + id );
		if ( ! setting ) {
			return;
		}
		setting.notifications.remove( ID );
		if ( show ) {
			setting.notifications.add(
				new api.Notification( ID, { type: type, message: message.replace( '%s', ratio.toFixed( 1 ) ) } )
			);
		}
	}

	function run() {
		var primary = val( 'primary' ),
			bg = val( 'bg' ),
			btnText = val( 'button_text' ),
			link = val( 'link' ),
			text = val( 'text' );
		if ( ! primary || ! bg ) {
			return;
		}

		var btnVsBg = contrast( primary, bg );
		var linkBase = link || primary;
		var linkVsBg = contrast( linkBase, bg );

		// El aviso de links va en el control que lo causa: "Links" si se eligió uno, si no el del botón.
		var primaryMsgs = [];
		if ( btnVsBg < 3 ) {
			primaryMsgs.push( [ 'info', i18n.btnBorder, btnVsBg ] );
		}
		if ( ! link && btnVsBg < 3 ) {
			// Muy claro para links: el sitio usa el secundario o el texto (misma regla que inc/colors.php).
			primaryMsgs.push( [ 'info', i18n.linkFallback, btnVsBg ] );
		} else if ( ! link && linkVsBg < 4.5 ) {
			primaryMsgs.push( [ 'warning', i18n.lowLinkAuto, linkVsBg ] );
		}
		var setting = api( 'feelolab_color_primary' );
		setting.notifications.remove( ID );
		setting.notifications.remove( ID + '_2' );
		primaryMsgs.forEach( function ( m, i ) {
			setting.notifications.add(
				new api.Notification( i ? ID + '_2' : ID, { type: m[ 0 ], message: m[ 1 ].replace( '%s', m[ 2 ].toFixed( 1 ) ) } )
			);
		} );

		notify( 'button_text', !! btnText && contrast( btnText, primary ) < 4.5, 'warning', i18n.lowBtnText, btnText ? contrast( btnText, primary ) : 0 );
		notify( 'link', !! link && linkVsBg < 4.5, 'warning', i18n.lowLink, linkVsBg );
		notify( 'text', !! text && contrast( text, bg ) < 7, 'warning', i18n.lowText, text ? contrast( text, bg ) : 0 );
	}

	api.bind( 'ready', function () {
		[ 'primary', 'button_text', 'link', 'bg', 'text' ].forEach( function ( id ) {
			api( 'feelolab_color_' + id, function ( setting ) {
				setting.bind( run );
			} );
		} );
		run();
	} );
} )( wp.customize, window.feelolabContrast );
