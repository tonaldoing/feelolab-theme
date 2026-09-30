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
		add_filter( 'upload_mimes', array( self::class, 'font_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( self::class, 'font_filetype' ), 10, 3 );
	}

	/**
	 * Fuente propia de la marca (Personalizar → Tipografía): WordPress no deja subir fuentes a la
	 * Biblioteca. Se habilitan .woff2 y .woff solo para quien puede editar el diseño del sitio.
	 *
	 * @param array<string, string> $mimes Tipos permitidos.
	 * @return array<string, string>
	 */
	public static function font_mimes( array $mimes ): array {
		if ( current_user_can( 'edit_theme_options' ) ) {
			$mimes['woff2'] = 'font/woff2';
			$mimes['woff']  = 'font/woff';
		}
		return $mimes;
	}

	/**
	 * PHP suele ver un woff2 como "octet-stream": se confirma por la firma del archivo.
	 *
	 * @param array<string, mixed> $data     Resultado de WordPress.
	 * @param string               $file     Ruta temporal.
	 * @param string               $filename Nombre original.
	 * @return array<string, mixed>
	 */
	public static function font_filetype( $data, $file, $filename ) {
		$ext = strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'woff2', 'woff' ), true ) || ! current_user_can( 'edit_theme_options' ) ) {
			return $data;
		}
		return self::is_font( $ext, self::magic( (string) $file ) )
			? array(
				'ext'             => $ext,
				'type'            => 'font/' . $ext,
				'proper_filename' => $data['proper_filename'] ?? false,
			)
			: $data;
	}

	/** Primeros 4 bytes de un archivo local recién subido. */
	private static function magic( string $file ): string {
		$handle = is_readable( $file ) ? fopen( $file, 'rb' ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- 4 bytes del archivo temporal de la subida.
		if ( ! $handle ) {
			return '';
		}
		$magic = (string) fread( $handle, 4 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		return $magic;
	}

	/** ¿La firma coincide con la extensión? wOF2 = woff2, wOFF = woff. */
	public static function is_font( string $ext, string $magic ): bool {
		return ( 'woff2' === $ext && 'wOF2' === $magic ) || ( 'woff' === $ext && 'wOFF' === $magic );
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
