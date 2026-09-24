<?php
/**
 * /llms.txt: resumen del sitio en markdown para asistentes y buscadores con IA (llmstxt.org).
 *
 * Se arma solo con lo cargado: negocio, contacto, servicios, productos, proyectos, sedes,
 * preguntas frecuentes, páginas y últimas notas. Nada inventado.
 * Cacheado en un transient que se borra al guardar contenido o ajustes.
 * Si existe un llms.txt físico (lo genera Yoast u otro), manda ese y este no se usa.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

use Feelo\Core\Settings\SiteSettings;

defined( 'ABSPATH' ) || exit;

final class LlmsTxt {

	private const CACHE = 'feelo_llms_txt';
	private const LIMIT = 50;

	public static function init(): void {
		add_action( 'parse_request', array( self::class, 'maybe_serve' ), 0 );
		add_action( 'save_post', array( self::class, 'flush' ) );
		add_action( 'deleted_post', array( self::class, 'flush' ) );
		add_action( 'update_option_' . SiteSettings::OPTION, array( self::class, 'flush' ) );
		add_action( 'update_option_blogdescription', array( self::class, 'flush' ) );
	}

	public static function enabled(): bool {
		return (bool) apply_filters( 'feelo_llms_txt_enabled', ! feelo_setting( 'llms_txt_off' ) && ! file_exists( ABSPATH . 'llms.txt' ) );
	}

	public static function flush(): void {
		delete_transient( self::CACHE );
	}

	public static function maybe_serve( \WP $wp ): void {
		$path = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );
		$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		if ( 'llms.txt' !== $wp->request && untrailingslashit( (string) $home ) . '/llms.txt' !== $path ) {
			return;
		}
		if ( ! self::enabled() ) {
			return;
		}
		$body = get_transient( self::CACHE );
		if ( ! is_string( $body ) ) {
			$body = self::build();
			set_transient( self::CACHE, $body, DAY_IN_SECONDS );
		}
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' ); // Es para máquinas: que no compita con las páginas en Google.
		header( 'Cache-Control: public, max-age=3600' );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- texto plano, cada dato pasa por clean().
		exit;
	}

	public static function build(): string {
		$lines   = array();
		$lines[] = '# ' . self::clean( feelo_business_name() );
		$lines[] = '';
		$desc    = (string) feelo_setting( 'descripcion', get_bloginfo( 'description' ) );
		if ( $desc ) {
			$lines[] = '> ' . self::clean( $desc );
			$lines[] = '';
		}

		$contact = array_filter(
			array(
				__( 'Teléfono', 'feelolab-core' )  => (string) feelo_setting( 'telefono' ),
				'WhatsApp'                         => (string) feelo_setting( 'whatsapp' ),
				__( 'Email', 'feelolab-core' )     => (string) feelo_setting( 'email' ),
				__( 'Dirección', 'feelolab-core' ) => implode( ', ', array_filter( array( feelo_setting( 'direccion' ), feelo_setting( 'ciudad' ), feelo_setting( 'provincia' ), feelo_setting( 'pais' ) ) ) ),
				__( 'Horarios', 'feelolab-core' )  => implode( '; ', wp_list_pluck( feelo_opening_hours(), 'text' ) ),
			)
		);
		foreach ( $contact as $label => $value ) {
			$lines[] = '- ' . $label . ': ' . self::clean( $value );
		}
		foreach ( feelo_socials() as $social ) {
			$lines[] = '- ' . $social['label'] . ': ' . esc_url_raw( $social['url'] );
		}
		if ( $contact || feelo_socials() ) {
			$lines[] = '';
		}

		$sections = array(
			'servicios' => array( __( 'Servicios', 'feelolab-core' ), 'bajada' ),
			'productos' => array( __( 'Productos', 'feelolab-core' ), 'precio' ),
			'proyectos' => array( __( 'Proyectos', 'feelolab-core' ), 'resultado' ),
			'sedes'     => array( __( 'Sedes', 'feelolab-core' ), 'direccion' ),
			'equipo'    => array( __( 'Equipo', 'feelolab-core' ), 'cargo' ),
		);
		foreach ( $sections as $module => $section ) {
			$post_type = feelo_module_post_type( $module );
			if ( ! $post_type ) {
				continue;
			}
			$items = self::posts( $post_type );
			if ( ! $items ) {
				continue;
			}
			$lines[] = '## ' . $section[0];
			$lines[] = '';
			foreach ( $items as $post ) {
				$note    = (string) feelo_field( $section[1], $post->ID );
				$note    = $note ? $note : feelo_plain_summary( $post );
				$lines[] = self::link( $post, $note );
			}
			$lines[] = '';
		}

		$faq_type = feelo_module_post_type( 'faq' );
		$faqs     = $faq_type ? self::posts( $faq_type ) : array();
		if ( $faqs ) {
			$lines[] = '## ' . __( 'Preguntas frecuentes', 'feelolab-core' );
			$lines[] = '';
			foreach ( $faqs as $faq ) {
				$lines[] = '- ' . self::clean( get_the_title( $faq ) ) . ' ' . self::clean( wp_strip_all_tags( $faq->post_content ) );
			}
			$lines[] = '';
		}

		$pages = get_pages(
			array(
				'parent'      => 0,
				'sort_column' => 'menu_order,post_title',
				'number'      => 20,
				'exclude'     => array_filter( array( (int) get_option( 'page_on_front' ) ) ),
			)
		);
		if ( $pages ) {
			$lines[] = '## ' . __( 'Páginas', 'feelolab-core' );
			$lines[] = '';
			foreach ( $pages as $page ) {
				$lines[] = self::link( $page, '' );
			}
			$lines[] = '';
		}

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 10,
				'no_found_rows'  => true,
			)
		);
		if ( $posts ) {
			$lines[] = '## ' . __( 'Blog', 'feelolab-core' );
			$lines[] = '';
			foreach ( $posts as $post ) {
				$lines[] = self::link( $post, feelo_plain_summary( $post ) );
			}
			$lines[] = '';
		}

		return (string) apply_filters( 'feelo_llms_txt', rtrim( implode( "\n", $lines ) ) . "\n" );
	}

	/** @return \WP_Post[] */
	private static function posts( string $post_type ): array {
		return get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => self::LIMIT,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'no_found_rows'  => true,
			)
		);
	}

	private static function link( \WP_Post $post, string $note ): string {
		$line = sprintf( '- [%s](%s)', self::clean( get_the_title( $post ) ), esc_url_raw( (string) get_permalink( $post ) ) );
		$note = self::clean( $note );
		return $note ? $line . ': ' . $note : $line;
	}

	/** Una línea de texto plano: sin HTML, sin saltos, sin corchetes que rompan el markdown. */
	private static function clean( string $text ): string {
		$text = html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES, 'UTF-8' );
		$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
		$text = str_replace( array( '[', ']' ), array( '(', ')' ), $text );
		return mb_strlen( $text ) > 300 ? mb_substr( $text, 0, 297 ) . '…' : $text;
	}
}
