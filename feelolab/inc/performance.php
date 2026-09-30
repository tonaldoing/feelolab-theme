<?php
/**
 * Performance del tema: CSS de bloques solo de los bloques en uso y sin estilos que main.css ya cubre.
 *
 * La limpieza de WordPress (emojis, embeds, enlaces del <head>, jQuery Migrate, límite de
 * revisiones) está en el plugin FeeloLab Core (src/Performance.php): es territorio de plugin.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

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
