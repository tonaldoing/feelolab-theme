<?php
/**
 * Eventos de conversión para Google Analytics 4 / Tag Manager.
 *
 * Qué se mide (sin configurar nada, solo con GA4 o GTM cargados en Ajustes del sitio):
 * - click_whatsapp: cualquier link a wa.me (botón flotante, encabezado, productos, contacto…).
 * - click_phone: links tel:.
 * - click_email: links mailto:.
 * - generate_lead: envío correcto del formulario de contacto (evento recomendado de GA4).
 * - sign_up: suscripción al newsletter (evento recomendado de GA4, method = newsletter). Una vez por envío.
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
		add_action( 'wp_enqueue_scripts', array( self::class, 'assets' ) );
	}

	/** El JS (assets/js/tracking.js) solo se carga si hay GA4 o GTM y esta visita se mide. */
	public static function assets(): void {
		if ( ! Frontend::tracking_active() ) {
			return;
		}
		wp_enqueue_script(
			'feelo-tracking',
			FEELO_CORE_URL . 'assets/js/tracking.js',
			array(),
			FEELO_CORE_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
