<?php
/**
 * Datos estructurados (JSON-LD) y meta básicos.
 *
 * Reglas:
 * - Organización/negocio, WebSite, meta description y Open Graph: solo si NO hay un plugin de SEO
 *   (Yoast, Rank Math…). Duplicar la entidad principal confunde a Google.
 * - Schema por tipo de contenido (Service, Product, FAQPage, LocalBusiness de sedes, Person):
 *   siempre, porque los plugins de SEO no los arman para nuestros CPT.
 * - Todo sale de datos cargados. Un campo vacío no se inventa: se omite.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core\Schema;

defined( 'ABSPATH' ) || exit;

final class Schema {

	/** Etiquetas que Google acepta en la respuesta de un FAQPage. */
	private const ANSWER_TAGS = array(
		'p'      => array(),
		'br'     => array(),
		'ul'     => array(),
		'ol'     => array(),
		'li'     => array(),
		'a'      => array( 'href' => array() ),
		'strong' => array(),
		'em'     => array(),
	);

	/** @var array<int, array<string, mixed>> Nodos agregados durante el render (FAQs, migas…). */
	private static array $pieces = array();

	/** @var int[] FAQs ya agregadas, para no repetirlas si aparecen dos veces en la página. */
	private static array $faq_ids = array();

	public static function init(): void {
		add_action( 'wp_head', array( self::class, 'head' ), 2 );
		add_action( 'wp_footer', array( self::class, 'footer' ), 99 );
	}

	/**
	 * Agrega un nodo al grafo de la página (lo usa el tema para migas, etc.).
	 *
	 * @param array<string, mixed> $node Nodo schema.org.
	 */
	public static function add( array $node ): void {
		self::$pieces[] = $node;
	}

	/**
	 * El tema llama esto al mostrar una lista de preguntas. Se juntan todas en un FAQPage.
	 *
	 * @param int[] $ids IDs de feelo_faq.
	 */
	public static function add_faqs( array $ids ): void {
		self::$faq_ids = array_values( array_unique( array_merge( self::$faq_ids, array_map( 'intval', $ids ) ) ) );
	}

	public static function head(): void {
		if ( ! feelo_seo_plugin_active() ) {
			self::meta();
		}

		$graph = array();
		if ( ! feelo_seo_plugin_active() ) {
			$graph[] = self::organization();
			$graph[] = array(
				'@type'      => 'WebSite',
				'@id'        => home_url( '/#website' ),
				'url'        => home_url( '/' ),
				'name'       => feelo_business_name(),
				'publisher'  => array( '@id' => home_url( '/#organization' ) ),
				'inLanguage' => get_bloginfo( 'language' ),
			);
		}

		if ( is_singular() ) {
			$node = self::singular_node( get_queried_object_id() );
			if ( $node ) {
				$graph[] = $node;
			}
		}

		self::print( $graph );
	}

	public static function footer(): void {
		$graph = self::$pieces;

		if ( self::$faq_ids ) {
			$questions = array();
			foreach ( self::$faq_ids as $id ) {
				$post = get_post( $id );
				if ( ! $post || 'publish' !== $post->post_status ) {
					continue;
				}
				$html        = apply_filters( 'the_content', $post->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro de core.
				$questions[] = array(
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( get_the_title( $post ) ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_kses( $html, self::ANSWER_TAGS ),
					),
				);
			}
			if ( $questions ) {
				$graph[] = array(
					'@type'      => 'FAQPage',
					'mainEntity' => $questions,
				);
			}
		}

		self::print( $graph );
	}

	/** @param array<int, array<string, mixed>> $graph Nodos. */
	private static function print( array $graph ): void {
		$graph = array_values( array_filter( apply_filters( 'feelo_schema_graph', $graph ) ) );
		if ( ! $graph ) {
			return;
		}
		echo "\n<script type=\"application/ld+json\">" . wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
		) . "</script>\n";
	}

	/** @return array<string, mixed> */
	private static function organization(): array {
		$type = (string) feelo_setting( 'tipo_negocio', 'Organization' );
		$node = array(
			'@type' => $type,
			'@id'   => home_url( '/#organization' ),
			'name'  => feelo_business_name(),
			'url'   => home_url( '/' ),
		);

		$logo_id = (int) get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$logo = wp_get_attachment_image_src( $logo_id, 'full' );
			if ( $logo ) {
				$node['logo'] = array(
					'@type'  => 'ImageObject',
					'url'    => $logo[0],
					'width'  => $logo[1],
					'height' => $logo[2],
				);
				// LocalBusiness exige image; el logo es mejor que nada.
				$node['image'] = $logo[0];
			}
		}

		$node += self::filled(
			array(
				'description' => (string) feelo_setting( 'descripcion' ),
				'telephone'   => (string) feelo_setting( 'telefono' ),
				'email'       => (string) feelo_setting( 'email' ),
				'legalName'   => (string) feelo_setting( 'razon_social' ),
				'taxID'       => (string) feelo_setting( 'id_fiscal' ),
			)
		);

		$same_as = array_values( wp_list_pluck( feelo_socials(), 'url' ) );
		if ( $same_as ) {
			$node['sameAs'] = $same_as;
		}

		if ( 'Organization' !== $type && 'EducationalOrganization' !== $type ) {
			$address = self::address(
				(string) feelo_setting( 'direccion' ),
				(string) feelo_setting( 'ciudad' ),
				(string) feelo_setting( 'provincia' ),
				(string) feelo_setting( 'codigo_postal' ),
				(string) feelo_setting( 'pais' )
			);
			if ( $address ) {
				$node['address'] = $address;
			}
			$hours = self::hours( feelo_opening_hours() );
			if ( $hours ) {
				$node['openingHoursSpecification'] = $hours;
			}
			if ( feelo_setting( 'rango_precios' ) ) {
				$node['priceRange'] = (string) feelo_setting( 'rango_precios' );
			}
			if ( feelo_setting( 'mapa_url' ) ) {
				$node['hasMap'] = (string) feelo_setting( 'mapa_url' );
			}
		}

		return $node;
	}

	/** @return array<string, mixed>|null */
	private static function singular_node( int $post_id ): ?array {
		$post_type = get_post_type( $post_id );
		$url       = get_permalink( $post_id );
		$base      = array(
			'@id'         => $url . '#main',
			'name'        => wp_strip_all_tags( get_the_title( $post_id ) ),
			'url'         => $url,
			'description' => feelo_plain_summary( get_post( $post_id ) ),
		);
		// Destacada + galería: Google recomienda varias imágenes para productos.
		$images = array_filter(
			array_merge(
				array( get_the_post_thumbnail_url( $post_id, 'large' ) ),
				array_map( static fn( $id ) => wp_get_attachment_image_url( $id, 'large' ), feelo_gallery_ids( $post_id ) )
			)
		);
		$images = array_values( array_unique( $images ) );
		if ( $images ) {
			$base['image'] = 1 === count( $images ) ? $images[0] : $images;
		}
		$base = self::filled( $base );

		switch ( $post_type ) {
			case feelo_module_post_type( 'servicios' ):
				$node  = array( '@type' => 'Service' ) + $base + array( 'provider' => array( '@id' => home_url( '/#organization' ) ) );
				$price = self::price( (string) feelo_field( 'precio_desde', $post_id ) );
				if ( null !== $price && feelo_setting( 'moneda' ) ) {
					$node['offers'] = array(
						'@type'              => 'Offer',
						'priceSpecification' => array(
							'@type'         => 'PriceSpecification',
							'minPrice'      => $price,
							'priceCurrency' => (string) feelo_setting( 'moneda' ),
						),
					);
				}
				return $node;

			case feelo_module_post_type( 'productos' ):
				$node = array( '@type' => 'Product' ) + $base;
				$sku  = (string) feelo_field( 'sku', $post_id );
				if ( $sku ) {
					$node['sku'] = $sku;
				}
				$brands = taxonomy_exists( 'feelo_marca' ) ? get_the_terms( $post_id, 'feelo_marca' ) : false;
				if ( $brands && ! is_wp_error( $brands ) ) {
					$node['brand'] = array(
						'@type' => 'Brand',
						'name'  => $brands[0]->name,
					);
				}
				$offer = (string) feelo_field( 'precio_oferta', $post_id );
				$price = self::price( $offer ? $offer : (string) feelo_field( 'precio', $post_id ) );
				if ( null !== $price && feelo_setting( 'moneda' ) ) {
					$available      = feelo_field( 'disponible', $post_id );
					$node['offers'] = array(
						'@type'         => 'Offer',
						'price'         => $price,
						'priceCurrency' => (string) feelo_setting( 'moneda' ),
						'availability'  => ( '' === $available || $available ) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
						'url'           => $url,
					);
				}
				return $node;

			case feelo_module_post_type( 'sedes' ):
				$node = array( '@type' => (string) feelo_setting( 'tipo_negocio', 'LocalBusiness' ) ) + $base;
				if ( in_array( $node['@type'], array( 'Organization', '' ), true ) ) {
					$node['@type'] = 'LocalBusiness';
				}
				$node['parentOrganization'] = array( '@id' => home_url( '/#organization' ) );
				$node                      += self::filled(
					array(
						'telephone' => (string) feelo_field( 'telefono', $post_id ),
						'email'     => (string) feelo_field( 'email', $post_id ),
						'hasMap'    => (string) feelo_field( 'mapa_url', $post_id ),
					)
				);
				$address                    = self::address( (string) feelo_field( 'direccion', $post_id ), (string) feelo_field( 'ciudad', $post_id ), '', '', (string) feelo_setting( 'pais' ) );
				if ( $address ) {
					$node['address'] = $address;
				}
				$hours = self::hours( feelo_opening_hours( (string) feelo_field( 'horarios', $post_id ) ) );
				if ( $hours ) {
					$node['openingHoursSpecification'] = $hours;
				}
				return $node;

			case feelo_module_post_type( 'equipo' ):
				$node  = array( '@type' => 'Person' ) + $base + array( 'worksFor' => array( '@id' => home_url( '/#organization' ) ) );
				$node += self::filled( array( 'jobTitle' => (string) feelo_field( 'cargo', $post_id ) ) );
				$same  = array_values( array_filter( array( (string) feelo_field( 'linkedin', $post_id ), (string) feelo_field( 'instagram', $post_id ) ) ) );
				if ( $same ) {
					$node['sameAs'] = $same;
				}
				return $node;
		}

		if ( 'post' === $post_type && ! feelo_seo_plugin_active() ) {
			return array(
				'@type'         => 'BlogPosting',
				'headline'      => $base['name'],
				'datePublished' => get_the_date( 'c', $post_id ),
				'dateModified'  => get_the_modified_date( 'c', $post_id ),
				'author'        => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ),
				),
				'publisher'     => array( '@id' => home_url( '/#organization' ) ),
			) + $base;
		}

		return null;
	}

	/** @return array<string, string>|null */
	private static function address( string $street, string $city, string $region, string $zip, string $country ): ?array {
		$address = self::filled(
			array(
				'streetAddress'   => $street,
				'addressLocality' => $city,
				'addressRegion'   => $region,
				'postalCode'      => $zip,
				'addressCountry'  => strtoupper( $country ),
			)
		);
		return $address ? array( '@type' => 'PostalAddress' ) + $address : null;
	}

	/**
	 * @param array<int, array{days: string[], opens: string, closes: string}> $rows Horarios parseados.
	 * @return array<int, array<string, mixed>>
	 */
	private static function hours( array $rows ): array {
		$out = array();
		foreach ( $rows as $row ) {
			if ( ! $row['days'] ) {
				continue;
			}
			$out[] = array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => $row['days'],
				'opens'     => $row['opens'],
				'closes'    => $row['closes'],
			);
		}
		return $out;
	}

	/** "$ 25.000,50" → "25000.50". Null si no hay número. */
	private static function price( string $raw ): ?string {
		$digits = preg_replace( '/[^\d.,]/', '', $raw );
		if ( '' === $digits || null === $digits ) {
			return null;
		}
		// Formato rioplatense: punto de miles, coma decimal.
		if ( preg_match( '/,\d{1,2}$/', $digits ) ) {
			$digits = str_replace( array( '.', ',' ), array( '', '.' ), $digits );
		} else {
			$digits = str_replace( array( '.', ',' ), '', $digits );
		}
		return is_numeric( $digits ) ? $digits : null;
	}

	/**
	 * Quita claves vacías.
	 *
	 * @param array<string, mixed> $values Valores.
	 * @return array<string, mixed>
	 */
	private static function filled( array $values ): array {
		return array_filter( $values, static fn( $v ) => '' !== $v && null !== $v && array() !== $v );
	}

	/** Meta description, Open Graph y Twitter card cuando no hay plugin de SEO. */
	private static function meta(): void {
		$title       = wp_get_document_title();
		$description = '';
		$image       = '';
		$type        = 'website';
		$url         = '';

		if ( is_front_page() ) {
			$description = (string) feelo_setting( 'descripcion', get_bloginfo( 'description' ) );
			$url         = home_url( '/' );
		} elseif ( is_singular() ) {
			$post_id     = get_queried_object_id();
			$description = feelo_plain_summary( get_post( $post_id ) );
			$image       = (string) get_the_post_thumbnail_url( $post_id, 'large' );
			$type        = 'post' === get_post_type( $post_id ) ? 'article' : 'website';
			$url         = (string) get_permalink( $post_id );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$description = wp_strip_all_tags( term_description() );
			$url         = (string) get_term_link( get_queried_object() );
		} elseif ( is_post_type_archive() ) {
			$object      = get_queried_object();
			$description = $object && ! empty( $object->description ) ? $object->description : '';
			$url         = (string) get_post_type_archive_link( (string) get_query_var( 'post_type' ) );
		}

		if ( ! $image ) {
			$logo_id = (int) get_theme_mod( 'custom_logo' );
			$image   = $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		}

		$description = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $description ) ) );
		$description = mb_strlen( $description ) > 160 ? mb_substr( $description, 0, 157 ) . '…' : $description;

		if ( $description ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		}
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( feelo_business_name() ) );
		printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( get_locale() ) );
		printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		if ( $description ) {
			printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
		}
		if ( $url ) {
			printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		}
		if ( $image ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		}
		printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );
	}
}
