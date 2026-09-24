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
 * - fields: campos editables (text, textarea, url, image, number).
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

/** @return string[] Claves de secciones visibles, en orden. */
function feelolab_home_active_sections(): array {
	$active = array();
	foreach ( feelolab_home_sections() as $key => $section ) {
		if ( ! feelolab_home_section_available( $section ) ) {
			continue;
		}
		if ( ! get_theme_mod( "feelolab_home_{$key}_show", $section['show'] ) ) {
			continue;
		}
		$active[ $key ] = (int) get_theme_mod( "feelolab_home_{$key}_order", $section['order'] );
	}
	asort( $active );
	return array_keys( $active );
}
