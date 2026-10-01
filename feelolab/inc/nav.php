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

/**
 * Menú de respaldo, mientras no haya un menú asignado (Apariencia → Menús o Personalizar → Menús):
 * Inicio, los contenidos que tienen algo publicado (Servicios, Productos…), el blog, las páginas
 * propias y Contacto al final. Nunca un encabezado vacío ni la "Página de ejemplo" de WordPress.
 */
function feelolab_fallback_menu(): void {
	echo '<ul class="menu">';
	foreach ( feelolab_fallback_menu_items() as $item ) {
		printf(
			'<li class="menu-item%1$s"><a href="%2$s"%3$s>%4$s</a></li>',
			$item['current'] ? ' current-menu-item' : '',
			esc_url( $item['url'] ),
			$item['current'] ? ' aria-current="page"' : '',
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}

/** @return array<int, array{label: string, url: string, current: bool}> */
function feelolab_fallback_menu_items(): array {
	$items = array(
		array(
			'label'   => __( 'Inicio', 'feelolab' ),
			'url'     => home_url( '/' ),
			'current' => is_front_page(),
		),
	);

	// Contenidos del plugin con al menos una publicación.
	if ( function_exists( 'feelo_module_post_type' ) ) {
		foreach ( array( 'servicios', 'productos', 'proyectos', 'equipo', 'sedes' ) as $module ) {
			$type = feelo_module_post_type( $module );
			$link = $type ? get_post_type_archive_link( $type ) : false;
			if ( ! $link || ! (int) ( wp_count_posts( $type )->publish ?? 0 ) ) {
				continue;
			}
			$items[] = array(
				'label'   => (string) get_post_type_object( $type )->labels->name,
				'url'     => $link,
				'current' => is_post_type_archive( $type ) || is_singular( $type ),
			);
		}
	}

	$front   = (int) get_option( 'page_on_front' );
	$blog    = (int) get_option( 'page_for_posts' );
	$skip    = array_filter( array( $front, $blog, (int) get_option( 'wp_page_for_privacy_policy' ) ) );
	$contact = 0;
	if ( $blog && (int) ( wp_count_posts()->publish ?? 0 ) ) {
		$items[] = array(
			'label'   => get_the_title( $blog ),
			'url'     => (string) get_permalink( $blog ),
			'current' => is_home() || is_singular( 'post' ),
		);
	}
	$pages = get_pages(
		array(
			'parent'      => 0,
			'sort_column' => 'menu_order,post_title',
			'exclude'     => $skip, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- tres páginas como mucho.
			'number'      => 12,
		)
	);
	foreach ( $pages as $page ) {
		// El contenido de ejemplo de WordPress no es del sitio.
		if ( in_array( $page->post_name, array( 'sample-page', 'pagina-ejemplo', 'pagina-de-ejemplo' ), true ) ) {
			continue;
		}
		if ( ! $contact && ( 'page-templates/contacto.php' === get_page_template_slug( $page ) || in_array( $page->post_name, array( 'contacto', 'contact' ), true ) ) ) {
			$contact = $page->ID;
			continue;
		}
		$items[] = array(
			'label'   => get_the_title( $page ),
			'url'     => (string) get_permalink( $page ),
			'current' => is_page( $page->ID ),
		);
	}
	// Seis enlaces como mucho (más no entra en una línea) y Contacto siempre al final.
	$items = array_slice( $items, 0, $contact ? 5 : 6 );
	if ( $contact ) {
		$items[] = array(
			'label'   => get_the_title( $contact ),
			'url'     => (string) get_permalink( $contact ),
			'current' => is_page( $contact ),
		);
	}
	return $items;
}
