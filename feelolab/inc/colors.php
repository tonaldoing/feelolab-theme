<?php
/**
 * Colores y tipografía de marca → variables CSS, con contraste AA garantizado.
 *
 * El cliente elige un color primario cualquiera. El tema calcula:
 * - --c-on-primary: blanco o casi negro, el que más contraste dé sobre el primario (botones).
 * - --c-primary-text: el primario oscurecido/aclarado hasta 4.5:1 sobre el fondo (links, volantas).
 * Así un amarillo de marca no deja texto ilegible. Es accesibilidad que no depende de que alguien se acuerde.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

/** @return array<string, array{label: string, default: string}> */
function feelolab_color_settings(): array {
	return array(
		'primary'   => array(
			'label'   => __( 'Primario (botones, links, acentos)', 'feelolab' ),
			'default' => '#2447d8',
		),
		'secondary' => array(
			'label'   => __( 'Secundario (bloques oscuros, footer)', 'feelolab' ),
			'default' => '#0f172a',
		),
		'bg'        => array(
			'label'   => __( 'Fondo', 'feelolab' ),
			'default' => '#ffffff',
		),
		'surface'   => array(
			'label'   => __( 'Superficie (secciones alternas, tarjetas)', 'feelolab' ),
			'default' => '#f4f4f5',
		),
		'text'      => array(
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

/** @return array<string, array{label: string, body: string, heading: string}> */
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
			'sistema'    => array(
				'label'   => __( 'Sistema (neutra, la más rápida)', 'feelolab' ),
				'body'    => $system,
				'heading' => $system,
			),
			'humanista'  => array(
				'label'   => __( 'Humanista (cercana)', 'feelolab' ),
				'body'    => $humanist,
				'heading' => $humanist,
			),
			'geometrica' => array(
				'label'   => __( 'Geométrica (moderna)', 'feelolab' ),
				'body'    => $system,
				'heading' => $geometric,
			),
			'editorial'  => array(
				'label'   => __( 'Editorial (títulos serif, texto sans)', 'feelolab' ),
				'body'    => $system,
				'heading' => $old_style,
			),
			'clasica'    => array(
				'label'   => __( 'Clásica (todo serif)', 'feelolab' ),
				'body'    => $serif,
				'heading' => $old_style,
			),
		)
	);
}

/** Variables CSS de marca. Van inline en el <head>: son ~600 bytes y evitan un parpadeo de color. */
function feelolab_brand_css(): string {
	$primary   = feelolab_color( 'primary' );
	$secondary = feelolab_color( 'secondary' );
	$bg        = feelolab_color( 'bg' );
	$surface   = feelolab_color( 'surface' );
	$text      = feelolab_ensure_contrast( feelolab_color( 'text' ), $bg, 7 );

	$on_white = ( '#ffffff' === feelolab_on_color( $primary ) );

	$fonts = feelolab_font_stacks();
	$pair  = (string) get_theme_mod( 'feelolab_font_pair', 'sistema' );
	$pair  = isset( $fonts[ $pair ] ) ? $fonts[ $pair ] : $fonts['sistema'];

	$vars = array(
		'--c-primary'             => $primary,
		// El hover se aleja del color del texto del botón: con texto blanco oscurece (más contraste, nunca menos).
		'--c-primary-hover'       => feelolab_shade( $primary, $on_white ? 0.18 : -0.18 ),
		'--c-on-primary'          => feelolab_on_color( $primary ),
		'--c-primary-text'        => feelolab_ensure_contrast( $primary, $bg ),
		'--c-primary-on-surface'  => feelolab_ensure_contrast( $primary, $surface ),
		'--c-secondary'           => $secondary,
		'--c-on-secondary'        => feelolab_on_color( $secondary ),
		'--c-accent-on-secondary' => feelolab_ensure_contrast( $primary, $secondary ),
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

	$css = ':root{';
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
			'primary'   => __( 'Primario', 'feelolab' ),
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
