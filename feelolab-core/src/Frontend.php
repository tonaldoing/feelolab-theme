<?php
/**
 * Salidas del plugin en el frontend: analítica, verificación, botón de WhatsApp.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

defined( 'ABSPATH' ) || exit;

final class Frontend {

	public static function init(): void {
		add_action( 'wp_head', array( self::class, 'head' ), 1 );
		add_action( 'wp_body_open', array( self::class, 'gtm_noscript' ), 1 );
		add_action( 'wp_footer', array( self::class, 'whatsapp' ), 20 );
		add_action( 'wp_footer', array( self::class, 'form_focus' ), 30 );
	}

	/** ¿Medimos esta visita? No a quien edita el sitio: ensucia los datos. */
	private static function should_track(): bool {
		return ! is_user_logged_in() || ! current_user_can( 'edit_posts' );
	}

	public static function head(): void {
		$gsc = (string) feelo_setting( 'gsc_verificacion' );
		if ( $gsc && ! feelo_seo_plugin_active() ) {
			printf( '<meta name="google-site-verification" content="%s">' . "\n", esc_attr( $gsc ) );
		}
		if ( ! self::should_track() ) {
			return;
		}

		$gtm = strtoupper( (string) feelo_setting( 'gtm_id' ) );
		$ga4 = strtoupper( (string) feelo_setting( 'ga4_id' ) );

		if ( preg_match( '/^GTM-[A-Z0-9]+$/', $gtm ) ) {
			// Snippet oficial de GTM; carga async y no bloquea el render.
			?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( $gtm ); ?>');</script>
			<?php
		} elseif ( preg_match( '/^G-[A-Z0-9]+$/', $ga4 ) ) {
			?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4 ); ?>"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- snippet oficial, debe ir primero en el head. ?>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo esc_js( $ga4 ); ?>');</script>
			<?php
		}
	}

	public static function gtm_noscript(): void {
		$gtm = strtoupper( (string) feelo_setting( 'gtm_id' ) );
		if ( self::should_track() && preg_match( '/^GTM-[A-Z0-9]+$/', $gtm ) ) {
			printf( '<noscript><iframe src="%s" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>', esc_url( 'https://www.googletagmanager.com/ns.html?id=' . $gtm ) );
		}
	}

	public static function whatsapp(): void {
		if ( ! feelo_setting( 'whatsapp_flotante' ) ) {
			return;
		}
		$url = feelo_whatsapp_url();
		if ( ! $url ) {
			return;
		}
		/**
		 * El tema lo estiliza (.feelo-wa). Si el tema no lo soporta, se agregan estilos mínimos inline.
		 */
		if ( ! current_theme_supports( 'feelolab-core' ) ) {
			echo '<style>.feelo-wa{position:fixed;right:1rem;bottom:1rem;z-index:50;display:grid;place-items:center;width:3.5rem;height:3.5rem;border-radius:50%;background:#1f7a4d;color:#fff}.feelo-wa svg{width:1.75rem;height:1.75rem}.feelo-wa:focus-visible{outline:3px solid #111;outline-offset:3px}</style>';
		}
		printf(
			'<aside class="feelo-wa-wrap" aria-label="WhatsApp"><a class="feelo-wa" href="%1$s" target="_blank" rel="noopener"><span class="screen-reader-text">%2$s</span>%3$s</a></aside>',
			esc_url( $url ),
			esc_html__( 'Escribinos por WhatsApp (se abre en otra pestaña)', 'feelolab-core' ),
			'<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.1-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1-.3-.1-1.2-.5-2.3-1.4-.9-.8-1.4-1.7-1.6-2-.2-.3 0-.5.1-.6l.4-.5c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.7-.7 2-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3zM12 21.8c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4C2.7 15.6 2.2 13.8 2.2 12 2.2 6.6 6.6 2.2 12 2.2c2.6 0 5.1 1 6.9 2.9 1.8 1.8 2.9 4.3 2.9 6.9 0 5.4-4.4 9.8-9.8 9.8zM20.5 3.5C18.2 1.2 15.2 0 12 0 5.4 0 .1 5.3.1 11.9c0 2.1.6 4.2 1.6 6L0 24l6.3-1.7c1.8 1 3.8 1.5 5.7 1.5 6.6 0 11.9-5.3 11.9-11.9 0-3.2-1.2-6.2-3.4-8.4z"/></svg>'
		);
	}

	/** Tras enviar el formulario, el foco va al aviso: así un lector de pantalla lo anuncia. */
	public static function form_focus(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo decide si imprimir un script.
		if ( ! isset( $_GET['feelo_form'] ) ) {
			return;
		}
		// En "load" y después del salto al ancla: si no, el navegador mueve el foco al ancla y lo pisa.
		echo '<script>addEventListener("load",function(){setTimeout(function(){var n=document.querySelector("[data-feelo-focus]");if(n){n.focus();}},0);});</script>';
	}
}
