<?php
/**
 * Personalizador: marca (colores, tipografía, forma), header, footer y home por secciones.
 * Logo e ícono del sitio usan los controles nativos de "Identidad del sitio".
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp_customize ): void {

		$wp_customize->add_panel(
			'feelolab_brand',
			array(
				'title'    => __( 'Marca', 'feelolab' ),
				'priority' => 25,
			)
		);

		/* ---------- Colores ---------- */
		$wp_customize->add_section(
			'feelolab_colors',
			array(
				'title'       => __( 'Colores', 'feelolab' ),
				'panel'       => 'feelolab_brand',
				'description' => __( 'El tema ajusta solo el color de texto sobre botones y el tono de los links para que siempre se lean bien (contraste WCAG AA). Si ves un aviso, el color se va a corregir automáticamente en el sitio.', 'feelolab' ),
			)
		);
		foreach ( feelolab_color_settings() as $key => $color ) {
			$wp_customize->add_setting(
				'feelolab_color_' . $key,
				array(
					'default'           => $color['default'],
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => 'refresh',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'feelolab_color_' . $key,
					array(
						'label'   => $color['label'],
						'section' => 'feelolab_colors',
					)
				)
			);
		}

		/* ---------- Tipografía y forma ---------- */
		$wp_customize->add_section(
			'feelolab_type',
			array(
				'title' => __( 'Tipografía y forma', 'feelolab' ),
				'panel' => 'feelolab_brand',
			)
		);
		$choices = wp_list_pluck( feelolab_font_stacks(), 'label' );
		feelolab_customizer_select( $wp_customize, 'feelolab_font_pair', __( 'Combinación tipográfica', 'feelolab' ), 'feelolab_type', $choices, 'sistema', __( 'Fuentes del sistema: no se descarga nada y el texto aparece al instante.', 'feelolab' ) );
		feelolab_customizer_select(
			$wp_customize,
			'feelolab_font_size',
			__( 'Tamaño de texto base', 'feelolab' ),
			'feelolab_type',
			array(
				'16' => '16 px',
				'17' => __( '17 px (recomendado)', 'feelolab' ),
				'18' => '18 px',
			),
			'17'
		);
		feelolab_customizer_select(
			$wp_customize,
			'feelolab_radius',
			__( 'Esquinas', 'feelolab' ),
			'feelolab_type',
			array(
				'0'  => __( 'Rectas', 'feelolab' ),
				'4'  => __( 'Apenas redondeadas', 'feelolab' ),
				'8'  => __( 'Redondeadas', 'feelolab' ),
				'16' => __( 'Muy redondeadas', 'feelolab' ),
			),
			'8'
		);
		feelolab_customizer_select(
			$wp_customize,
			'feelolab_container',
			__( 'Ancho máximo del contenido', 'feelolab' ),
			'feelolab_type',
			array(
				'1120' => '1120 px',
				'1200' => '1200 px',
				'1320' => '1320 px',
			),
			'1200'
		);

		/* ---------- Header ---------- */
		$wp_customize->add_section(
			'feelolab_header',
			array(
				'title' => __( 'Encabezado', 'feelolab' ),
				'panel' => 'feelolab_brand',
			)
		);
		feelolab_customizer_checkbox( $wp_customize, 'feelolab_header_sticky', __( 'Encabezado fijo al hacer scroll', 'feelolab' ), 'feelolab_header', true );
		feelolab_customizer_text( $wp_customize, 'feelolab_header_cta_text', __( 'Botón del encabezado: texto', 'feelolab' ), 'feelolab_header', '' );
		feelolab_customizer_text( $wp_customize, 'feelolab_header_cta_url', __( 'Botón del encabezado: link', 'feelolab' ), 'feelolab_header', '', 'url' );

		/* ---------- Footer ---------- */
		$wp_customize->add_section(
			'feelolab_footer',
			array(
				'title' => __( 'Pie de página', 'feelolab' ),
				'panel' => 'feelolab_brand',
			)
		);
		feelolab_customizer_text( $wp_customize, 'feelolab_footer_text', __( 'Texto breve bajo el logo', 'feelolab' ), 'feelolab_footer', '', 'textarea' );
		feelolab_customizer_checkbox( $wp_customize, 'feelolab_footer_credit', __( 'Mostrar "Sitio hecho por Feelo"', 'feelolab' ), 'feelolab_footer', true );

		/* ---------- Home ---------- */
		$wp_customize->add_panel(
			'feelolab_home',
			array(
				'title'           => __( 'Secciones de la home', 'feelolab' ),
				'priority'        => 26,
				'description'     => __( 'Prendé, ordená y completá cada sección. El orden es un número: menor va primero.', 'feelolab' ),
				'active_callback' => 'is_front_page',
			)
		);
		foreach ( feelolab_home_sections() as $key => $section ) {
			if ( ! feelolab_home_section_available( $section ) ) {
				continue;
			}
			$section_id = 'feelolab_home_' . $key;
			$wp_customize->add_section(
				$section_id,
				array(
					'title' => $section['label'],
					'panel' => 'feelolab_home',
				)
			);
			feelolab_customizer_checkbox( $wp_customize, "feelolab_home_{$key}_show", __( 'Mostrar esta sección', 'feelolab' ), $section_id, $section['show'] );
			feelolab_customizer_text( $wp_customize, "feelolab_home_{$key}_order", __( 'Orden', 'feelolab' ), $section_id, $section['order'], 'number' );

			foreach ( $section['fields'] as $field => $def ) {
				$id = "feelolab_home_{$key}_{$field}";
				if ( 'image' === $def['type'] ) {
					$wp_customize->add_setting(
						$id,
						array(
							'default'           => 0,
							'sanitize_callback' => 'absint',
						)
					);
					$wp_customize->add_control(
						new WP_Customize_Media_Control(
							$wp_customize,
							$id,
							array(
								'label'     => $def['label'],
								'section'   => $section_id,
								'mime_type' => 'image',
							)
						)
					);
					continue;
				}
				feelolab_customizer_text( $wp_customize, $id, $def['label'], $section_id, $def['default'] ?? '', $def['type'] );
			}
		}
	}
);

/** Aviso de contraste en vivo mientras se eligen colores. */
add_action(
	'customize_controls_enqueue_scripts',
	static function (): void {
		wp_enqueue_script( 'feelolab-customizer-controls', FEELOLAB_URI . '/assets/js/customizer-controls.js', array( 'customize-controls' ), FEELOLAB_VERSION, true );
		wp_localize_script(
			'feelolab-customizer-controls',
			'feelolabContrast',
			array(
				/* translators: %s: relación de contraste, por ejemplo 3.2 */
				'lowPrimary' => __( 'Poco contraste con el fondo (%s:1, mínimo 4.5:1). En links y textos el tema va a usar una versión más oscura de este color.', 'feelolab' ),
				/* translators: %s: relación de contraste, por ejemplo 3.2 */
				'lowText'    => __( 'El texto tiene poco contraste con el fondo (%s:1). El tema lo va a oscurecer para que se lea.', 'feelolab' ),
			)
		);
	}
);

/* ---------- Helpers ---------- */

/**
 * @param array<string, string> $choices Opciones.
 */
function feelolab_customizer_select( WP_Customize_Manager $wp_customize, string $id, string $label, string $section, array $choices, string $fallback, string $description = '' ): void {
	$wp_customize->add_setting(
		$id,
		array(
			'default'           => $fallback,
			'sanitize_callback' => static fn( $value ) => array_key_exists( (string) $value, $choices ) ? (string) $value : $fallback,
		)
	);
	$wp_customize->add_control(
		$id,
		array(
			'label'       => $label,
			'description' => $description,
			'section'     => $section,
			'type'        => 'select',
			'choices'     => $choices,
		)
	);
}

function feelolab_customizer_checkbox( WP_Customize_Manager $wp_customize, string $id, string $label, string $section, bool $fallback ): void {
	$wp_customize->add_setting(
		$id,
		array(
			'default'           => $fallback,
			'sanitize_callback' => static fn( $value ) => (bool) $value,
		)
	);
	$wp_customize->add_control(
		$id,
		array(
			'label'   => $label,
			'section' => $section,
			'type'    => 'checkbox',
		)
	);
}

/**
 * @param mixed $fallback Valor si está vacío.
 */
function feelolab_customizer_text( WP_Customize_Manager $wp_customize, string $id, string $label, string $section, $fallback, string $type = 'text' ): void {
	$sanitize = array(
		'url'      => static fn( $v ) => ( str_starts_with( (string) $v, '#' ) ? sanitize_text_field( $v ) : esc_url_raw( $v ) ),
		'number'   => 'absint',
		'textarea' => 'sanitize_textarea_field',
	);
	$wp_customize->add_setting(
		$id,
		array(
			'default'           => $fallback,
			'sanitize_callback' => $sanitize[ $type ] ?? 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		$id,
		array(
			'label'   => $label,
			'section' => $section,
			'type'    => 'url' === $type ? 'text' : $type,
		)
	);
}
