<?php
/**
 * Eventos de conversión para Google Analytics 4 / Tag Manager.
 *
 * Qué se mide (sin configurar nada, solo con GA4 o GTM cargados en Ajustes del sitio):
 * - click_whatsapp: cualquier link a wa.me (botón flotante, encabezado, productos, contacto…).
 * - click_phone: links tel:.
 * - click_email: links mailto:.
 * - generate_lead: envío correcto del formulario de contacto (evento recomendado de GA4).
 * - share: botones de compartir de las notas (evento recomendado de GA4, con method = la red).
 *   Una sola vez por envío: recargar la página de "gracias" no lo repite.
 * Cada evento lleva feelo_location: dónde estaba el link (flotante, encabezado, pie, o el id
 * de la sección: servicios, contacto…), para saber qué parte del sitio convierte.
 *
 * Con GA4 directo se usa gtag('event'); con GTM se empuja al dataLayer (crear un disparador de
 * "Evento personalizado" con esos nombres). Respeta Consent Mode: sin consentimiento, Google
 * recibe el evento sin cookies.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

defined( 'ABSPATH' ) || exit;

final class Tracking {

	public static function init(): void {
		add_action( 'wp_footer', array( self::class, 'script' ), 40 );
	}

	public static function script(): void {
		if ( ! Frontend::tracking_active() ) {
			return;
		}
		?>
<script>
(function () {
	function send(name, params) {
		if (typeof window.gtag === 'function') {
			window.gtag('event', name, params);
		} else {
			(window.dataLayer = window.dataLayer || []).push(Object.assign({ event: name }, params));
		}
	}
	function where(el) {
		if (el.closest('.feelo-wa-wrap')) { return 'flotante'; }
		if (el.closest('.topbar')) { return 'barra-superior'; }
		if (el.closest('.site-header')) { return 'encabezado'; }
		if (el.closest('.site-footer')) { return 'pie'; }
		var s = el.closest('section[id], [id].section, main [id]');
		return s ? s.id : 'contenido';
	}
	document.addEventListener('click', function (e) {
		var share = e.target.closest('[data-feelo-share]');
		if (share) {
			send('share', { method: share.getAttribute('data-feelo-share'), content_type: 'article', item_id: location.pathname });
			return;
		}
		var a = e.target.closest('a[href]');
		if (!a) { return; }
		var href = a.getAttribute('href') || '';
		var name = /^https:\/\/(wa\.me|api\.whatsapp\.com)\//.test(href) ? 'click_whatsapp' : (/^tel:/.test(href) ? 'click_phone' : (/^mailto:/.test(href) ? 'click_email' : ''));
		if (!name) { return; }
		send(name, { feelo_location: where(a), link_text: (a.textContent || '').trim().slice(0, 80), page_path: location.pathname });
	}, { capture: true });
	// Formulario enviado: ?feelo_form=ok. Una vez por envío (el parámetro queda en la URL al recargar).
	if (/[?&]feelo_form=ok\b/.test(location.search)) {
		var key = 'feelo_lead_' + location.pathname + location.search;
		try {
			if (!sessionStorage.getItem(key)) {
				sessionStorage.setItem(key, '1');
				send('generate_lead', { feelo_location: 'formulario', page_path: location.pathname });
			}
		} catch (err) {
			send('generate_lead', { feelo_location: 'formulario', page_path: location.pathname });
		}
	}
})();
</script>
		<?php
	}
}
