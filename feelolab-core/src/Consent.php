<?php
/**
 * Banner de cookies con Google Consent Mode v2.
 *
 * Cómo funciona (modo avanzado de Google):
 * - Antes de cargar Analytics o Tag Manager se declara todo "denied". Google mide sin cookies
 *   (señales anónimas) y modela lo que falta; nada se guarda en el navegador sin permiso.
 * - Si la persona acepta, se actualiza a "granted" y se guarda la elección 180 días en la cookie
 *   feelo_consent (así el banner no vuelve a aparecer). Si rechaza, también se recuerda.
 * - El HTML de la página es el mismo para todos (compatible con caché): el banner se muestra
 *   o no con JS, según la cookie.
 * - Aceptar y Rechazar tienen el mismo peso visual (requisito del RGPD). No bloquea la página.
 * - "Preferencias de cookies" (feelo_consent_link()) lo vuelve a abrir.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

defined( 'ABSPATH' ) || exit;

final class Consent {

	public const COOKIE = 'feelo_consent';

	public static function init(): void {
		add_action( 'wp_footer', array( self::class, 'banner' ), 5 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'assets' ) );
	}

	/** JS del banner (y estilos mínimos si el tema activo no es FeeloLab). */
	public static function assets(): void {
		if ( ! self::enabled() ) {
			return;
		}
		wp_enqueue_script(
			'feelo-consent',
			FEELO_CORE_URL . 'assets/js/consent.js',
			array(),
			FEELO_CORE_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		if ( ! current_theme_supports( 'feelolab-core' ) ) {
			wp_register_style( 'feelo-consent', false, array(), FEELO_CORE_VERSION );
			wp_enqueue_style( 'feelo-consent' );
			wp_add_inline_style( 'feelo-consent', '.feelo-consent{position:fixed;left:1rem;right:1rem;bottom:1rem;z-index:100;max-width:34rem;padding:1rem 1.25rem;border-radius:12px;background:#111;color:#fff;box-shadow:0 10px 30px rgb(0 0 0/.25)}.feelo-consent[hidden]{display:none}.feelo-consent a{color:inherit}.feelo-consent__actions{display:flex;gap:.5rem;margin-top:.75rem}.feelo-consent button{min-height:44px;padding:.5rem 1rem;border:2px solid #fff;border-radius:8px;background:#fff;color:#111;font:inherit;font-weight:700;cursor:pointer}.feelo-consent button+button{background:transparent;color:#fff}' );
		}
	}

	public static function enabled(): bool {
		return (bool) apply_filters( 'feelo_consent_enabled', (bool) feelo_setting( 'consent_banner' ) );
	}

	/**
	 * Consent Mode por defecto. Va en el <head> ANTES de las etiquetas de Google (lo imprime Frontend::head).
	 * Si ya hay una elección guardada, se aplica en el mismo instante: sin parpadeo de datos.
	 */
	public static function default_script(): void {
		if ( ! self::enabled() ) {
			return;
		}
		$cookie = wp_json_encode( self::COOKIE );
		wp_print_inline_script_tag(
			"window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',functionality_storage:'granted',security_storage:'granted',wait_for_update:500});gtag('set','ads_data_redaction',true);(function(){var m=document.cookie.match(new RegExp('(?:^|; )'+" . $cookie . "+'=(granted|denied)'));if(m&&m[1]==='granted'){gtag('consent','update',{ad_storage:'granted',ad_user_data:'granted',ad_personalization:'granted',analytics_storage:'granted'});}})();"
		);
	}

	public static function banner(): void {
		if ( ! self::enabled() ) {
			return;
		}
		$text    = (string) feelo_setting( 'consent_text', __( 'Usamos cookies para entender cómo se usa el sitio y mejorarlo. Podés aceptarlas o rechazarlas: el sitio funciona igual.', 'feelolab-core' ) );
		$privacy = get_privacy_policy_url();

		?>
		<section class="feelo-consent" id="feelo-consent" data-cookie="<?php echo esc_attr( self::COOKIE ); ?>" aria-label="<?php esc_attr_e( 'Aviso de cookies', 'feelolab-core' ); ?>" hidden>
			<p class="feelo-consent__text">
				<?php echo esc_html( $text ); ?>
				<?php if ( $privacy ) : ?>
					<a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'Política de privacidad', 'feelolab-core' ); ?></a>
				<?php endif; ?>
			</p>
			<div class="feelo-consent__actions">
				<button type="button" class="feelo-consent__btn" data-feelo-consent="granted"><?php esc_html_e( 'Aceptar', 'feelolab-core' ); ?></button>
				<button type="button" class="feelo-consent__btn" data-feelo-consent="denied"><?php esc_html_e( 'Rechazar', 'feelolab-core' ); ?></button>
			</div>
		</section>
		<?php
	}

	/** Link "Preferencias de cookies" para el pie (vuelve a abrir el banner). Vacío si el banner está apagado. */
	public static function link(): string {
		if ( ! self::enabled() ) {
			return '';
		}
		return '<button type="button" class="feelo-consent-link" data-feelo-consent-open>' . esc_html__( 'Preferencias de cookies', 'feelolab-core' ) . '</button>';
	}
}
