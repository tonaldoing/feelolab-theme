<?php
/**
 * Admin: bandeja de mensajes y avisos.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

defined( 'ABSPATH' ) || exit;

final class Admin {

	public static function init(): void {
		add_action( 'add_meta_boxes_feelo_mensaje', array( self::class, 'message_box' ) );
		add_filter( 'manage_feelo_mensaje_posts_columns', array( self::class, 'message_columns' ) );
		add_action( 'manage_feelo_mensaje_posts_custom_column', array( self::class, 'message_column' ), 10, 2 );
		add_action( 'admin_notices', array( self::class, 'acf_notice' ) );
	}

	public static function message_box(): void {
		add_meta_box(
			'feelo-mensaje',
			__( 'Mensaje', 'feelolab-core' ),
			static function ( \WP_Post $post ): void {
				$rows = array(
					__( 'Email', 'feelolab-core' )         => get_post_meta( $post->ID, '_feelo_email', true ),
					__( 'Teléfono', 'feelolab-core' )      => get_post_meta( $post->ID, '_feelo_telefono', true ),
					__( 'Interés', 'feelolab-core' )       => get_post_meta( $post->ID, '_feelo_servicio', true ),
					__( 'Enviado desde', 'feelolab-core' ) => get_post_meta( $post->ID, '_feelo_origen', true ),
					__( 'Fecha', 'feelolab-core' )         => get_the_date( 'j/n/Y H:i', $post ),
				);
				echo '<table class="form-table" role="presentation"><tbody>';
				foreach ( $rows as $label => $value ) {
					if ( '' === (string) $value ) {
						continue;
					}
					echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . esc_html( (string) $value ) . '</td></tr>';
				}
				echo '</tbody></table>';
				echo '<h3>' . esc_html__( 'Mensaje', 'feelolab-core' ) . '</h3>';
				echo '<div style="white-space:pre-wrap;font-size:14px;line-height:1.6">' . esc_html( (string) get_post_meta( $post->ID, '_feelo_mensaje', true ) ) . '</div>';
				if ( get_post_meta( $post->ID, '_feelo_mail_fallo', true ) ) {
					echo '<p class="notice notice-warning inline" style="padding:8px">' . esc_html__( 'El email de aviso no se pudo enviar. Revisá la configuración SMTP del sitio.', 'feelolab-core' ) . '</p>';
				}
				$email = (string) get_post_meta( $post->ID, '_feelo_email', true );
				if ( is_email( $email ) ) {
					printf( '<p><a class="button button-primary" href="%s">%s</a></p>', esc_url( 'mailto:' . $email ), esc_html__( 'Responder por email', 'feelolab-core' ) );
				}
			},
			'feelo_mensaje',
			'normal',
			'high'
		);
		remove_meta_box( 'submitdiv', 'feelo_mensaje', 'side' );
	}

	/**
	 * @param array<string, string> $columns Columnas.
	 * @return array<string, string>
	 */
	public static function message_columns( array $columns ): array {
		return array(
			'cb'             => $columns['cb'] ?? '',
			'title'          => __( 'De', 'feelolab-core' ),
			'feelo_servicio' => __( 'Interés', 'feelolab-core' ),
			'feelo_mail'     => __( 'Aviso por email', 'feelolab-core' ),
			'date'           => __( 'Fecha', 'feelolab-core' ),
		);
	}

	public static function message_column( string $column, int $post_id ): void {
		if ( 'feelo_servicio' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, '_feelo_servicio', true ) );
		} elseif ( 'feelo_mail' === $column ) {
			echo get_post_meta( $post_id, '_feelo_mail_fallo', true )
				? '<span style="color:#b32d2e">' . esc_html__( 'Falló', 'feelolab-core' ) . '</span>'
				: esc_html__( 'Enviado', 'feelolab-core' );
		}
	}

	public static function acf_notice(): void {
		if ( function_exists( 'acf_add_local_field_group' ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ( 'dashboard' !== $screen->id && ! str_starts_with( (string) $screen->post_type, 'feelo_' ) ) ) {
			return;
		}
		printf(
			'<div class="notice notice-info"><p>%s</p></div>',
			esc_html__( 'Feelolab Core: instalá Advanced Custom Fields (versión gratuita) o Secure Custom Fields para editar los campos extra de cada contenido (precio, cargo, horarios…). Sin eso, el sitio funciona pero esos datos no se pueden cargar.', 'feelolab-core' )
		);
	}
}
