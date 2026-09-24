<?php
/**
 * Performance: sacar lo que WordPress carga por defecto y no usamos.
 *
 * Cada línea ahorra un request o bytes en todas las páginas. Si un cliente necesita algo de esto
 * (por ejemplo emojis en imágenes), se reactiva con el filtro feelolab_cleanup.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		$cleanup = apply_filters(
			'feelolab_cleanup',
			array(
				'emoji'     => true,
				'embeds'    => true,
				'head_junk' => true,
			)
		);

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
);

/**
 * CSS de bloques solo de los bloques que aparecen en la página, no el block-library entero (~100 KB).
 */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		// Dashicons y estilos de clásicos solo para quien está logueado (barra de admin).
		if ( ! is_user_logged_in() ) {
			wp_dequeue_style( 'dashicons' );
		}
		// Estilos de botón/archivo de bloques para temas clásicos: main.css ya los cubre.
		// global-styles NO se saca: trae las clases de color de la paleta que usa el contenido.
		wp_dequeue_style( 'classic-theme-styles' );
	},
	100
);

/** jQuery Migrate no hace falta: el tema no usa jQuery. */
add_action(
	'wp_default_scripts',
	static function ( WP_Scripts $scripts ): void {
		if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
			return;
		}
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}
);

/** Límite de revisiones: la tabla wp_posts crece rápido y pesa en backups y consultas. */
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	add_filter( 'wp_revisions_to_keep', static fn( $num ) => ( $num < 0 || $num > 10 ) ? 10 : $num );
}
