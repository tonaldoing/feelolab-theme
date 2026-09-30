<?php
/**
 * Imágenes en WebP: al subir una foto JPEG o PNG, WordPress guarda en WebP la imagen principal y
 * todos sus tamaños (lo que usa el sitio). Pesan entre 25 y 35 % menos con la misma calidad.
 * El archivo subido se conserva aparte (original_image), por si hace falta el original.
 *
 * Solo si el servidor sabe generar WebP; se apaga en Ajustes del sitio → Integraciones.
 * Afecta a las fotos que se suban desde ahora: las anteriores no se tocan.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

defined( 'ABSPATH' ) || exit;

final class Media {

	public static function init(): void {
		add_filter( 'image_editor_output_format', array( self::class, 'webp' ) );
	}

	public static function enabled(): bool {
		return ! feelo_setting( 'webp_off' ) && self::supported();
	}

	public static function supported(): bool {
		static $supported = null;
		if ( null === $supported ) {
			$supported = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
		}
		return $supported;
	}

	/**
	 * @param array<string, string> $formats Formato de entrada => formato de salida.
	 * @return array<string, string>
	 */
	public static function webp( array $formats ): array {
		if ( ! self::enabled() ) {
			return $formats;
		}
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
		return $formats;
	}
}
