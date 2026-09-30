<?php
/**
 * Performance: sacar lo que WordPress carga por defecto y un sitio de negocio no usa.
 *
 * Vivía en el tema; es "territorio de plugin" (cambia el comportamiento de WordPress, no el
 * diseño), así que está acá y funciona con cualquier tema. Cada parte se reactiva con el filtro
 * feelo_cleanup (el nombre viejo, feelolab_cleanup, sigue funcionando).
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

defined( 'ABSPATH' ) || exit;

final class Performance {

	public static function init(): void {
		add_action( 'init', array( self::class, 'cleanup' ) );
		add_action( 'wp_default_scripts', array( self::class, 'no_jquery_migrate' ) );
		if ( ! defined( 'WP_POST_REVISIONS' ) ) {
			add_filter( 'wp_revisions_to_keep', array( self::class, 'revisions' ) );
		}
	}

	/** @return array{emoji: bool, embeds: bool, head_junk: bool} */
	public static function options(): array {
		$defaults = array(
			'emoji'     => true,
			'embeds'    => true,
			'head_junk' => true,
		);
		$options  = apply_filters( 'feelolab_cleanup', $defaults );
		return array_merge( $defaults, (array) apply_filters( 'feelo_cleanup', $options ) );
	}

	public static function cleanup(): void {
		$cleanup = self::options();

		if ( $cleanup['emoji'] ) {
			// Script + estilos de emoji: ~15 KB en cada página para dibujar emojis que el sistema ya dibuja.
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'emoji_svg_url', '__return_false' );
		}

		if ( $cleanup['embeds'] ) {
			// oEmbed de terceros hacia este sitio: casi nunca se usa y suma wp-embed.js.
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}

		if ( $cleanup['head_junk'] ) {
			remove_action( 'wp_head', 'rsd_link' );
			remove_action( 'wp_head', 'wlwmanifest_link' );
			remove_action( 'wp_head', 'wp_generator' );
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
			remove_action( 'wp_head', 'rest_output_link_wp_head' );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}
	}

	/** jQuery Migrate no hace falta en el frontend: el tema no usa jQuery. */
	public static function no_jquery_migrate( \WP_Scripts $scripts ): void {
		if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
			return;
		}
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}

	/** Límite de revisiones: la tabla de contenidos crece rápido y pesa en backups y consultas. */
	public static function revisions( $num ): int {
		$num = (int) $num;
		return ( $num < 0 || $num > 10 ) ? 10 : $num;
	}
}
