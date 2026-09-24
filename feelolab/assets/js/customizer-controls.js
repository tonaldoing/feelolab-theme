/**
 * Aviso de contraste en vivo en el Personalizador (misma fórmula WCAG que inc/colors.php).
 */
( function ( api, i18n ) {
	'use strict';

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

	function check( settingId, against, min, message ) {
		var setting = api( settingId ),
			other = api( against );
		if ( ! setting || ! other ) {
			return;
		}
		var ratio = contrast( setting.get(), other.get() );
		if ( ratio < min ) {
			setting.notifications.add(
				new api.Notification( 'feelolab_contrast', {
					type: 'warning',
					message: message.replace( '%s', ratio.toFixed( 1 ) ),
				} )
			);
		} else {
			setting.notifications.remove( 'feelolab_contrast' );
		}
	}

	function run() {
		check( 'feelolab_color_primary', 'feelolab_color_bg', 4.5, i18n.lowPrimary );
		check( 'feelolab_color_text', 'feelolab_color_bg', 7, i18n.lowText );
	}

	api.bind( 'ready', function () {
		[ 'feelolab_color_primary', 'feelolab_color_text', 'feelolab_color_bg' ].forEach( function ( id ) {
			api( id, function ( setting ) {
				setting.bind( run );
			} );
		} );
		run();
	} );
} )( wp.customize, window.feelolabContrast );
