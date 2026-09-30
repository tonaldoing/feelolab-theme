<?php
/**
 * Datos personales de los mensajes y suscripciones que guarda el sitio.
 *
 * - Exportar y borrar: se integra con Herramientas → Exportar / Borrar datos personales de
 *   WordPress. Busca por email en Mensajes (contacto y newsletter).
 * - Borrado automático: los mensajes con más antigüedad que la elegida en Ajustes del sitio →
 *   Formulario se borran solos, una vez por día, de a tandas (no guardar datos de más).
 * - Texto sugerido para la política de privacidad (Ajustes → Privacidad → guía), armado con lo
 *   que el sitio realmente usa: formulario, newsletter, Analytics, WhatsApp y mapa.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

defined( 'ABSPATH' ) || exit;

final class Privacy {

	public const CRON = 'feelo_privacy_cleanup';

	private const TYPE  = 'feelo_mensaje';
	private const BATCH = 50;

	public static function init(): void {
		add_filter( 'wp_privacy_personal_data_exporters', array( self::class, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( self::class, 'register_eraser' ) );
		add_action( 'admin_init', array( self::class, 'policy_text' ) );
		add_action( self::CRON, array( self::class, 'cleanup' ) );
		add_action( 'init', array( self::class, 'schedule' ) );
	}

	/** @param array<string, array<string, mixed>> $exporters */
	public static function register_exporter( array $exporters ): array {
		$exporters['feelolab-core'] = array(
			'exporter_friendly_name' => __( 'Mensajes y suscripciones (FeeloLab)', 'feelolab-core' ),
			'callback'               => array( self::class, 'export' ),
		);
		return $exporters;
	}

	/** @param array<string, array<string, mixed>> $erasers */
	public static function register_eraser( array $erasers ): array {
		$erasers['feelolab-core'] = array(
			'eraser_friendly_name' => __( 'Mensajes y suscripciones (FeeloLab)', 'feelolab-core' ),
			'callback'             => array( self::class, 'erase' ),
		);
		return $erasers;
	}

	/** @return int[] Mensajes de esa persona (por email), de a tandas. */
	private static function messages_for( string $email, int $page ): array {
		return get_posts(
			array(
				'post_type'      => self::TYPE,
				'post_status'    => 'any',
				'posts_per_page' => self::BATCH,
				'paged'          => $page,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'meta_key'       => '_feelo_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- herramienta de privacidad, a pedido.
				'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
	}

	/** @return array{data: array<int, array<string, mixed>>, done: bool} */
	public static function export( string $email, int $page = 1 ): array {
		$ids   = self::messages_for( $email, $page );
		$items = array();
		$map   = array(
			'_feelo_email'    => __( 'Email', 'feelolab-core' ),
			'_feelo_telefono' => __( 'Teléfono', 'feelolab-core' ),
			'_feelo_mensaje'  => __( 'Mensaje', 'feelolab-core' ),
			'_feelo_servicio' => __( 'Interés', 'feelolab-core' ),
			'_feelo_origen'   => __( 'Enviado desde', 'feelolab-core' ),
		);
		foreach ( $ids as $id ) {
			$data = array(
				array(
					'name'  => __( 'Fecha', 'feelolab-core' ),
					'value' => get_the_date( 'Y-m-d H:i', $id ),
				),
				array(
					'name'  => __( 'Asunto', 'feelolab-core' ),
					'value' => get_the_title( $id ),
				),
			);
			foreach ( $map as $key => $label ) {
				$value = (string) get_post_meta( $id, $key, true );
				if ( '' !== $value ) {
					$data[] = array(
						'name'  => $label,
						'value' => $value,
					);
				}
			}
			$items[] = array(
				'group_id'    => 'feelo-mensajes',
				'group_label' => __( 'Mensajes enviados al sitio', 'feelolab-core' ),
				'item_id'     => 'feelo-mensaje-' . $id,
				'data'        => $data,
			);
		}
		return array(
			'data' => $items,
			'done' => count( $ids ) < self::BATCH,
		);
	}

	/** @return array{items_removed: bool, items_retained: bool, messages: string[], done: bool} */
	public static function erase( string $email ): array {
		// Siempre la primera página: lo borrado ya no aparece en la siguiente consulta.
		$ids = self::messages_for( $email, 1 );
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
		return array(
			'items_removed'  => (bool) $ids,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => count( $ids ) < self::BATCH,
		);
	}

	/** Meses que se guardan los mensajes (0 = para siempre). */
	public static function retention_months(): int {
		return absint( feelo_setting( 'mensajes_retencion' ) );
	}

	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::CRON ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON );
		}
	}

	/** Borra mensajes más viejos que la retención elegida. Hasta 5 tandas por día para no cargar el servidor. */
	public static function cleanup(): void {
		$months = self::retention_months();
		if ( ! $months ) {
			return;
		}
		for ( $i = 0; $i < 5; $i++ ) {
			$ids = get_posts(
				array(
					'post_type'      => self::TYPE,
					'post_status'    => 'any',
					'posts_per_page' => self::BATCH,
					'fields'         => 'ids',
					'date_query'     => array(
						array(
							'before' => $months . ' months ago',
						),
					),
				)
			);
			foreach ( $ids as $id ) {
				wp_delete_post( $id, true );
			}
			if ( count( $ids ) < self::BATCH ) {
				return;
			}
		}
	}

	/** Texto sugerido para la política de privacidad, según lo que el sitio usa. */
	public static function policy_text(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		$name  = feelo_business_name();
		$parts = array();

		$parts[] = '<h2>' . esc_html__( 'Formulario de contacto', 'feelolab-core' ) . '</h2><p>' . esc_html(
			sprintf(
				/* translators: %s: nombre del negocio */
				__( 'Cuando nos escribís por el formulario guardamos tu nombre, email, teléfono (si lo dejás) y el mensaje, para poder responderte. Solo los ve %s y no los compartimos con terceros.', 'feelolab-core' ),
				$name
			)
		) . '</p>';
		$months  = self::retention_months();
		$parts[] = '<p>' . esc_html(
			$months
				/* translators: %d: meses */
				? sprintf( _n( 'Los mensajes se borran automáticamente a los %d mes.', 'Los mensajes se borran automáticamente a los %d meses.', $months, 'feelolab-core' ), $months )
				: __( 'Los mensajes se guardan hasta que pidas que los borremos.', 'feelolab-core' )
		) . ' ' . esc_html__( 'Podés pedir una copia de tus datos o que los borremos escribiéndonos.', 'feelolab-core' ) . '</p>';

		$provider = (string) feelo_setting( 'news_proveedor' );
		$parts[]  = '<h2>' . esc_html__( 'Newsletter', 'feelolab-core' ) . '</h2><p>' . esc_html(
			$provider
				/* translators: %s: Brevo o Mailchimp */
				? sprintf( __( 'Si te suscribís, tu email se guarda en %s, el servicio que usamos para enviar el newsletter. Cada email trae un link para darte de baja.', 'feelolab-core' ), ucfirst( $provider ) )
				: __( 'Si te suscribís, guardamos tu email solo para enviarte el newsletter. Podés darte de baja cuando quieras.', 'feelolab-core' )
		) . '</p>';

		if ( feelo_setting( 'ga4_id' ) || feelo_setting( 'gtm_id' ) ) {
			$parts[] = '<h2>' . esc_html__( 'Cookies y medición', 'feelolab-core' ) . '</h2><p>' . esc_html__( 'Usamos Google Analytics para saber cuántas personas visitan el sitio y qué páginas les sirven. Si el aviso de cookies está activo, no se guardan cookies de medición hasta que las aceptes; podés cambiar tu elección desde "Preferencias de cookies" al pie del sitio.', 'feelolab-core' ) . '</p>';
		}
		if ( feelo_setting( 'whatsapp' ) ) {
			$parts[] = '<h2>' . esc_html__( 'WhatsApp', 'feelolab-core' ) . '</h2><p>' . esc_html__( 'Los botones de WhatsApp abren la aplicación de WhatsApp (Meta). Lo que escribas ahí queda sujeto a su política de privacidad.', 'feelolab-core' ) . '</p>';
		}
		$parts[] = '<h2>' . esc_html__( 'Mapas', 'feelolab-core' ) . '</h2><p>' . esc_html__( 'Los mapas del sitio son de Google Maps y se cargan solo si tocás "Ver mapa". Recién ahí Google recibe tu visita.', 'feelolab-core' ) . '</p>';

		wp_add_privacy_policy_content( 'FeeloLab', wp_kses_post( implode( '', $parts ) ) );
	}
}
