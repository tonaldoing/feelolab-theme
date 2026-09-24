<?php
/**
 * Menú accesible: patrón "disclosure". Cada ítem con submenú lleva un <button aria-expanded>
 * que lo abre con clic, Enter o Espacio. Nada depende del hover (inaccesible en teclado y en táctil).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'walker_nav_menu_start_el',
	static function ( string $output, $item, int $depth, $args ): string {
		if ( 'primary' !== ( $args->theme_location ?? '' ) || ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
			return $output;
		}
		$output .= sprintf(
			'<button type="button" class="submenu-toggle" aria-expanded="false"><span class="screen-reader-text">%s</span>%s</button>',
			/* translators: %s: nombre del ítem de menú */
			esc_html( sprintf( __( 'Mostrar submenú de %s', 'feelolab' ), wp_strip_all_tags( $item->title ) ) ),
			feelolab_icon( 'chevron-down' )
		);
		return $output;
	},
	10,
	4
);

/** Menú de respaldo: sin menú asignado, las páginas de primer nivel (nunca un header vacío). */
function feelolab_fallback_menu(): void {
	echo '<ul class="menu">';
	wp_list_pages(
		array(
			'depth'    => 1,
			'title_li' => '',
			'number'   => 6,
		)
	);
	echo '</ul>';
}
