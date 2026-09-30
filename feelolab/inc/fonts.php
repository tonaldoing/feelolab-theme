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
	$css   = feelolab_own_font_face_css();
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

/*
 * ---------- Fuente propia de la marca ----------
 * Se suben los .woff2 en Personalizar → Marca → Tipografía y forma, y se elige "Fuente propia".
 * Se sirven desde el propio sitio (Biblioteca de medios), con precarga y font-display:swap.
 */

/** @return array{heading: string, body: string, body_bold: string, generic: string} URLs de los archivos subidos. */
function feelolab_own_fonts(): array {
	$url = static function ( string $mod ): string {
		$id = absint( get_theme_mod( $mod, 0 ) );
		return $id ? (string) wp_get_attachment_url( $id ) : '';
	};
	return array(
		'heading'   => $url( 'feelolab_font_own_heading' ),
		'body'      => $url( 'feelolab_font_own_body' ),
		'body_bold' => $url( 'feelolab_font_own_body_bold' ),
		'generic'   => 'serif' === get_theme_mod( 'feelolab_font_own_generic', 'sans-serif' ) ? 'serif' : 'sans-serif',
	);
}

/** ¿Está elegida la fuente propia y hay al menos un archivo? */
function feelolab_own_fonts_active(): bool {
	if ( 'propia' !== get_theme_mod( 'feelolab_font_pair', 'sistema' ) ) {
		return false;
	}
	$own = feelolab_own_fonts();
	return '' !== $own['heading'] || '' !== $own['body'];
}

/** Stacks CSS de la fuente propia. Lo que falte (títulos o texto) usa la otra, y después la del sistema. */
function feelolab_own_font_stacks(): array {
	$own    = feelolab_own_fonts();
	$system = 'serif' === $own['generic'] ? 'Charter, "Bitstream Charter", "Sitka Text", Cambria, serif' : 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
	$body   = $own['body'] ? '"FeeloLab Texto", ' : ( $own['heading'] ? '"FeeloLab Titulos", ' : '' );
	$head   = $own['heading'] ? '"FeeloLab Titulos", ' : $body;
	return array(
		'body'    => $body . $system,
		'heading' => $head . $system,
	);
}

/** @font-face de la fuente propia. */
function feelolab_own_font_face_css(): string {
	if ( ! feelolab_own_fonts_active() ) {
		return '';
	}
	$own  = feelolab_own_fonts();
	$face = static fn( string $family, string $url, string $weight ) => sprintf( '@font-face{font-family:"%1$s";src:url(%2$s) format("%3$s");font-weight:%4$s;font-style:normal;font-display:swap}', $family, esc_url( $url ), str_ends_with( strtolower( (string) wp_parse_url( $url, PHP_URL_PATH ) ), '.woff' ) ? 'woff' : 'woff2', $weight );
	$css  = '';
	if ( $own['heading'] ) {
		$css .= $face( 'FeeloLab Titulos', $own['heading'], '100 900' );
	}
	if ( $own['body'] ) {
		// Con archivo de negrita aparte, el normal cubre hasta 500 y la negrita desde 600.
		$css .= $face( 'FeeloLab Texto', $own['body'], $own['body_bold'] ? '100 500' : '100 900' );
		if ( $own['body_bold'] ) {
			$css .= $face( 'FeeloLab Texto', $own['body_bold'], '600 900' );
		}
	}
	return $css;
}

/** Archivos de la fuente propia a precargar (títulos y texto normal: los que se ven primero). */
function feelolab_own_font_preloads(): array {
	if ( ! feelolab_own_fonts_active() ) {
		return array();
	}
	$own = feelolab_own_fonts();
	return array_values( array_unique( array_filter( array( $own['body'], $own['heading'] ) ) ) );
}

add_action(
	'wp_head',
	static function (): void {
		foreach ( feelolab_own_font_preloads() as $url ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/%s" crossorigin>' . "\n", esc_url( $url ), str_ends_with( strtolower( (string) wp_parse_url( $url, PHP_URL_PATH ) ), '.woff' ) ? 'woff' : 'woff2' );
		}
	},
	1
);

/**
 * WordPress no deja subir fuentes a la Biblioteca de medios: se habilitan .woff2 y .woff solo para
 * quien puede editar el diseño del sitio.
 */
add_filter(
	'upload_mimes',
	static function ( array $mimes ): array {
		if ( current_user_can( 'edit_theme_options' ) ) {
			$mimes['woff2'] = 'font/woff2';
			$mimes['woff']  = 'font/woff';
		}
		return $mimes;
	}
);

/** La detección de tipo de PHP suele ver un woff2 como "octet-stream": se confirma por la firma del archivo. */
add_filter(
	'wp_check_filetype_and_ext',
	static function ( $data, $file, $filename ) {
		$ext = strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'woff2', 'woff' ), true ) || ! current_user_can( 'edit_theme_options' ) ) {
			return $data;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- 4 bytes de un archivo local recién subido.
		$magic = (string) file_get_contents( $file, false, null, 0, 4 );
		if ( ( 'woff2' === $ext && 'wOF2' === $magic ) || ( 'woff' === $ext && 'wOFF' === $magic ) ) {
			return array(
				'ext'             => $ext,
				'type'            => 'font/' . $ext,
				'proper_filename' => $data['proper_filename'] ?? false,
			);
		}
		return $data;
	},
	10,
	3
);
