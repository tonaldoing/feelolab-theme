<?php
/**
 * Campos de cada módulo, en PHP y versionados. Solo tipos de ACF free
 * (nada de repeater, gallery, flexible content ni options page, que son Pro).
 * Lo que en Pro sería un repeater acá es una relación entre contenidos.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core\Fields;

use Feelo\Core\Modules\Registry;

defined( 'ABSPATH' ) || exit;

final class FieldGroups {

	public static function register(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		foreach ( self::groups() as $module => $fields ) {
			$post_type = Registry::post_type( $module );
			if ( ! $post_type ) {
				continue;
			}
			acf_add_local_field_group(
				array(
					'key'      => 'group_feelo_' . $module,
					'title'    => __( 'Datos', 'feelolab-core' ),
					'fields'   => self::prefix_keys( $module, $fields ),
					'location' => array(
						array(
							array(
								'param'    => 'post_type',
								'operator' => '==',
								'value'    => $post_type,
							),
						),
					),
					'position' => 'acf_after_title',
					'style'    => 'seamless',
				)
			);
		}
	}

	/**
	 * @param string                           $module Módulo.
	 * @param array<int, array<string, mixed>> $fields Campos sin key.
	 * @return array<int, array<string, mixed>>
	 */
	private static function prefix_keys( string $module, array $fields ): array {
		foreach ( $fields as &$field ) {
			$field['key'] = 'field_feelo_' . $module . '_' . $field['name'];
		}
		return $fields;
	}

	/** @return array<string, array<int, array<string, mixed>>> */
	private static function groups(): array {
		$faq_type = Registry::post_type( 'faq' );

		$servicios = array(
			array(
				'name'         => 'bajada',
				'label'        => __( 'Bajada', 'feelolab-core' ),
				'type'         => 'textarea',
				'rows'         => 2,
				'instructions' => __( 'Una frase que resume el servicio. Aparece en las tarjetas.', 'feelolab-core' ),
			),
			array(
				'name'          => 'icono',
				'label'         => __( 'Ícono', 'feelolab-core' ),
				'type'          => 'image',
				'return_format' => 'id',
				'preview_size'  => 'thumbnail',
				'mime_types'    => 'svg,png,webp',
				'instructions'  => __( 'Opcional. SVG o PNG cuadrado.', 'feelolab-core' ),
			),
			array(
				'name'         => 'precio_desde',
				'label'        => __( 'Precio desde', 'feelolab-core' ),
				'type'         => 'text',
				'instructions' => __( 'Opcional, como se muestra: "$ 25.000".', 'feelolab-core' ),
			),
			array(
				'name'          => 'cta',
				'label'         => __( 'Botón', 'feelolab-core' ),
				'type'          => 'link',
				'return_format' => 'array',
				'instructions'  => __( 'Si queda vacío, el botón lleva a WhatsApp o a contacto.', 'feelolab-core' ),
			),
		);
		if ( $faq_type ) {
			$servicios[] = array(
				'name'          => 'faqs',
				'label'         => __( 'Preguntas frecuentes de este servicio', 'feelolab-core' ),
				'type'          => 'relationship',
				'post_type'     => array( $faq_type ),
				'filters'       => array( 'search' ),
				'return_format' => 'id',
			);
		}

		return array(
			'servicios'   => $servicios,
			'productos'   => array(
				array(
					'name'  => 'precio',
					'label' => __( 'Precio', 'feelolab-core' ),
					'type'  => 'text',
				),
				array(
					'name'         => 'precio_oferta',
					'label'        => __( 'Precio de oferta', 'feelolab-core' ),
					'type'         => 'text',
					'instructions' => __( 'Si está cargado, el precio normal se muestra tachado.', 'feelolab-core' ),
				),
				array(
					'name'  => 'sku',
					'label' => __( 'Código / SKU', 'feelolab-core' ),
					'type'  => 'text',
				),
				array(
					'name'          => 'disponible',
					'label'         => __( 'Disponible', 'feelolab-core' ),
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				),
				array(
					'name'         => 'ficha_tecnica',
					'label'        => __( 'Ficha técnica', 'feelolab-core' ),
					'type'         => 'textarea',
					'instructions' => __( 'Una característica por línea, formato "Material: Acero". Se muestra como tabla.', 'feelolab-core' ),
				),
				array(
					'name'          => 'consulta_whatsapp',
					'label'         => __( 'Botón "Consultar por WhatsApp"', 'feelolab-core' ),
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				),
			),
			'proyectos'   => array(
				array(
					'name'  => 'cliente',
					'label' => __( 'Cliente', 'feelolab-core' ),
					'type'  => 'text',
				),
				array(
					'name'  => 'anio',
					'label' => __( 'Año', 'feelolab-core' ),
					'type'  => 'number',
					'min'   => 1900,
					'max'   => 2100,
				),
				array(
					'name'  => 'url',
					'label' => __( 'Link al proyecto', 'feelolab-core' ),
					'type'  => 'url',
				),
				array(
					'name'         => 'resultado',
					'label'        => __( 'Resultado', 'feelolab-core' ),
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => __( 'El logro en una frase, con un número si lo hay.', 'feelolab-core' ),
				),
			),
			'equipo'      => array(
				array(
					'name'  => 'cargo',
					'label' => __( 'Cargo', 'feelolab-core' ),
					'type'  => 'text',
				),
				array(
					'name'  => 'email',
					'label' => __( 'Email', 'feelolab-core' ),
					'type'  => 'email',
				),
				array(
					'name'  => 'linkedin',
					'label' => 'LinkedIn',
					'type'  => 'url',
				),
				array(
					'name'  => 'instagram',
					'label' => 'Instagram',
					'type'  => 'url',
				),
			),
			'testimonios' => array(
				array(
					'name'         => 'autor',
					'label'        => __( 'Nombre', 'feelolab-core' ),
					'type'         => 'text',
					'instructions' => __( 'El título del testimonio es interno; esto es lo que se muestra.', 'feelolab-core' ),
				),
				array(
					'name'  => 'cargo',
					'label' => __( 'Cargo y empresa', 'feelolab-core' ),
					'type'  => 'text',
				),
				array(
					'name'          => 'estrellas',
					'label'         => __( 'Calificación', 'feelolab-core' ),
					'type'          => 'number',
					'min'           => 1,
					'max'           => 5,
					'default_value' => 5,
				),
			),
			'sedes'       => array(
				array(
					'name'  => 'direccion',
					'label' => __( 'Dirección', 'feelolab-core' ),
					'type'  => 'text',
				),
				array(
					'name'  => 'ciudad',
					'label' => __( 'Ciudad', 'feelolab-core' ),
					'type'  => 'text',
				),
				array(
					'name'  => 'telefono',
					'label' => __( 'Teléfono', 'feelolab-core' ),
					'type'  => 'text',
				),
				array(
					'name'  => 'email',
					'label' => __( 'Email', 'feelolab-core' ),
					'type'  => 'email',
				),
				array(
					'name'         => 'horarios',
					'label'        => __( 'Horarios', 'feelolab-core' ),
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => __( 'Mismo formato que en Ajustes del sitio: "Lu-Vi 09:00-18:00".', 'feelolab-core' ),
				),
				array(
					'name'  => 'mapa_url',
					'label' => __( 'Link a Google Maps', 'feelolab-core' ),
					'type'  => 'url',
				),
			),
			'clientes'    => array(
				array(
					'name'  => 'url',
					'label' => __( 'Sitio web', 'feelolab-core' ),
					'type'  => 'url',
				),
			),
		);
	}
}
