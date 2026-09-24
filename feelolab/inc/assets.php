<?php
/**
 * CSS y JS. Un solo CSS (con las variables de marca inline) y un JS chico, diferido.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

/** Versión por fecha de archivo en desarrollo, por versión del tema en producción. */
function feelolab_asset_version( string $relative ): string {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$path = FEELOLAB_DIR . '/' . $relative;
		return file_exists( $path ) ? (string) filemtime( $path ) : FEELOLAB_VERSION;
	}
	return FEELOLAB_VERSION;
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_enqueue_style( 'feelolab', FEELOLAB_URI . '/assets/css/main.css', array(), feelolab_asset_version( 'assets/css/main.css' ) );
		wp_add_inline_style( 'feelolab', feelolab_brand_css() );

		wp_enqueue_script(
			'feelolab',
			FEELOLAB_URI . '/assets/js/main.js',
			array(),
			feelolab_asset_version( 'assets/js/main.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
);

/**
 * Mismas variables en el editor: lo que se ve al editar es lo que se publica.
 * enqueue_block_assets (no enqueue_block_editor_assets) es el que llega al iframe del editor.
 */
add_action(
	'enqueue_block_assets',
	static function (): void {
		if ( ! is_admin() ) {
			return;
		}
		wp_register_style( 'feelolab-editor-vars', false, array(), FEELOLAB_VERSION );
		wp_enqueue_style( 'feelolab-editor-vars' );
		wp_add_inline_style( 'feelolab-editor-vars', str_replace( ':root', ':root,.editor-styles-wrapper', feelolab_brand_css() ) );
	}
);

/** Marca <html> con JS disponible, antes de pintar: el menú sin JS queda usable. */
add_action(
	'wp_head',
	static function (): void {
		echo "<script>document.documentElement.classList.replace('no-js','js');</script>\n";
	},
	0
);
