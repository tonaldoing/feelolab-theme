/**
 * Vista previa del Personalizador: cuando cambia un color, la tipografía o la forma, el servidor
 * recalcula el CSS de marca (con el ajuste de contraste) y lo manda en #feelolab-brand-partial.
 * Acá se copia al <style> del tema, que nunca se reemplaza (ver feelolab_brand_css_partial()).
 */
( function ( api ) {
	'use strict';

	function apply() {
		var source = document.getElementById( 'feelolab-brand-partial' );
		var style = document.getElementById( 'feelolab-inline-css' );
		if ( source && style ) {
			style.textContent = source.textContent;
		}
	}

	api.bind( 'preview-ready', function () {
		api.selectiveRefresh.bind( 'partial-content-rendered', function ( placement ) {
			if ( placement.partial.id === 'feelolab_brand_css' ) {
				apply();
			}
		} );
	} );
} )( wp.customize );
