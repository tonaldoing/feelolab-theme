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
				'description' => __( 'Botones, texto de los botones y links se eligen por separado. Si una combinación no se lee bien (contraste WCAG AA), el tema la corrige sola y te avisa acá abajo.', 'feelolab' ),
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
						'label'       => $color['label'],
						'description' => $color['description'] ?? '',
						'section'     => 'feelolab_colors',
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
		feelolab_customizer_select( $wp_customize, 'feelolab_font_pair', __( 'Combinación tipográfica', 'feelolab' ), 'feelolab_type', $choices, 'sistema', __( 'Las de sistema no descargan nada. Las "Web" se sirven desde el propio sitio, con métricas ajustadas para que el texto no salte al cargar.', 'feelolab' ) );
		// Fuente propia: aparece al elegir "Fuente propia de la marca".
		$own_active = static fn() => 'propia' === get_theme_mod( 'feelolab_font_pair', 'sistema' );
		$own_files  = array(
			'feelolab_font_own_heading'   => array( __( 'Fuente propia: títulos (.woff2)', 'feelolab' ), __( 'Subí el archivo .woff2 (si tenés .ttf u .otf, convertilo gratis en transfonter.org). Revisá que la licencia permita usarla en la web.', 'feelolab' ) ),
			'feelolab_font_own_body'      => array( __( 'Fuente propia: textos (.woff2)', 'feelolab' ), __( 'Opcional: si no la cargás, los textos usan la de títulos.', 'feelolab' ) ),
			'feelolab_font_own_body_bold' => array( __( 'Fuente propia: textos en negrita (.woff2)', 'feelolab' ), __( 'Opcional. Si la fuente de textos es "variable" (trae todos los pesos), no hace falta.', 'feelolab' ) ),
		);
		// Subir fuentes a la Biblioteca lo habilita el plugin FeeloLab Core (o cualquier plugin de fuentes).
		if ( ! array_key_exists( 'woff2', get_allowed_mime_types() ) ) {
			$own_files['feelolab_font_own_heading'][1] .= ' ' . __( 'WordPress no deja subir fuentes sin un plugin: activá FeeloLab Core para habilitarlo.', 'feelolab' );
		}
		foreach ( $own_files as $id => $texts ) {
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
						'label'           => $texts[0],
						'description'     => $texts[1],
						'section'         => 'feelolab_type',
						'mime_type'       => 'font',
						'active_callback' => $own_active,
					)
				)
			);
		}
		feelolab_customizer_select(
			$wp_customize,
			'feelolab_font_own_generic',
			__( 'Fuente propia: estilo', 'feelolab' ),
			'feelolab_type',
			array(
				'sans-serif' => __( 'Sin serifa (palo seco)', 'feelolab' ),
				'serif'      => __( 'Con serifa', 'feelolab' ),
			),
			'sans-serif',
			__( 'Define qué fuente del sistema se ve mientras carga la tuya.', 'feelolab' )
		);
		$wp_customize->get_control( 'feelolab_font_own_generic' )->active_callback = $own_active;

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
		feelolab_customizer_select(
			$wp_customize,
			'feelolab_header_layout',
			__( 'Diseño', 'feelolab' ),
			'feelolab_header',
			array(
				'clasico'  => __( 'Logo a la izquierda, menú a la derecha', 'feelolab' ),
				'centrado' => __( 'Logo centrado, menú debajo', 'feelolab' ),
			),
			'clasico',
			__( 'En el celular los dos se ven igual: logo y botón de menú.', 'feelolab' )
		);
		feelolab_customizer_checkbox( $wp_customize, 'feelolab_header_sticky', __( 'Encabezado fijo al hacer scroll', 'feelolab' ), 'feelolab_header', true );
		feelolab_customizer_checkbox( $wp_customize, 'feelolab_header_search', __( 'Mostrar buscador', 'feelolab' ), 'feelolab_header', false );
		feelolab_customizer_text( $wp_customize, 'feelolab_header_cta_text', __( 'Botón del encabezado: texto', 'feelolab' ), 'feelolab_header', '' );
		feelolab_customizer_text( $wp_customize, 'feelolab_header_cta_url', __( 'Botón del encabezado: link', 'feelolab' ), 'feelolab_header', '', 'url' );

		feelolab_customizer_checkbox( $wp_customize, 'feelolab_header_transparent', __( 'Transparente sobre la portada', 'feelolab' ), 'feelolab_header', false );
		$wp_customize->get_control( 'feelolab_header_transparent' )->description = __( 'Solo en la home y cuando la portada usa "Imagen de fondo a todo el ancho". Al hacer scroll vuelve a su color.', 'feelolab' );
		$wp_customize->add_setting(
			'feelolab_logo_light',
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'feelolab_logo_light',
				array(
					'label'           => __( 'Logo claro (para el encabezado transparente)', 'feelolab' ),
					'description'     => __( 'Una versión blanca o clara del logo, que se lea sobre la foto. Si no la cargás, se usa el logo de siempre.', 'feelolab' ),
					'section'         => 'feelolab_header',
					'mime_type'       => 'image',
					'active_callback' => static fn() => (bool) get_theme_mod( 'feelolab_header_transparent', false ),
				)
			)
		);

		feelolab_customizer_checkbox( $wp_customize, 'feelolab_topbar', __( 'Barra superior con contacto y redes', 'feelolab' ), 'feelolab_header', false );
		$wp_customize->get_control( 'feelolab_topbar' )->description = __( 'Muestra el teléfono, WhatsApp, email y redes cargados en Ajustes del sitio.', 'feelolab' );
		feelolab_customizer_text( $wp_customize, 'feelolab_topbar_text', __( 'Barra superior: texto corto (opcional)', 'feelolab' ), 'feelolab_header', '' );
		$wp_customize->get_control( 'feelolab_topbar_text' )->active_callback = static fn() => (bool) get_theme_mod( 'feelolab_topbar', false );

		/* ---------- Footer ---------- */
		$wp_customize->add_section(
			'feelolab_footer',
			array(
				'title' => __( 'Pie de página', 'feelolab' ),
				'panel' => 'feelolab_brand',
			)
		);
		feelolab_customizer_text( $wp_customize, 'feelolab_footer_text', __( 'Texto breve bajo el logo', 'feelolab' ), 'feelolab_footer', '', 'textarea' );
		feelolab_customizer_select(
			$wp_customize,
			'feelolab_footer_style',
			__( 'Estilo', 'feelolab' ),
			'feelolab_footer',
			array(
				'marca' => __( 'Del color secundario de la marca', 'feelolab' ),
				'claro' => __( 'Claro', 'feelolab' ),
			),
			'marca',
			__( 'Para sumar columnas (horarios, links, un texto), agregá widgets en Apariencia → Widgets → Pie de página: columnas.', 'feelolab' )
		);
		feelolab_customizer_checkbox( $wp_customize, 'feelolab_footer_credit', __( 'Mostrar "Sitio hecho por FeeloLab" (con link a feelolab.com)', 'feelolab' ), 'feelolab_footer', true );

		/* ---------- Blog ---------- */
		$wp_customize->add_section(
			'feelolab_blog',
			array(
				'title' => __( 'Blog', 'feelolab' ),
				'panel' => 'feelolab_brand',
			)
		);
		feelolab_customizer_checkbox( $wp_customize, 'feelolab_blog_reading_time', __( 'Mostrar el tiempo de lectura', 'feelolab' ), 'feelolab_blog', true );
		feelolab_customizer_checkbox( $wp_customize, 'feelolab_blog_share', __( 'Mostrar botones para compartir', 'feelolab' ), 'feelolab_blog', true );
		feelolab_customizer_checkbox( $wp_customize, 'feelolab_blog_author', __( 'Mostrar el recuadro del autor', 'feelolab' ), 'feelolab_blog', true );
		$wp_customize->get_control( 'feelolab_blog_author' )->description = __( 'Aparece solo si el autor completó su biografía en Usuarios → Perfil.', 'feelolab' );

		/* ---------- Home ---------- */
		$wp_customize->add_panel(
			'feelolab_home',
			array(
				'title'           => __( 'Secciones de la home', 'feelolab' ),
				'priority'        => 26,
				'description'     => __( 'Ordená las secciones arrastrándolas en "Orden de las secciones" y completá cada una. Los cambios de texto e imágenes se ven al instante.', 'feelolab' ),
				'active_callback' => 'is_front_page',
			)
		);
		require_once FEELOLAB_DIR . '/inc/class-feelolab-sortable-control.php';
		$sections = feelolab_home_sections();
		$items    = array();
		foreach ( feelolab_home_ordered_keys() as $key ) {
			$items[ $key ] = $sections[ $key ]['label'];
		}
		$wp_customize->add_section(
			'feelolab_home_order',
			array(
				'title'    => __( 'Orden de las secciones', 'feelolab' ),
				'panel'    => 'feelolab_home',
				'priority' => 1,
			)
		);
		$wp_customize->add_setting(
			'feelolab_home_order',
			array(
				'default'           => '',
				'sanitize_callback' => static fn( $value ) => implode( ',', array_intersect( array_map( 'sanitize_key', explode( ',', (string) $value ) ), array_keys( $sections ) ) ),
			)
		);
		$wp_customize->add_control(
			new Feelolab_Sortable_Control(
				$wp_customize,
				'feelolab_home_order',
				array(
					'label'       => __( 'Orden de las secciones', 'feelolab' ),
					'description' => __( 'Arrastrá cada sección o usá las flechas. Las ocultas no se ven en el sitio; se prenden desde el lápiz de cada una.', 'feelolab' ),
					'section'     => 'feelolab_home_order',
					'items'       => $items,
				)
			)
		);

		foreach ( $sections as $key => $section ) {
			if ( ! feelolab_home_section_available( $section ) ) {
				continue;
			}
			$section_id = 'feelolab_home_' . $key;
			$wp_customize->add_section(
				$section_id,
				array(
					'title'    => $section['label'],
					'panel'    => 'feelolab_home',
					'priority' => 10 + (int) array_search( $key, array_keys( $items ), true ),
				)
			);
			feelolab_customizer_checkbox( $wp_customize, "feelolab_home_{$key}_show", __( 'Mostrar esta sección', 'feelolab' ), $section_id, $section['show'] );

			$live = array();
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
				} elseif ( 'select' === $def['type'] ) {
					feelolab_customizer_select( $wp_customize, $id, $def['label'], $section_id, $def['choices'], $def['default'] );
				} else {
					feelolab_customizer_text( $wp_customize, $id, $def['label'], $section_id, $def['default'] ?? '', $def['type'] );
				}
				// La portada define si el encabezado va transparente: su diseño y su imagen recargan todo.
				if ( 'hero' !== $key || ! in_array( $field, array( 'layout', 'image' ), true ) ) {
					$live[] = $id;

					$wp_customize->get_setting( $id )->transport = 'postMessage';
				}
			}

			// Vista previa al instante: solo se vuelve a pedir esta sección, no la página entera.
			if ( $live && isset( $wp_customize->selective_refresh ) ) {
				$wp_customize->selective_refresh->add_partial(
					'feelolab_home_' . $key,
					array(
						'selector'            => '[data-feelolab-home="' . $key . '"]',
						'settings'            => $live,
						'container_inclusive' => true,
						'fallback_refresh'    => true,
						'render_callback'     => static function () use ( $key ): void {
							feelolab_home_render_section( $key, (int) array_search( $key, feelolab_home_active_sections(), true ) );
						},
					)
				);
			}
		}

		// Colores, tipografía y forma: se recalcula solo el CSS de marca (con el ajuste de contraste en PHP).
		$brand = array( 'feelolab_font_pair', 'feelolab_font_size', 'feelolab_radius', 'feelolab_container', 'feelolab_font_own_heading', 'feelolab_font_own_body', 'feelolab_font_own_body_bold', 'feelolab_font_own_generic' );
		foreach ( array_keys( feelolab_color_settings() ) as $color ) {
			$brand[] = 'feelolab_color_' . $color;
		}
		foreach ( $brand as $id ) {
			$wp_customize->get_setting( $id )->transport = 'postMessage';
		}
		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'feelolab_brand_css',
				array(
					'selector'            => '#feelolab-brand-partial',
					'settings'            => $brand,
					'container_inclusive' => true,
					'fallback_refresh'    => true,
					'render_callback'     => 'feelolab_brand_css_partial',
				)
			);
		}
	}
);

/**
 * Contenedor del CSS de marca para la vista previa en vivo. No se refresca el <style> mismo: el
 * refresco parcial le pone un title y un <style> con title pasa a ser una hoja "alternativa" que el
 * navegador no aplica. Llega acá y customizer-preview.js lo copia al <style> del tema.
 */
function feelolab_brand_css_partial(): void {
	wp_print_inline_script_tag(
		feelolab_brand_css(),
		array(
			'type' => 'text/css',
			'id'   => 'feelolab-brand-partial',
		)
	);
}

add_action(
	'wp_footer',
	static function (): void {
		if ( is_customize_preview() ) {
			feelolab_brand_css_partial();
		}
	}
);

/** Vista previa: aplica el CSS de marca refrescado en vivo. */
add_action(
	'customize_preview_init',
	static function (): void {
		wp_enqueue_script( 'feelolab-customizer-preview', FEELOLAB_URI . '/assets/js/customizer-preview.js', array( 'customize-preview', 'customize-selective-refresh' ), FEELOLAB_VERSION, true );
	}
);

/** Aviso de contraste en vivo mientras se eligen colores. */
add_action(
	'customize_controls_enqueue_scripts',
	static function (): void {
		wp_enqueue_script( 'feelolab-customizer-controls', FEELOLAB_URI . '/assets/js/customizer-controls.js', array( 'customize-controls' ), FEELOLAB_VERSION, true );
		wp_enqueue_script( 'feelolab-customizer-sortable', FEELOLAB_URI . '/assets/js/customizer-sortable.js', array( 'customize-controls', 'wp-a11y' ), FEELOLAB_VERSION, true );
		wp_localize_script(
			'feelolab-customizer-sortable',
			'feelolabSortable',
			array(
				'hidden' => __( 'Oculta', 'feelolab' ),
				/* translators: 1: sección, 2: posición, 3: total */
				'moved'  => __( '%1$s, posición %2$d de %3$d', 'feelolab' ),
			)
		);

		// Paneles propios de FeeloLab: barra y etiqueta de marca en la lista, cabecera con degradado al abrirlos.
		$row   = static fn( string $suffix = '' ) => '#accordion-panel-feelolab_brand > .accordion-section-title' . $suffix . ',#accordion-panel-feelolab_home > .accordion-section-title' . $suffix;
		$metas = '#sub-accordion-panel-feelolab_brand .panel-meta,#sub-accordion-panel-feelolab_home .panel-meta';
		$meta  = static fn( string $sel ) => '#sub-accordion-panel-feelolab_brand .panel-meta ' . $sel . ',#sub-accordion-panel-feelolab_home .panel-meta ' . $sel;
		wp_add_inline_style(
			'customize-controls',
			$row() . '{position:relative;box-shadow:inset 4px 0 0 #4a6400}'
			. $row( '::before' ) . '{content:"FeeloLab";position:absolute;top:50%;right:44px;z-index:1;transform:translateY(-50%);padding:1px 8px;border-radius:999px;background:#10130a;color:#b9d101;font-size:11px;font-weight:700;line-height:18px;pointer-events:none}'
			. $metas . '{background:radial-gradient(circle at 85% 20%,rgb(185 209 1/.35),transparent 45%),linear-gradient(135deg,#0e1108,#1b240a 55%,#34470a)}'
			. $meta( '.accordion-section-title' ) . '{background:transparent;border:0}'
			. $meta( '.preview-notice' ) . '{color:#ece7e2}'
			. $meta( '.panel-title' ) . '{color:#fff}'
			. $meta( '.customize-panel-back' ) . '{background:transparent;color:#ece7e2;border-right-color:rgb(255 255 255/.2)}'
			. $meta( '.customize-panel-back:hover' ) . ',' . $meta( '.customize-panel-back:focus' ) . '{background:rgb(255 255 255/.1);color:#b9d101}'
			// Lista ordenable de secciones.
			. '.feelolab-sortable{margin:12px 0 0;padding:0;list-style:none}'
			. '.feelolab-sortable__item{display:flex;align-items:center;gap:4px;margin:0 0 6px;padding:6px 6px 6px 8px;border:1px solid #c3c4c7;border-radius:6px;background:#fff;cursor:grab}'
			. '.feelolab-sortable__item.is-dragging{opacity:.5;border-style:dashed}'
			. '.feelolab-sortable__item.is-hidden .feelolab-sortable__label{color:#646970}'
			. '.feelolab-sortable__handle{color:#646970}'
			. '.feelolab-sortable__label{flex:1;font-weight:600}'
			. '.feelolab-sortable__state{padding:0 6px;border-radius:99px;background:#f0f0f1;color:#50575e;font-size:11px}'
			. '.feelolab-sortable__state:empty{display:none}'
			. '.feelolab-sortable__btn{display:grid;place-items:center;width:30px;height:30px;padding:0;border:0;border-radius:4px;background:none;color:#1d2327;cursor:pointer}'
			. '.feelolab-sortable__btn:focus-visible{outline:2px solid #2271b1;outline-offset:0;box-shadow:none}'
			. '.feelolab-sortable__item button:hover{background:#f0f6e1;color:#4a6400}'
			. '.feelolab-sortable__item:first-child [data-dir="-1"],.feelolab-sortable__item:last-child [data-dir="1"]{opacity:.35;cursor:default}'
		);

		wp_localize_script(
			'feelolab-customizer-controls',
			'feelolabContrast',
			array(
				/* translators: %s: relación de contraste, por ejemplo 3.2 */
				'btnBorder'    => __( 'El botón casi no se distingue del fondo de la página (%s:1). Se le agrega un borde para que se vea como botón.', 'feelolab' ),
				/* translators: %s: relación de contraste, por ejemplo 3.2 */
				'lowBtnText'   => __( 'Este texto no se lee bien sobre el fondo del botón (%s:1, mínimo 4.5:1). El sitio va a usar blanco o negro, el que mejor se lea.', 'feelolab' ),
				/* translators: %s: relación de contraste, por ejemplo 3.2 */
				'lowLink'      => __( 'Los links tendrían poco contraste con el fondo (%s:1, mínimo 4.5:1). El sitio va a usar una versión más oscura de este color.', 'feelolab' ),
				/* translators: %s: relación de contraste, por ejemplo 3.2 */
				'lowLinkAuto'  => __( 'Los links usan este color y sobre el fondo no se leerían (%s:1). El sitio va a usar una versión más oscura; si preferís otro, elegilo en "Links y acentos".', 'feelolab' ),
				/* translators: %s: relación de contraste, por ejemplo 1.0 */
				'linkFallback' => __( 'Este color es muy claro para usarlo en links (%s:1 sobre el fondo), así que los links van a usar el color Secundario (o el de Texto). Si preferís otro, elegilo en "Links y acentos".', 'feelolab' ),
				/* translators: %s: relación de contraste, por ejemplo 3.2 */
				'lowText'      => __( 'El texto tiene poco contraste con el fondo (%s:1). El tema lo va a oscurecer para que se lea.', 'feelolab' ),
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
