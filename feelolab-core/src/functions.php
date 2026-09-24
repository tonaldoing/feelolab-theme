<?php
/**
 * API pública del plugin para el tema. El tema llama a estas funciones SIEMPRE detrás de
 * function_exists(): sin el plugin activo, el sitio sigue andando.
 *
 * @package Feelo\Core
 */

use Feelo\Core\Modules\Registry;
use Feelo\Core\Settings\SiteSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Un valor de Ajustes del sitio.
 *
 * @param string $key     Clave (ver SiteSettings::tabs()).
 * @param mixed  $fallback Valor si está vacío.
 * @return mixed
 */
function feelo_setting( string $key, $fallback = '' ) {
	static $settings = null;
	if ( null === $settings ) {
		$settings = get_option( SiteSettings::OPTION, array() );
		$settings = is_array( $settings ) ? $settings : array();
	}
	return ( isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) ? $settings[ $key ] : $fallback;
}

function feelo_business_name(): string {
	return (string) feelo_setting( 'nombre_comercial', get_bloginfo( 'name' ) );
}

/** Post type de un módulo si está activo; null si no. */
function feelo_module_post_type( string $module ): ?string {
	return Registry::post_type( $module );
}

function feelo_module_enabled( string $module ): bool {
	return Registry::is_enabled( $module );
}

/**
 * Campo de un contenido: ACF si está activo, meta nativa si no.
 * Los campos se guardan con el nombre tal cual, así get_post_meta los lee igual.
 *
 * @param string   $name    Nombre del campo.
 * @param int|null $post_id Post; por defecto el actual.
 * @return mixed
 */
function feelo_field( string $name, ?int $post_id = null ) {
	$post_id = $post_id ?? get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	if ( function_exists( 'get_field' ) ) {
		return get_field( $name, $post_id );
	}
	return get_post_meta( $post_id, $name, true );
}

/** Solo dígitos y el + inicial, para tel: y wa.me. */
function feelo_phone_digits( string $phone ): string {
	$phone = trim( $phone );
	$plus  = str_starts_with( $phone, '+' ) ? '+' : '';
	return $plus . preg_replace( '/\D+/', '', $phone );
}

function feelo_tel_href( string $phone ): string {
	return 'tel:' . feelo_phone_digits( $phone );
}

function feelo_whatsapp_url( ?string $message = null ): string {
	$number = ltrim( feelo_phone_digits( (string) feelo_setting( 'whatsapp' ) ), '+' );
	if ( '' === $number ) {
		return '';
	}
	$message = $message ?? (string) feelo_setting( 'whatsapp_mensaje' );
	$url     = 'https://wa.me/' . $number;
	return '' !== $message ? add_query_arg( 'text', rawurlencode( $message ), $url ) : $url;
}

/** @return array<string, array{label: string, url: string}> Redes cargadas, en orden fijo. */
function feelo_socials(): array {
	$labels = array(
		'instagram' => 'Instagram',
		'facebook'  => 'Facebook',
		'linkedin'  => 'LinkedIn',
		'tiktok'    => 'TikTok',
		'youtube'   => 'YouTube',
		'x'         => 'X',
	);
	$out    = array();
	foreach ( $labels as $key => $label ) {
		$url = (string) feelo_setting( $key );
		if ( '' !== $url ) {
			$out[ $key ] = array(
				'label' => $label,
				'url'   => $url,
			);
		}
	}
	return $out;
}

/**
 * Horarios parseados: "Lu-Vi 09:00-18:00" → días schema.org + apertura + cierre.
 * Las líneas que no respetan el formato se muestran igual pero no van al schema.
 *
 * @return array<int, array{text: string, days: string[], opens: string, closes: string}>
 */
function feelo_opening_hours( ?string $raw = null ): array {
	$raw  = $raw ?? (string) feelo_setting( 'horarios' );
	$map  = array(
		'lu' => 'Monday',
		'ma' => 'Tuesday',
		'mi' => 'Wednesday',
		'ju' => 'Thursday',
		'vi' => 'Friday',
		'sa' => 'Saturday',
		'do' => 'Sunday',
	);
	$keys = array_keys( $map );
	$out  = array();

	foreach ( preg_split( '/\R/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$row = array(
			'text'   => $line,
			'days'   => array(),
			'opens'  => '',
			'closes' => '',
		);
		if ( preg_match( '/^([a-zá]{2})[a-zá]*\.?(?:\s*-\s*([a-zá]{2})[a-zá]*\.?)?\s+(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})$/iu', $line, $m ) ) {
			$from = array_search( mb_strtolower( $m[1] ), $keys, true );
			$to   = ! empty( $m[2] ) ? array_search( mb_strtolower( $m[2] ), $keys, true ) : $from;
			if ( false !== $from && false !== $to && $to >= $from ) {
				for ( $i = $from; $i <= $to; $i++ ) {
					$row['days'][] = $map[ $keys[ $i ] ];
				}
				$row['opens']  = str_pad( $m[3], 5, '0', STR_PAD_LEFT );
				$row['closes'] = str_pad( $m[4], 5, '0', STR_PAD_LEFT );
			}
		}
		$out[] = $row;
	}
	return $out;
}

/**
 * Resumen en texto plano de un contenido: el extracto si lo cargaron, si no las primeras palabras.
 * A diferencia de get_the_excerpt(), deja un espacio entre bloques ("fiscal. Ganancias" y no
 * "fiscal.Ganancias"): lo usan la meta description y /llms.txt.
 */
function feelo_plain_summary( \WP_Post $post, int $words = 28 ): string {
	if ( has_excerpt( $post ) ) {
		return trim( wp_strip_all_tags( $post->post_excerpt ) );
	}
	$html = preg_replace( '/<\/(p|h[1-6]|li|div|blockquote|figcaption)>|<br\s*\/?>/i', ' ', strip_shortcodes( $post->post_content ) );
	return wp_trim_words( wp_strip_all_tags( excerpt_remove_blocks( $html ) ), $words, '…' );
}

/** ¿Hay un plugin de SEO que ya se ocupa de meta y schema? Entonces no duplicamos. */
function feelo_seo_plugin_active(): bool {
	$active = defined( 'WPSEO_VERSION' )           // Yoast.
		|| defined( 'RANK_MATH_VERSION' )          // Rank Math.
		|| defined( 'AIOSEO_VERSION' )             // All in One SEO.
		|| defined( 'SEOPRESS_VERSION' )           // SEOPress.
		|| class_exists( 'The_SEO_Framework\\Load' );
	return (bool) apply_filters( 'feelo_seo_plugin_active', $active );
}

/**
 * IDs de la galería de un contenido (sin la imagen destacada).
 *
 * @return int[]
 */
function feelo_gallery_ids( ?int $post_id = null ): array {
	$post_id = $post_id ?? get_the_ID();
	return $post_id ? Feelo\Core\Gallery::ids( (int) $post_id ) : array();
}

/**
 * Formulario de contacto. También disponible como shortcode [feelo_formulario].
 *
 * @param array<string, mixed> $args Ver Feelo\Core\Forms\ContactForm::render().
 */
function feelo_contact_form( array $args = array() ): string {
	return Feelo\Core\Forms\ContactForm::render( $args );
}
