<?php
/**
 * Home por secciones: cada sección se prende, se ordena y se completa desde el Personalizador.
 * El HTML de cada una está en template-parts/home/{clave}.php.
 *
 * Secciones fijas en lugar de un constructor libre: el cliente no puede romper el diseño,
 * la performance es predecible y el marcado semántico (un solo h1, h2 por sección) está garantizado.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

/**
 * Definición de secciones.
 *
 * - module: módulo de Feelolab Core que necesita; si no está activo, la sección no se ofrece.
 * - template: plantilla de template-parts/home/ si no es la clave (las secciones repetidas, como
 *   la segunda llamada a la acción, reusan la plantilla de la primera con sus propios textos).
 * - fields: campos editables (text, textarea, url, image, number, select con choices).
 *
 * @return array<string, array<string, mixed>>
 */
function feelolab_home_sections(): array {
	$sections = array(
		'hero'        => array(
			'label'  => __( 'Portada (hero)', 'feelolab' ),
			'show'   => true,
			'order'  => 10,
			'fields' => array(
				'layout'    => array(
					'type'    => 'select',
					'label'   => __( 'Diseño', 'feelolab' ),
					'default' => 'dividida',
					'choices' => array(
						'dividida' => __( 'Texto e imagen lado a lado', 'feelolab' ),
						'fondo'    => __( 'Imagen de fondo a todo el ancho', 'feelolab' ),
						'centrada' => __( 'Solo texto, centrado', 'feelolab' ),
					),
				),
				'title'     => array( 'type' => 'text', 'label' => __( 'Título (es el h1 de la home)', 'feelolab' ), 'default' => get_bloginfo( 'name' ) ),
				'text'      => array( 'type' => 'textarea', 'label' => __( 'Bajada', 'feelolab' ), 'default' => get_bloginfo( 'description' ) ),
				'image'     => array( 'type' => 'image', 'label' => __( 'Imagen (1600×900 o más)', 'feelolab' ) ),
				'cta1_text' => array( 'type' => 'text', 'label' => __( 'Botón principal: texto', 'feelolab' ), 'default' => __( 'Contactanos', 'feelolab' ) ),
				'cta1_url'  => array( 'type' => 'url', 'label' => __( 'Botón principal: link', 'feelolab' ), 'default' => '#contacto' ),
				'cta2_text' => array( 'type' => 'text', 'label' => __( 'Botón secundario: texto', 'feelolab' ) ),
				'cta2_url'  => array( 'type' => 'url', 'label' => __( 'Botón secundario: link', 'feelolab' ) ),
			),
		),
		'servicios'   => array(
			'label'  => __( 'Servicios', 'feelolab' ),
			'module' => 'servicios',
			'show'   => true,
			'order'  => 20,
			'fields' => array(
				'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Servicios', 'feelolab' ) ),
				'text'  => array( 'type' => 'textarea', 'label' => __( 'Bajada', 'feelolab' ) ),
				'count' => array( 'type' => 'number', 'label' => __( 'Cantidad', 'feelolab' ), 'default' => 6 ),
			),
		),
		'nosotros'    => array(
			'label'  => __( 'Sobre nosotros', 'feelolab' ),
			'show'   => true,
			'order'  => 30,
			'fields' => array(
				'title'     => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Quiénes somos', 'feelolab' ) ),
				'text'      => array( 'type' => 'textarea', 'label' => __( 'Texto', 'feelolab' ) ),
				'image'     => array( 'type' => 'image', 'label' => __( 'Imagen', 'feelolab' ) ),
				'link_text' => array( 'type' => 'text', 'label' => __( 'Link: texto', 'feelolab' ), 'default' => __( 'Conocenos', 'feelolab' ) ),
				'link_url'  => array( 'type' => 'url', 'label' => __( 'Link: URL', 'feelolab' ) ),
				'side'      => feelolab_home_side_field( 'derecha' ),
			),
		),
		'cta_2'       => array(
			'label'    => __( 'Llamada a la acción (segunda)', 'feelolab' ),
			'template' => 'cta',
			'show'     => false,
			'order'    => 35,
			'fields'   => array(
				'title'    => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( '¿Necesitás una mano?', 'feelolab' ) ),
				'text'     => array( 'type' => 'textarea', 'label' => __( 'Texto', 'feelolab' ) ),
				'btn_text' => array( 'type' => 'text', 'label' => __( 'Botón: texto', 'feelolab' ), 'default' => __( 'Consultanos', 'feelolab' ) ),
				'btn_url'  => array( 'type' => 'url', 'label' => __( 'Botón: link (vacío = WhatsApp o contacto)', 'feelolab' ) ),
			),
		),
		'cifras'      => array(
			'label'  => __( 'Cifras', 'feelolab' ),
			'show'   => false,
			'order'  => 40,
			'fields' => array(
				'title'   => array( 'type' => 'text', 'label' => __( 'Título (opcional)', 'feelolab' ) ),
				'value_1' => array( 'type' => 'text', 'label' => __( 'Cifra 1', 'feelolab' ), 'default' => '+15' ),
				'label_1' => array( 'type' => 'text', 'label' => __( 'Texto 1', 'feelolab' ), 'default' => __( 'años de experiencia', 'feelolab' ) ),
				'value_2' => array( 'type' => 'text', 'label' => __( 'Cifra 2', 'feelolab' ) ),
				'label_2' => array( 'type' => 'text', 'label' => __( 'Texto 2', 'feelolab' ) ),
				'value_3' => array( 'type' => 'text', 'label' => __( 'Cifra 3', 'feelolab' ) ),
				'label_3' => array( 'type' => 'text', 'label' => __( 'Texto 3', 'feelolab' ) ),
				'value_4' => array( 'type' => 'text', 'label' => __( 'Cifra 4', 'feelolab' ) ),
				'label_4' => array( 'type' => 'text', 'label' => __( 'Texto 4', 'feelolab' ) ),
			),
		),
		'proyectos'   => array(
			'label'  => __( 'Proyectos', 'feelolab' ),
			'module' => 'proyectos',
			'show'   => true,
			'order'  => 45,
			'fields' => array(
				'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Proyectos', 'feelolab' ) ),
				'count' => array( 'type' => 'number', 'label' => __( 'Cantidad', 'feelolab' ), 'default' => 3 ),
			),
		),
		'testimonios' => array(
			'label'  => __( 'Testimonios', 'feelolab' ),
			'module' => 'testimonios',
			'show'   => true,
			'order'  => 50,
			'fields' => array(
				'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Lo que dicen nuestros clientes', 'feelolab' ) ),
				'count' => array( 'type' => 'number', 'label' => __( 'Cantidad', 'feelolab' ), 'default' => 3 ),
			),
		),
		'clientes'    => array(
			'label'  => __( 'Logos de clientes', 'feelolab' ),
			'module' => 'clientes',
			'show'   => true,
			'order'  => 55,
			'fields' => array(
				'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Confían en nosotros', 'feelolab' ) ),
			),
		),
		'faq'         => array(
			'label'  => __( 'Preguntas frecuentes', 'feelolab' ),
			'module' => 'faq',
			'show'   => true,
			'order'  => 60,
			'fields' => array(
				'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Preguntas frecuentes', 'feelolab' ) ),
				'count' => array( 'type' => 'number', 'label' => __( 'Cantidad', 'feelolab' ), 'default' => 6 ),
			),
		),
		'nosotros_2'  => array(
			'label'    => __( 'Texto con imagen (segundo bloque)', 'feelolab' ),
			'template' => 'nosotros',
			'show'     => false,
			'order'    => 65,
			'fields'   => array(
				'title'     => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Cómo trabajamos', 'feelolab' ) ),
				'text'      => array( 'type' => 'textarea', 'label' => __( 'Texto', 'feelolab' ) ),
				'image'     => array( 'type' => 'image', 'label' => __( 'Imagen', 'feelolab' ) ),
				'link_text' => array( 'type' => 'text', 'label' => __( 'Link: texto', 'feelolab' ) ),
				'link_url'  => array( 'type' => 'url', 'label' => __( 'Link: URL', 'feelolab' ) ),
				'side'      => feelolab_home_side_field( 'izquierda' ),
			),
		),
		'blog'        => array(
			'label'  => __( 'Últimas notas del blog', 'feelolab' ),
			'show'   => false,
			'order'  => 70,
			'fields' => array(
				'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Novedades', 'feelolab' ) ),
				'count' => array( 'type' => 'number', 'label' => __( 'Cantidad', 'feelolab' ), 'default' => 3 ),
			),
		),
		'cta'         => array(
			'label'  => __( 'Llamada a la acción', 'feelolab' ),
			'show'   => true,
			'order'  => 80,
			'fields' => array(
				'title'    => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( '¿Hablamos?', 'feelolab' ) ),
				'text'     => array( 'type' => 'textarea', 'label' => __( 'Texto', 'feelolab' ) ),
				'btn_text' => array( 'type' => 'text', 'label' => __( 'Botón: texto', 'feelolab' ), 'default' => __( 'Escribinos', 'feelolab' ) ),
				'btn_url'  => array( 'type' => 'url', 'label' => __( 'Botón: link (vacío = WhatsApp o contacto)', 'feelolab' ) ),
			),
		),
		'contacto'    => array(
			'label'  => __( 'Contacto con formulario', 'feelolab' ),
			'show'   => true,
			'order'  => 90,
			'fields' => array(
				'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Contacto', 'feelolab' ) ),
				'text'  => array( 'type' => 'textarea', 'label' => __( 'Texto', 'feelolab' ) ),
			),
		),
	);

	return apply_filters( 'feelolab_home_sections', $sections );
}

/** ¿La sección está disponible (su módulo, si tiene, está activo)? */
function feelolab_home_section_available( array $section ): bool {
	if ( empty( $section['module'] ) ) {
		return true;
	}
	return function_exists( 'feelo_module_enabled' ) && feelo_module_enabled( $section['module'] );
}

/** Valor de un campo de sección. */
function feelolab_home( string $section, string $field ) {
	$sections = feelolab_home_sections();
	$default  = $sections[ $section ]['fields'][ $field ]['default'] ?? '';
	return get_theme_mod( "feelolab_home_{$section}_{$field}", $default );
}

/** Campo "lado de la imagen" de los bloques de texto con imagen. */
function feelolab_home_side_field( string $side ): array {
	return array(
		'type'    => 'select',
		'label'   => __( 'Imagen a la', 'feelolab' ),
		'default' => $side,
		'choices' => array(
			'derecha'   => __( 'Derecha', 'feelolab' ),
			'izquierda' => __( 'Izquierda', 'feelolab' ),
		),
	);
}

/** Plantilla de una sección (las repetidas usan la de la original). */
function feelolab_home_template( string $key ): string {
	$sections = feelolab_home_sections();
	return (string) ( $sections[ $key ]['template'] ?? $key );
}

/**
 * Todas las secciones disponibles, en orden (prendidas o no).
 *
 * El orden se arrastra en el Personalizador y se guarda como lista en feelolab_home_order.
 * Las que no están en la lista (sitios de antes de 0.5 o secciones nuevas) van según su número.
 *
 * @return string[]
 */
function feelolab_home_ordered_keys(): array {
	$numbers = array();
	foreach ( feelolab_home_sections() as $key => $section ) {
		if ( feelolab_home_section_available( $section ) ) {
			$numbers[ $key ] = (int) get_theme_mod( "feelolab_home_{$key}_order", $section['order'] );
		}
	}
	asort( $numbers );
	$by_number = array_keys( $numbers );

	$saved = array_filter( array_map( 'trim', explode( ',', (string) get_theme_mod( 'feelolab_home_order', '' ) ) ) );
	if ( ! $saved ) {
		return $by_number;
	}
	$ordered = array_values( array_intersect( $saved, $by_number ) );
	// Una sección nueva se ubica después de la que la precede por número.
	foreach ( $by_number as $i => $key ) {
		if ( in_array( $key, $ordered, true ) ) {
			continue;
		}
		$after = $i > 0 ? array_search( $by_number[ $i - 1 ], $ordered, true ) : -1;
		array_splice( $ordered, false === $after ? count( $ordered ) : $after + 1, 0, array( $key ) );
	}
	return $ordered;
}

/** @return string[] Claves de secciones visibles, en orden. */
function feelolab_home_active_sections(): array {
	$sections = feelolab_home_sections();
	return array_values(
		array_filter(
			feelolab_home_ordered_keys(),
			static fn( string $key ) => (bool) get_theme_mod( "feelolab_home_{$key}_show", $sections[ $key ]['show'] )
		)
	);
}

/** Renderiza una sección. En la vista previa del Personalizador va envuelta para refrescarla sola. */
function feelolab_home_render_section( string $key, int $index ): void {
	$preview = is_customize_preview();
	if ( $preview ) {
		echo '<div class="feelolab-home-partial" data-feelolab-home="' . esc_attr( $key ) . '">';
	}
	get_template_part(
		'template-parts/home/' . feelolab_home_template( $key ),
		null,
		array(
			'index' => $index,
			'key'   => $key,
		)
	);
	if ( $preview ) {
		echo '</div>';
	}
}

/** Diseño efectivo de la portada: "fondo" sin imagen no tiene sentido y pasa a "centrada". */
function feelolab_hero_layout(): string {
	$layout = (string) feelolab_home( 'hero', 'layout' );
	if ( ! in_array( $layout, array( 'dividida', 'fondo', 'centrada' ), true ) ) {
		$layout = 'dividida';
	}
	if ( 'fondo' === $layout && ! (int) feelolab_home( 'hero', 'image' ) ) {
		$layout = 'centrada';
	}
	return $layout;
}

/**
 * ¿El encabezado va transparente sobre la portada? Solo en la home, con la opción prendida,
 * la portada con imagen de fondo y como primera sección: si no, quedaría texto blanco sobre blanco.
 */
function feelolab_header_is_transparent(): bool {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$sections = is_front_page() && get_theme_mod( 'feelolab_header_transparent', false ) ? feelolab_home_active_sections() : array();
	$cache    = $sections && 'hero' === $sections[0] && 'fondo' === feelolab_hero_layout();
	return $cache;
}
