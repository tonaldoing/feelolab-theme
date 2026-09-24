<?php
/**
 * Módulos de contenido: cada uno es un CPT con sus taxonomías.
 *
 * Todo se declara como datos en definitions() y se registra en código (no desde la UI de ACF):
 * así va versionado en git y el contenido no depende de que ACF esté activo.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core\Modules;

defined( 'ABSPATH' ) || exit;

final class Registry {

	public const OPTION = 'feelo_modules';

	/** Activos en una instalación nueva. El resto se prende desde Ajustes → Módulos. */
	private const DEFAULTS = array( 'servicios', 'testimonios', 'faq' );

	/**
	 * Definición de cada módulo.
	 *
	 * - public: false → sin URL propia (testimonios, FAQ, clientes se muestran dentro de otras páginas).
	 * - slug: base de la URL pública; editable con el filtro feelo_module_slug.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function definitions(): array {
		$modules = array(
			'servicios'   => array(
				'post_type'   => 'feelo_servicio',
				'singular'    => __( 'Servicio', 'feelolab-core' ),
				'plural'      => __( 'Servicios', 'feelolab-core' ),
				'description' => __( 'Lo que ofrece el negocio, con categoría, precio desde y llamada a la acción.', 'feelolab-core' ),
				'slug'        => _x( 'servicios', 'slug de URL', 'feelolab-core' ),
				'icon'        => 'dashicons-hammer',
				'public'      => true,
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
				'taxonomies'  => array(
					'feelo_servicio_cat' => array(
						'singular'     => __( 'Categoría de servicio', 'feelolab-core' ),
						'plural'       => __( 'Categorías de servicio', 'feelolab-core' ),
						'slug'         => _x( 'categoria-servicio', 'slug de URL', 'feelolab-core' ),
						'hierarchical' => true,
					),
				),
			),
			'productos'   => array(
				'post_type'   => 'feelo_producto',
				'singular'    => __( 'Producto', 'feelolab-core' ),
				'plural'      => __( 'Productos', 'feelolab-core' ),
				'description' => __( 'Catálogo con consulta. Para vender online, instalar WooCommerce.', 'feelolab-core' ),
				'slug'        => _x( 'productos', 'slug de URL', 'feelolab-core' ),
				'icon'        => 'dashicons-products',
				'public'      => true,
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'taxonomies'  => array(
					'feelo_producto_cat' => array(
						'singular'     => __( 'Categoría de producto', 'feelolab-core' ),
						'plural'       => __( 'Categorías de producto', 'feelolab-core' ),
						'slug'         => _x( 'categoria-producto', 'slug de URL', 'feelolab-core' ),
						'hierarchical' => true,
					),
					'feelo_marca'        => array(
						'singular'     => __( 'Marca', 'feelolab-core' ),
						'plural'       => __( 'Marcas', 'feelolab-core' ),
						'slug'         => _x( 'marca', 'slug de URL', 'feelolab-core' ),
						'hierarchical' => false,
					),
				),
			),
			'proyectos'   => array(
				'post_type'   => 'feelo_proyecto',
				'singular'    => __( 'Proyecto', 'feelolab-core' ),
				'plural'      => __( 'Proyectos', 'feelolab-core' ),
				'description' => __( 'Portfolio o casos de éxito.', 'feelolab-core' ),
				'slug'        => _x( 'proyectos', 'slug de URL', 'feelolab-core' ),
				'icon'        => 'dashicons-portfolio',
				'public'      => true,
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'taxonomies'  => array(
					'feelo_proyecto_tipo' => array(
						'singular'     => __( 'Tipo de proyecto', 'feelolab-core' ),
						'plural'       => __( 'Tipos de proyecto', 'feelolab-core' ),
						'slug'         => _x( 'tipo-proyecto', 'slug de URL', 'feelolab-core' ),
						'hierarchical' => true,
					),
				),
			),
			'equipo'      => array(
				'post_type'   => 'feelo_miembro',
				'singular'    => __( 'Integrante', 'feelolab-core' ),
				'plural'      => __( 'Equipo', 'feelolab-core' ),
				'description' => __( 'Personas del equipo con cargo, foto y redes.', 'feelolab-core' ),
				'slug'        => _x( 'equipo', 'slug de URL', 'feelolab-core' ),
				'icon'        => 'dashicons-groups',
				'public'      => true,
				'supports'    => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
				'taxonomies'  => array(
					'feelo_area' => array(
						'singular'     => __( 'Área', 'feelolab-core' ),
						'plural'       => __( 'Áreas', 'feelolab-core' ),
						'slug'         => _x( 'area', 'slug de URL', 'feelolab-core' ),
						'hierarchical' => true,
					),
				),
			),
			'testimonios' => array(
				'post_type'   => 'feelo_testimonio',
				'singular'    => __( 'Testimonio', 'feelolab-core' ),
				'plural'      => __( 'Testimonios', 'feelolab-core' ),
				'description' => __( 'Opiniones de clientes. Se muestran en la home y en los servicios.', 'feelolab-core' ),
				'icon'        => 'dashicons-format-quote',
				'public'      => false,
				'supports'    => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
				'taxonomies'  => array(),
			),
			'faq'         => array(
				'post_type'   => 'feelo_faq',
				'singular'    => __( 'Pregunta frecuente', 'feelolab-core' ),
				'plural'      => __( 'Preguntas frecuentes', 'feelolab-core' ),
				'description' => __( 'El título es la pregunta y el contenido la respuesta. Suma schema FAQPage.', 'feelolab-core' ),
				'icon'        => 'dashicons-editor-help',
				'public'      => false,
				'supports'    => array( 'title', 'editor', 'page-attributes' ),
				'taxonomies'  => array(
					'feelo_faq_tema' => array(
						'singular'     => __( 'Tema', 'feelolab-core' ),
						'plural'       => __( 'Temas', 'feelolab-core' ),
						'slug'         => _x( 'tema-faq', 'slug de URL', 'feelolab-core' ),
						'hierarchical' => true,
					),
				),
			),
			'sedes'       => array(
				'post_type'   => 'feelo_sede',
				'singular'    => __( 'Sede', 'feelolab-core' ),
				'plural'      => __( 'Sedes', 'feelolab-core' ),
				'description' => __( 'Sucursales con dirección, horarios y mapa. Cada una suma su LocalBusiness.', 'feelolab-core' ),
				'slug'        => _x( 'sedes', 'slug de URL', 'feelolab-core' ),
				'icon'        => 'dashicons-location',
				'public'      => true,
				'supports'    => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
				'taxonomies'  => array(
					'feelo_zona' => array(
						'singular'     => __( 'Zona', 'feelolab-core' ),
						'plural'       => __( 'Zonas', 'feelolab-core' ),
						'slug'         => _x( 'zona', 'slug de URL', 'feelolab-core' ),
						'hierarchical' => true,
					),
				),
			),
			'clientes'    => array(
				'post_type'   => 'feelo_cliente',
				'singular'    => __( 'Cliente', 'feelolab-core' ),
				'plural'      => __( 'Clientes', 'feelolab-core' ),
				'description' => __( 'Logos de clientes o marcas con las que se trabaja. El logo es la imagen destacada.', 'feelolab-core' ),
				'icon'        => 'dashicons-awards',
				'public'      => false,
				'supports'    => array( 'title', 'thumbnail', 'page-attributes' ),
				'taxonomies'  => array(),
			),
		);

		/**
		 * Permite agregar o ajustar módulos desde un plugin del cliente.
		 *
		 * @param array $modules Definiciones.
		 */
		return apply_filters( 'feelo_modules', $modules );
	}

	/** @return string[] Claves de módulos activos. */
	public static function enabled(): array {
		$saved = get_option( self::OPTION, null );
		$keys  = is_array( $saved ) ? $saved : self::DEFAULTS;
		return array_values( array_intersect( $keys, array_keys( self::definitions() ) ) );
	}

	public static function is_enabled( string $key ): bool {
		return in_array( $key, self::enabled(), true );
	}

	/** Post type de un módulo si está activo, o null. */
	public static function post_type( string $key ): ?string {
		$defs = self::definitions();
		return ( isset( $defs[ $key ] ) && self::is_enabled( $key ) ) ? $defs[ $key ]['post_type'] : null;
	}

	public static function register_all(): void {
		$defs = self::definitions();
		foreach ( self::enabled() as $key ) {
			self::register( $key, $defs[ $key ] );
		}
		self::register_messages();
	}

	/**
	 * @param string               $key Clave del módulo.
	 * @param array<string, mixed> $def Definición.
	 */
	private static function register( string $key, array $def ): void {
		$public = (bool) $def['public'];
		$slug   = $public ? apply_filters( 'feelo_module_slug', $def['slug'], $key ) : false;

		register_post_type(
			$def['post_type'],
			array(
				'labels'              => self::labels( $def['singular'], $def['plural'] ),
				'description'         => $def['description'],
				'public'              => $public,
				'publicly_queryable'  => $public,
				'exclude_from_search' => ! $public,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => $public,
				'show_in_rest'        => true,
				'menu_icon'           => $def['icon'],
				'supports'            => $def['supports'],
				'has_archive'         => $public ? $slug : false,
				'rewrite'             => $public ? array(
					'slug'       => $slug,
					'with_front' => false,
				) : false,
				'hierarchical'        => false,
			)
		);

		foreach ( $def['taxonomies'] as $taxonomy => $tax ) {
			register_taxonomy(
				$taxonomy,
				$def['post_type'],
				array(
					'labels'             => self::tax_labels( $tax['singular'], $tax['plural'] ),
					'hierarchical'       => $tax['hierarchical'],
					'public'             => $public,
					'publicly_queryable' => $public,
					'show_ui'            => true,
					'show_admin_column'  => true,
					'show_in_rest'       => true,
					'show_in_nav_menus'  => $public,
					'rewrite'            => $public ? array(
						'slug'       => $tax['slug'],
						'with_front' => false,
					) : false,
				)
			);
		}
	}

	/** Mensajes del formulario de contacto: siempre activo, solo en el admin. */
	private static function register_messages(): void {
		register_post_type(
			'feelo_mensaje',
			array(
				'labels'          => self::labels( __( 'Mensaje', 'feelolab-core' ), __( 'Mensajes', 'feelolab-core' ) ),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'show_in_rest'    => false,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 26,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}

	/** @return array<string, string> */
	private static function labels( string $singular, string $plural ): array {
		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'menu_name'          => $plural,
			/* translators: %s: nombre singular */
			'add_new_item'       => sprintf( __( 'Agregar %s', 'feelolab-core' ), mb_strtolower( $singular ) ),
			/* translators: %s: nombre singular */
			'edit_item'          => sprintf( __( 'Editar %s', 'feelolab-core' ), mb_strtolower( $singular ) ),
			/* translators: %s: nombre singular */
			'new_item'           => sprintf( __( 'Nuevo: %s', 'feelolab-core' ), mb_strtolower( $singular ) ),
			/* translators: %s: nombre singular */
			'view_item'          => sprintf( __( 'Ver %s', 'feelolab-core' ), mb_strtolower( $singular ) ),
			/* translators: %s: nombre plural */
			'search_items'       => sprintf( __( 'Buscar en %s', 'feelolab-core' ), mb_strtolower( $plural ) ),
			'not_found'          => __( 'No se encontró nada.', 'feelolab-core' ),
			'not_found_in_trash' => __( 'No hay nada en la papelera.', 'feelolab-core' ),
			'all_items'          => $plural,
		);
	}

	/** @return array<string, string> */
	private static function tax_labels( string $singular, string $plural ): array {
		return array(
			'name'          => $plural,
			'singular_name' => $singular,
			'menu_name'     => $plural,
			/* translators: %s: nombre singular */
			'add_new_item'  => sprintf( __( 'Agregar %s', 'feelolab-core' ), mb_strtolower( $singular ) ),
			/* translators: %s: nombre singular */
			'edit_item'     => sprintf( __( 'Editar %s', 'feelolab-core' ), mb_strtolower( $singular ) ),
			/* translators: %s: nombre plural */
			'search_items'  => sprintf( __( 'Buscar en %s', 'feelolab-core' ), mb_strtolower( $plural ) ),
			'all_items'     => $plural,
		);
	}
}
