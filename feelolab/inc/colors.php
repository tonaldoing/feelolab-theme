<?php
/**
 * Colores y tipografía de marca → variables CSS, con contraste AA garantizado.
 *
 * Tres roles separados, porque un mismo color no sirve siempre para todo (un botón blanco con
 * texto oscuro es válido, pero links blancos sobre fondo blanco no se leen):
 * - Botones: fondo (el "primario", también color de marca) y texto. Texto vacío = automático
 *   (blanco o casi negro, el que más contraste dé). Si el elegido no llega a 4.5:1, se usa el automático.
 * - Links y acentos: vacío = el primario. Siempre se ajusta hasta 4.5:1 sobre el fondo.
 * - Si el botón casi no se distingue del fondo (menos de 3:1), lleva un borde visible.
 * Accesibilidad que no depende de que alguien se acuerde.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

/** @return array<string, array{label: string, default: string, description?: string}> */
function feelolab_color_settings(): array {
	return array(
		'primary'     => array(
			'label'   => __( 'Botones: fondo (color de marca)', 'feelolab' ),
			'default' => '#2447d8',
		),
		'button_text' => array(
			'label'       => __( 'Botones: texto', 'feelolab' ),
			'default'     => '',
			'description' => __( 'Vacío = automático: blanco o negro, el que mejor se lea sobre el botón.', 'feelolab' ),
		),
		'link'        => array(
			'label'       => __( 'Links y acentos', 'feelolab' ),
			'default'     => '',
			'description' => __( 'Links, volantas e íconos. Vacío = el color de los botones.', 'feelolab' ),
		),
		'secondary'   => array(
			'label'   => __( 'Secundario (bloques oscuros, footer)', 'feelolab' ),
			'default' => '#0f172a',
		),
		'bg'          => array(
			'label'   => __( 'Fondo', 'feelolab' ),
			'default' => '#ffffff',
		),
		'surface'     => array(
			'label'   => __( 'Superficie (secciones alternas, tarjetas)', 'feelolab' ),
			'default' => '#f4f4f5',
		),
		'text'        => array(
			'label'   => __( 'Texto', 'feelolab' ),
			'default' => '#1f2937',
		),
	);
}

function feelolab_color( string $key ): string {
	$settings = feelolab_color_settings();
	$value    = sanitize_hex_color( (string) get_theme_mod( 'feelolab_color_' . $key, $settings[ $key ]['default'] ) );
	return $value ? $value : $settings[ $key ]['default'];
}

/**
 * Color base de links y acentos: el elegido; si no hay, el del botón. Si el del botón es tan claro
 * (o tan parecido al fondo) que ni oscurecido quedaría bien —menos de 3:1—, el secundario y, si ese
 * tampoco se lee, el color del texto. Después siempre se ajusta a 4.5:1 sobre el fondo.
 */
function feelolab_link_base_color( string $primary, string $secondary, string $bg, string $text ): string {
	$chosen = feelolab_color( 'link' );
	if ( $chosen ) {
		return $chosen;
	}
	if ( feelolab_contrast( $primary, $bg ) >= 3 ) {
		return $primary;
	}
	return feelolab_contrast( $secondary, $bg ) >= 4.5 ? $secondary : $text;
}

/** Texto del botón: el elegido si se lee (4.5:1) sobre el fondo del botón; si no, el automático. */
function feelolab_button_text_color( string $primary ): string {
	$chosen = feelolab_color( 'button_text' );
	return ( $chosen && feelolab_contrast( $chosen, $primary ) >= 4.5 ) ? $chosen : feelolab_on_color( $primary );
}

/** @return array{0: int, 1: int, 2: int} */
function feelolab_hex_to_rgb( string $hex ): array {
	$hex = ltrim( $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
}

/** Luminancia relativa WCAG 2.x. */
function feelolab_luminance( string $hex ): float {
	$channels = array_map(
		static function ( int $c ): float {
			$c = $c / 255;
			return $c <= 0.03928 ? $c / 12.92 : ( ( $c + 0.055 ) / 1.055 ) ** 2.4;
		},
		feelolab_hex_to_rgb( $hex )
	);
	return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function feelolab_contrast( string $a, string $b ): float {
	$la = feelolab_luminance( $a );
	$lb = feelolab_luminance( $b );
	return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
}

/** Blanco o casi negro, el que más contraste dé. */
function feelolab_on_color( string $bg ): string {
	return feelolab_contrast( $bg, '#ffffff' ) >= feelolab_contrast( $bg, '#111111' ) ? '#ffffff' : '#111111';
}

/** Mezcla hacia negro (amount > 0) o blanco (amount < 0). */
function feelolab_shade( string $hex, float $amount ): string {
	$target = $amount > 0 ? 0 : 255;
	$amount = abs( $amount );
	$rgb    = array_map( static fn( int $c ) => (int) round( $c + ( $target - $c ) * $amount ), feelolab_hex_to_rgb( $hex ) );
	return sprintf( '#%02x%02x%02x', ...$rgb );
}

/**
 * Ajusta $color hasta llegar a $ratio sobre $bg, oscureciendo en fondos claros y aclarando en oscuros.
 */
function feelolab_ensure_contrast( string $color, string $bg, float $ratio = 4.5 ): string {
	$direction = feelolab_luminance( $bg ) > 0.4 ? 1 : -1;
	for ( $step = 0; $step <= 20; $step++ ) {
		$candidate = feelolab_shade( $color, $direction * $step * 0.05 );
		if ( feelolab_contrast( $candidate, $bg ) >= $ratio ) {
			return $candidate;
		}
	}
	return feelolab_on_color( $bg );
}

/**
 * Combinaciones tipográficas. Las que tienen 'webfonts' cargan archivos de assets/fonts (ver inc/fonts.php);
 * el resto usa fuentes del sistema y no descarga nada.
 *
 * @return array<string, array{label: string, body: string, heading: string, webfonts?: string[]}>
 */
function feelolab_font_stacks(): array {
	// Stacks del sistema (modernfontstacks.com): cero requests, cero CLS por cambio de fuente.
	$system    = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
	$humanist  = 'Seravek, "Gill Sans Nova", Ubuntu, Calibri, "DejaVu Sans", source-sans-pro, sans-serif';
	$geometric = 'Avenir, Montserrat, Corbel, "URW Gothic", source-sans-pro, sans-serif';
	$serif     = 'Charter, "Bitstream Charter", "Sitka Text", Cambria, serif';
	$old_style = '"Iowan Old Style", "Palatino Linotype", "URW Palladio L", P052, serif';

	return apply_filters(
		'feelolab_font_stacks',
		array(
			'sistema'       => array(
				'label'   => __( 'Sistema (neutra, la más rápida)', 'feelolab' ),
				'body'    => $system,
				'heading' => $system,
			),
			'humanista'     => array(
				'label'   => __( 'Humanista (cercana)', 'feelolab' ),
				'body'    => $humanist,
				'heading' => $humanist,
			),
			'geometrica'    => array(
				'label'   => __( 'Geométrica (moderna)', 'feelolab' ),
				'body'    => $system,
				'heading' => $geometric,
			),
			'editorial'     => array(
				'label'   => __( 'Editorial (títulos serif, texto sans)', 'feelolab' ),
				'body'    => $system,
				'heading' => $old_style,
			),
			'clasica'       => array(
				'label'   => __( 'Clásica (todo serif)', 'feelolab' ),
				'body'    => $serif,
				'heading' => $old_style,
			),
			'inter'         => array(
				'label'    => __( 'Web: Inter (moderna, muy legible) · 48 KB', 'feelolab' ),
				'body'     => feelolab_web_font_stack( 'inter' ),
				'heading'  => feelolab_web_font_stack( 'inter' ),
				'webfonts' => array( 'inter' ),
			),
			'figtree'       => array(
				'label'    => __( 'Web: Figtree (amable, geométrica) · 20 KB', 'feelolab' ),
				'body'     => feelolab_web_font_stack( 'figtree' ),
				'heading'  => feelolab_web_font_stack( 'figtree' ),
				'webfonts' => array( 'figtree' ),
			),
			'fraunces'      => array(
				'label'    => __( 'Web: Fraunces + Inter (títulos con carácter) · 85 KB', 'feelolab' ),
				'body'     => feelolab_web_font_stack( 'inter' ),
				'heading'  => feelolab_web_font_stack( 'fraunces' ),
				'webfonts' => array( 'fraunces', 'inter' ),
			),
			'editorial-web' => array(
				'label'    => __( 'Web: Source Serif + Figtree (editorial) · 71 KB', 'feelolab' ),
				'body'     => feelolab_web_font_stack( 'figtree' ),
				'heading'  => feelolab_web_font_stack( 'source-serif-4' ),
				'webfonts' => array( 'source-serif-4', 'figtree' ),
			),
		)
	);
}

/** Variables CSS de marca (y @font-face si hay web fonts). Inline en el <head>: ~600 bytes, evitan un parpadeo de color. */
function feelolab_brand_css(): string {
	$primary   = feelolab_color( 'primary' );
	$secondary = feelolab_color( 'secondary' );
	$bg        = feelolab_color( 'bg' );
	$surface   = feelolab_color( 'surface' );
	$text      = feelolab_ensure_contrast( feelolab_color( 'text' ), $bg, 7 );

	$on_primary = feelolab_button_text_color( $primary );
	$link       = feelolab_link_base_color( $primary, $secondary, $bg, $text );

	// Hover: se aleja del texto del botón (más contraste, nunca menos). Un botón muy claro se oscurece apenas.
	$hover = feelolab_luminance( $on_primary ) > 0.4
		? feelolab_shade( $primary, 0.18 )
		: feelolab_shade( $primary, feelolab_luminance( $primary ) > 0.85 ? 0.08 : -0.18 );
	$hover = feelolab_ensure_contrast( $hover, $on_primary );

	// Botón que casi no se distingue del fondo (un botón blanco sobre blanco): borde a 3:1.
	$btn_border = feelolab_contrast( $primary, $bg ) < 3 ? feelolab_ensure_contrast( $primary, $bg, 3 ) : 'transparent';

	$fonts = feelolab_font_stacks();
	$pair  = (string) get_theme_mod( 'feelolab_font_pair', 'sistema' );
	$pair  = isset( $fonts[ $pair ] ) ? $fonts[ $pair ] : $fonts['sistema'];

	$vars = array(
		'--c-primary'             => $primary,
		'--c-primary-hover'       => $hover,
		'--c-on-primary'          => $on_primary,
		'--c-btn-border'          => $btn_border,
		'--c-primary-text'        => feelolab_ensure_contrast( $link, $bg ),
		'--c-primary-on-surface'  => feelolab_ensure_contrast( $link, $surface ),
		'--c-secondary'           => $secondary,
		'--c-on-secondary'        => feelolab_on_color( $secondary ),
		'--c-accent-on-secondary' => feelolab_ensure_contrast( $link, $secondary ),
		'--c-bg'                  => $bg,
		'--c-surface'             => $surface,
		'--c-text'                => $text,
		// Texto secundario a 5:1 y no 4.5: margen para texto chico.
		'--c-muted'               => feelolab_ensure_contrast( feelolab_shade( $text, -0.35 ), $bg, 5 ),
		'--c-muted-on-surface'    => feelolab_ensure_contrast( feelolab_shade( $text, -0.35 ), $surface, 5 ),
		'--c-border'              => feelolab_shade( $bg, feelolab_luminance( $bg ) > 0.4 ? 0.14 : -0.2 ),
		'--f-body'                => $pair['body'],
		'--f-heading'             => $pair['heading'],
		'--radius'                => absint( get_theme_mod( 'feelolab_radius', 8 ) ) . 'px',
		'--container'             => absint( get_theme_mod( 'feelolab_container', 1200 ) ) . 'px',
		'--fs-base'               => ( absint( get_theme_mod( 'feelolab_font_size', 17 ) ) / 16 ) . 'rem',
	);

	$css = feelolab_font_face_css() . ':root{';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}
	return $css . '}';
}

/**
 * La paleta del editor de bloques sale de los mismos colores: lo que el cliente elige en el
 * Personalizador es lo que ve al pintar un párrafo o un botón.
 */
add_filter(
	'wp_theme_json_data_theme',
	static function ( $theme_json ) {
		$palette = array();
		$names   = array(
			'primary'   => __( 'Botones', 'feelolab' ),
			'secondary' => __( 'Secundario', 'feelolab' ),
			'surface'   => __( 'Superficie', 'feelolab' ),
			'text'      => __( 'Texto', 'feelolab' ),
			'bg'        => __( 'Fondo', 'feelolab' ),
		);
		foreach ( $names as $slug => $name ) {
			$palette[] = array(
				'slug'  => $slug,
				'name'  => $name,
				'color' => feelolab_color( $slug ),
			);
		}
		return $theme_json->update_with(
			array(
				'version'  => 3,
				'settings' => array( 'color' => array( 'palette' => $palette ) ),
			)
		);
	}
);
