<?php
/**
 * Fuentes web self-hosted (opcionales). Por defecto el tema usa fuentes del sistema;
 * elegir una combinación "web" en el Personalizador carga solo los archivos de esa combinación.
 *
 * - woff2 variables, subset latino (incluye ñ y acentos): un archivo por familia, todos los pesos.
 * - Servidas desde el propio sitio: sin requests a Google (más rápido y sin problemas de GDPR).
 * - Precarga solo de los archivos en uso (máximo dos).
 * - Cada familia tiene una "fallback" con métricas ajustadas (size-adjust, ascent/descent-override)
 *   calculadas contra Arial/Times: mientras carga la web font, el texto ocupa exactamente el mismo
 *   lugar y no hay salto de layout (CLS 0).
 *
 * Licencias OFL en assets/fonts/LICENSE-*.txt.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

/**
 * Métricas: frecuencia de letras del español sobre las métricas reales de cada archivo,
 * comparadas con Liberation Sans/Serif (clones métricos de Arial/Times).
 *
 * @return array<string, array{family: string, file: string, fallback: string, metrics: string}>
 */
function feelolab_web_fonts(): array {
	$sans  = 'local("Arial"), local("Liberation Sans"), local("Arimo")';
	$serif = 'local("Times New Roman"), local("Liberation Serif"), local("Tinos")';

	return apply_filters(
		'feelolab_web_fonts',
		array(
			'inter'          => array(
				'family'   => 'Inter',
				'file'     => 'inter.woff2',
				'fallback' => $sans,
				'metrics'  => 'size-adjust:106.68%;ascent-override:90.81%;descent-override:22.61%;line-gap-override:0%',
			),
			'figtree'        => array(
				'family'   => 'Figtree',
				'file'     => 'figtree.woff2',
				'fallback' => $sans,
				'metrics'  => 'size-adjust:98.87%;ascent-override:96.08%;descent-override:25.29%;line-gap-override:0%',
			),
			'fraunces'       => array(
				'family'   => 'Fraunces',
				'file'     => 'fraunces.woff2',
				'fallback' => $serif,
				'metrics'  => 'size-adjust:127.58%;ascent-override:76.66%;descent-override:19.99%;line-gap-override:0%',
			),
			'source-serif-4' => array(
				'family'   => 'Source Serif 4',
				'file'     => 'source-serif-4.woff2',
				'fallback' => $serif,
				'metrics'  => 'size-adjust:119.40%;ascent-override:86.77%;descent-override:28.06%;line-gap-override:0%',
			),
		)
	);
}

/** Stack CSS de una web font: la fuente, su fallback ajustada y el genérico. */
function feelolab_web_font_stack( string $key ): string {
	$font    = feelolab_web_fonts()[ $key ];
	$generic = str_contains( $font['fallback'], 'Times' ) ? 'serif' : 'sans-serif';
	return sprintf( '"%1$s", "%1$s Fallback", %2$s', $font['family'], $generic );
}

/** @return string[] Claves de web fonts que usa la combinación elegida. */
function feelolab_active_web_fonts(): array {
	$stacks = feelolab_font_stacks();
	$pair   = (string) get_theme_mod( 'feelolab_font_pair', 'sistema' );
	return isset( $stacks[ $pair ]['webfonts'] ) ? array_values( array_unique( $stacks[ $pair ]['webfonts'] ) ) : array();
}

function feelolab_font_url( string $key ): string {
	return FEELOLAB_URI . '/assets/fonts/' . feelolab_web_fonts()[ $key ]['file'] . '?ver=' . FEELOLAB_VERSION;
}

/** @font-face de la fuente y de su fallback con métricas. Va inline junto a las variables de marca. */
function feelolab_font_face_css(): string {
	$css   = '';
	$fonts = feelolab_web_fonts();
	foreach ( feelolab_active_web_fonts() as $key ) {
		$font = $fonts[ $key ];
		$css .= sprintf(
			'@font-face{font-family:"%1$s";src:url(%2$s) format("woff2");font-weight:100 900;font-style:normal;font-display:swap}',
			$font['family'],
			esc_url( feelolab_font_url( $key ) )
		);
		$css .= sprintf( '@font-face{font-family:"%1$s Fallback";src:%2$s;%3$s}', $font['family'], $font['fallback'], $font['metrics'] );
	}
	return $css;
}

/** Precarga de las fuentes en uso, antes que cualquier CSS. */
add_action(
	'wp_head',
	static function (): void {
		foreach ( feelolab_active_web_fonts() as $key ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( feelolab_font_url( $key ) ) );
		}
	},
	1
);
