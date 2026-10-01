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
			'label'    => __( 'Servicios', 'feelolab' ),
			'module'   => 'servicios',
			'template' => 'coleccion',
			'list'     => array(
				'card'     => 'servicio',
				'taxonomy' => 'feelo_servicio_cat',
				'id'       => 'servicios',
				'empty'    => __( 'Todavía no hay servicios cargados. Se cargan en el panel, en Servicios → Añadir nuevo; acá elegís cómo se muestran.', 'feelolab' ),
			),
			'show'     => true,
			'order'    => 20,
			'fields'   => feelolab_home_list_fields(
				array(
					'title'    => __( 'Servicios', 'feelolab' ),
					'count'    => 6,
					'taxonomy' => 'feelo_servicio_cat',
					'more'     => __( 'Ver todos los servicios', 'feelolab' ),
				)
			) + array(
				'show_media' => array( 'type' => 'checkbox', 'label' => __( 'Mostrar ícono o imagen', 'feelolab' ), 'default' => true ),
				'show_text'  => array( 'type' => 'checkbox', 'label' => __( 'Mostrar la descripción corta', 'feelolab' ), 'default' => true ),
				'show_price' => array( 'type' => 'checkbox', 'label' => __( 'Mostrar el precio "desde"', 'feelolab' ), 'default' => true ),
			),
		),
		'productos'   => array(
			'label'    => __( 'Productos', 'feelolab' ),
			'module'   => 'productos',
			'template' => 'coleccion',
			'list'     => array(
				'card'     => 'producto',
				'taxonomy' => 'feelo_producto_cat',
				'id'       => 'productos',
				'empty'    => __( 'Todavía no hay productos cargados. Se cargan en el panel, en Productos → Añadir nuevo (o en lote desde Importar productos).', 'feelolab' ),
			),
			'show'     => false,
			'order'    => 25,
			'fields'   => feelolab_home_list_fields(
				array(
					'title'    => __( 'Productos', 'feelolab' ),
					'count'    => 8,
					'taxonomy' => 'feelo_producto_cat',
					'columns'  => '4',
					'more'     => __( 'Ver todos los productos', 'feelolab' ),
				)
			),
		),
		'nosotros'    => array(
			'label'  => __( 'Sobre nosotros', 'feelolab' ),
			'empty'  => __( 'Escribí el texto o elegí una imagen acá abajo y la sección aparece.', 'feelolab' ),
			'show'   => true,
			'order'  => 30,
			'fields' => array(
				'title'     => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Quiénes somos', 'feelolab' ) ),
				'text'      => array( 'type' => 'textarea', 'label' => __( 'Texto', 'feelolab' ) ),
				'image'     => array( 'type' => 'image', 'label' => __( 'Imagen', 'feelolab' ) ),
				'link_text' => array( 'type' => 'text', 'label' => __( 'Link: texto', 'feelolab' ), 'default' => __( 'Conocenos', 'feelolab' ) ),
				'link_url'  => array( 'type' => 'url', 'label' => __( 'Link: URL', 'feelolab' ) ),
				'side'      => feelolab_home_side_field( 'derecha' ),
				'bg'        => feelolab_home_bg_field( 'gris' ),
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
				'bg'      => feelolab_home_bg_field( 'oscuro' ),
			),
		),
		'proyectos'   => array(
			'label'    => __( 'Proyectos', 'feelolab' ),
			'module'   => 'proyectos',
			'template' => 'coleccion',
			'list'     => array(
				'card'     => 'proyecto',
				'taxonomy' => 'feelo_proyecto_tipo',
				'id'       => 'proyectos',
				'empty'    => __( 'Todavía no hay proyectos cargados. Se cargan en el panel, en Proyectos → Añadir nuevo.', 'feelolab' ),
			),
			'show'     => true,
			'order'    => 45,
			'fields'   => feelolab_home_list_fields(
				array(
					'title'    => __( 'Proyectos', 'feelolab' ),
					'count'    => 3,
					'taxonomy' => 'feelo_proyecto_tipo',
					'more'     => __( 'Ver todos los proyectos', 'feelolab' ),
				)
			),
		),
		'equipo'      => array(
			'label'    => __( 'Equipo', 'feelolab' ),
			'module'   => 'equipo',
			'template' => 'coleccion',
			'list'     => array(
				'card'     => 'miembro',
				'taxonomy' => 'feelo_area',
				'id'       => 'equipo',
				'empty'    => __( 'Todavía no hay personas cargadas. Se cargan en el panel, en Equipo → Añadir nuevo.', 'feelolab' ),
			),
			'show'     => false,
			'order'    => 47,
			'fields'   => feelolab_home_list_fields(
				array(
					'title'    => __( 'Nuestro equipo', 'feelolab' ),
					'count'    => 4,
					'taxonomy' => 'feelo_area',
					'columns'  => '4',
					'more'     => __( 'Conocé al equipo', 'feelolab' ),
				)
			),
		),
		'testimonios' => array(
			'label'  => __( 'Testimonios', 'feelolab' ),
			'module' => 'testimonios',
			'show'   => true,
			'order'  => 50,
			'list'   => array(
				'empty' => __( 'Todavía no hay testimonios cargados. Se cargan en el panel, en Testimonios → Añadir nuevo.', 'feelolab' ),
			),
			'fields' => feelolab_home_list_fields(
				array(
					'title' => __( 'Lo que dicen nuestros clientes', 'feelolab' ),
					'count' => 3,
					'more'  => false,
					'bg'    => 'gris',
				)
			),
		),
		'clientes'    => array(
			'label'  => __( 'Logos de clientes', 'feelolab' ),
			'module' => 'clientes',
			'show'   => true,
			'order'  => 55,
			'list'   => array(
				'empty' => __( 'Todavía no hay logos cargados. Se cargan en el panel, en Clientes → Añadir nuevo (el logo va como imagen destacada).', 'feelolab' ),
			),
			'fields' => feelolab_home_list_fields(
				array(
					'title'   => __( 'Confían en nosotros', 'feelolab' ),
					'count'   => 24,
					'columns' => false,
					'more'    => false,
				)
			) + array(
				'color' => array( 'type' => 'checkbox', 'label' => __( 'Logos en color (si no, en gris y a color al pasar el mouse)', 'feelolab' ), 'default' => false ),
			),
		),
		'faq'         => array(
			'label'  => __( 'Preguntas frecuentes', 'feelolab' ),
			'module' => 'faq',
			'show'   => true,
			'order'  => 60,
			'list'   => array(
				'taxonomy' => 'feelo_faq_tema',
				'empty'    => __( 'Todavía no hay preguntas cargadas. Se cargan en el panel, en Preguntas frecuentes → Añadir nueva.', 'feelolab' ),
			),
			'fields' => feelolab_home_list_fields(
				array(
					'title'    => __( 'Preguntas frecuentes', 'feelolab' ),
					'count'    => 6,
					'columns'  => false,
					'taxonomy' => 'feelo_faq_tema',
					'more'     => false,
				)
			),
		),
		'nosotros_2'  => array(
			'label'    => __( 'Texto con imagen (segundo bloque)', 'feelolab' ),
			'empty'    => __( 'Escribí el texto o elegí una imagen acá abajo y la sección aparece.', 'feelolab' ),
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
				'bg'        => feelolab_home_bg_field( 'gris' ),
			),
		),
		'blog'        => array(
			'label'    => __( 'Últimas notas del blog', 'feelolab' ),
			'template' => 'coleccion',
			'list'     => array(
				'post_type' => 'post',
				'card'      => '',
				'taxonomy'  => 'category',
				'id'        => 'novedades',
				'empty'     => __( 'Todavía no hay notas publicadas. Se escriben en el panel, en Entradas → Añadir nueva.', 'feelolab' ),
			),
			'show'     => false,
			'order'    => 70,
			'fields'   => feelolab_home_list_fields(
				array(
					'title'    => __( 'Novedades', 'feelolab' ),
					'count'    => 3,
					'taxonomy' => 'category',
					'orderby'  => false,
					'more'     => __( 'Ver todas las notas', 'feelolab' ),
					'bg'       => 'gris',
				)
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
		'newsletter'  => array(
			'label'  => __( 'Newsletter', 'feelolab' ),
			'show'   => false,
			'order'  => 85,
			'fields' => array(
				'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Recibí nuestras novedades', 'feelolab' ) ),
				'text'  => array( 'type' => 'textarea', 'label' => __( 'Texto', 'feelolab' ), 'default' => __( 'Un email al mes con lo que te sirve. Sin spam.', 'feelolab' ) ),
				'bg'    => feelolab_home_bg_field( 'gris' ),
			),
		),
		'contacto'    => array(
			'label'  => __( 'Contacto con formulario', 'feelolab' ),
			'show'   => true,
			'order'  => 90,
			'fields' => array(
				'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => __( 'Contacto', 'feelolab' ) ),
				'text'  => array( 'type' => 'textarea', 'label' => __( 'Texto', 'feelolab' ) ),
				'bg'    => feelolab_home_bg_field( 'blanco' ),
			),
		),
	);

	return apply_filters( 'feelolab_home_sections', $sections );
}

/**
 * Campos comunes de las secciones que listan contenido (servicios, productos, notas…).
 *
 * @param array<string, mixed> $o title, count, columns ('2'|'3'|'4'|false), taxonomy, orderby (false = sin
 *                                elegir orden), more (texto del botón "ver todos", false = sin botón), bg.
 * @return array<string, array<string, mixed>>
 */
function feelolab_home_list_fields( array $o ): array {
	$o      = array_merge(
		array(
			'title'    => '',
			'count'    => 6,
			'columns'  => '3',
			'taxonomy' => '',
			'orderby'  => true,
			'more'     => '',
			'bg'       => 'blanco',
		),
		$o
	);
	$fields = array(
		'title' => array( 'type' => 'text', 'label' => __( 'Título', 'feelolab' ), 'default' => $o['title'] ),
		'text'  => array( 'type' => 'textarea', 'label' => __( 'Bajada (opcional)', 'feelolab' ) ),
		'count' => array( 'type' => 'number', 'label' => __( 'Cuántos mostrar', 'feelolab' ), 'default' => $o['count'] ),
	);
	if ( $o['columns'] ) {
		$fields['columns'] = array(
			'type'    => 'select',
			'label'   => __( 'Columnas (en pantallas grandes)', 'feelolab' ),
			'default' => (string) $o['columns'],
			'choices' => array(
				'2' => __( 'Dos', 'feelolab' ),
				'3' => __( 'Tres', 'feelolab' ),
				'4' => __( 'Cuatro', 'feelolab' ),
			),
		);
	}
	if ( $o['taxonomy'] ) {
		$taxonomy           = (string) $o['taxonomy'];
		$fields['category'] = array(
			'type'    => 'select',
			'label'   => __( 'Mostrar', 'feelolab' ),
			'default' => '',
			// Se arma solo en el Personalizador: en el sitio no hace falta la lista de categorías.
			'choices' => static fn() => feelolab_home_term_choices( $taxonomy ),
		);
	}
	if ( $o['orderby'] ) {
		$fields['orderby'] = array(
			'type'    => 'select',
			'label'   => __( 'Orden', 'feelolab' ),
			'default' => 'manual',
			'choices' => array(
				'manual' => __( 'Manual (campo "Orden" de cada uno)', 'feelolab' ),
				'fecha'  => __( 'Los más nuevos primero', 'feelolab' ),
				'titulo' => __( 'Alfabético', 'feelolab' ),
			),
		);
	}
	if ( false !== $o['more'] ) {
		$fields['more_text'] = array( 'type' => 'text', 'label' => __( 'Botón "ver todos": texto (vacío = sin botón)', 'feelolab' ), 'default' => $o['more'] );
	}
	$fields['bg'] = feelolab_home_bg_field( (string) $o['bg'] );
	return $fields;
}

/** @return array<string, string> "Todas" + las categorías con contenido de una taxonomía. */
function feelolab_home_term_choices( string $taxonomy ): array {
	$choices = array( '' => __( 'Todas las categorías', 'feelolab' ) );
	$terms   = taxonomy_exists( $taxonomy ) ? get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		)
	) : array();
	foreach ( is_array( $terms ) ? $terms : array() as $term ) {
		/* translators: %s: nombre de la categoría */
		$choices[ $term->slug ] = sprintf( __( 'Solo "%s"', 'feelolab' ), $term->name );
	}
	return $choices;
}

/** Campo "fondo" de una sección. */
function feelolab_home_bg_field( string $fallback ): array {
	return array(
		'type'    => 'select',
		'label'   => __( 'Fondo', 'feelolab' ),
		'default' => $fallback,
		'choices' => array(
			'blanco' => __( 'Color de fondo del sitio', 'feelolab' ),
			'gris'   => __( 'Color de superficie (suave)', 'feelolab' ),
			'oscuro' => __( 'Color secundario (contrastado)', 'feelolab' ),
		),
	);
}

/** Clases del <section> según el fondo elegido. */
function feelolab_home_section_class( string $key ): string {
	$classes = array(
		'gris'   => 'section section--surface',
		'oscuro' => 'section section--dark',
	);
	return $classes[ (string) feelolab_home( $key, 'bg' ) ] ?? 'section';
}

/** Clase de la grilla según las columnas elegidas. */
function feelolab_home_grid_class( string $key ): string {
	$columns = (string) feelolab_home( $key, 'columns' );
	return 'grid grid--' . ( in_array( $columns, array( '2', '3', '4' ), true ) ? $columns : '3' );
}

/**
 * Argumentos de WP_Query de una sección que lista contenido: cantidad, categoría y orden elegidos.
 *
 * @return array<string, mixed>
 */
function feelolab_home_query_args( string $key, string $post_type ): array {
	$sections = feelolab_home_sections();
	$list     = $sections[ $key ]['list'] ?? array();
	$orders   = array(
		'manual' => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'fecha'  => array( 'date' => 'DESC' ),
		'titulo' => array( 'title' => 'ASC' ),
	);
	$orderby  = isset( $sections[ $key ]['fields']['orderby'] ) ? (string) feelolab_home( $key, 'orderby' ) : 'fecha';
	$args     = array(
		'post_type'           => $post_type,
		'posts_per_page'      => min( 48, max( 1, (int) feelolab_home( $key, 'count' ) ) ),
		'orderby'             => $orders[ $orderby ] ?? $orders['manual'],
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	$term     = isset( $sections[ $key ]['fields']['category'] ) ? (string) feelolab_home( $key, 'category' ) : '';
	if ( '' !== $term && ! empty( $list['taxonomy'] ) ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- una sola categoría, con caché de objetos.
		$args['tax_query'] = array(
			array(
				'taxonomy' => $list['taxonomy'],
				'field'    => 'slug',
				'terms'    => $term,
			),
		);
	}
	return $args;
}

/**
 * Sección sin contenido: en el sitio no se muestra; en la vista previa del Personalizador, un aviso
 * que dice por qué no aparece y dónde se carga (si no, parece que la opción no anda).
 */
function feelolab_home_placeholder( string $key ): void {
	if ( ! is_customize_preview() ) {
		return;
	}
	$sections = feelolab_home_sections();
	$message  = (string) ( $sections[ $key ]['list']['empty'] ?? $sections[ $key ]['empty'] ?? '' );
	printf(
		'<section class="section home-placeholder" aria-label="%1$s"><div class="container"><p class="home-placeholder__box"><strong>%2$s</strong> %3$s</p></div></section>',
		esc_attr( $sections[ $key ]['label'] ?? $key ),
		esc_html( $sections[ $key ]['label'] ?? $key ),
		esc_html( $message ? $message : __( 'Esta sección todavía no tiene contenido: no se muestra en el sitio.', 'feelolab' ) )
	);
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
