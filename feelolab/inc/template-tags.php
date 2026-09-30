<?php
/**
 * Helpers de plantilla.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ícono SVG inline (trazo 2px, 24×24, estilo Lucide). Decorativo: aria-hidden siempre;
 * el texto accesible va al lado, en el elemento que lo contiene.
 */
function feelolab_icon( string $name, string $extra_class = '' ): string {
	$paths = array(
		'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
		'arrow-right'  => '<path d="M5 12h14M13 5l7 7-7 7"/>',
		'cart'         => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2.5 3h2.6l2.4 12.1a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.5L21 7.5H6"/>',
		'menu'         => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'        => '<path d="M18 6 6 18M6 6l12 12"/>',
		'search'       => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
		'phone'        => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
		'mail'         => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
		'map-pin'      => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
		'clock'        => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'check'        => '<path d="M20 6 9 17l-5-5"/>',
		'link'         => '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>',
		'share'        => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
		'star'         => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
		'whatsapp'     => '<path d="M3 21l1.7-5A8.5 8.5 0 1 1 8 19.3z"/><path d="M9 10c0 3 2 5 5 5l1.5-1.5-2-1-1 .8a4 4 0 0 1-2-2l.8-1-1-2z"/>',
		'instagram'    => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
		'facebook'     => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
		'linkedin'     => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/>',
		'tiktok'       => 'fill:<path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>',
		'youtube'      => '<path d="M2.5 17a24 24 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.6 49.6 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24 24 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.6 49.6 0 0 1-16.2 0A2 2 0 0 1 2.5 17z"/><path d="m10 15 5-3-5-3z"/>',
		'x'            => 'fill:<path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.24 6.93zm-1.29 19.5h2.04L6.49 3.24H4.3z"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	// Los logos de marcas (X, TikTok) van con relleno y en su forma oficial; el resto, de trazo.
	$filled = str_starts_with( $paths[ $name ], 'fill:' );
	return sprintf(
		'<svg class="icon icon-%1$s %2$s" aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" %3$s>%4$s</svg>',
		esc_attr( $name ),
		esc_attr( $extra_class ),
		$filled ? 'fill="currentColor"' : 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"',
		$filled ? substr( $paths[ $name ], 5 ) : $paths[ $name ]
	);
}

/**
 * Ajuste del plugin, o $fallback si el plugin no está.
 *
 * @param mixed $fallback Valor si está vacío.
 * @return mixed
 */
function feelolab_setting( string $key, $fallback = '' ) {
	return function_exists( 'feelo_setting' ) ? feelo_setting( $key, $fallback ) : $fallback;
}

/** Campo de contenido (ACF o meta) sin depender del plugin. */
function feelolab_field( string $name, ?int $post_id = null ) {
	if ( function_exists( 'feelo_field' ) ) {
		return feelo_field( $name, $post_id );
	}
	return get_post_meta( $post_id ?? get_the_ID(), $name, true );
}

/**
 * Nivel del título de una tarjeta. En archivos y búsqueda la tarjeta cuelga directo del h1
 * (va h2); dentro de una sección con su h2 (home, relacionados) va h3.
 *
 * @param array<string, mixed> $args Args del template part.
 */
function feelolab_card_heading( array $args ): string {
	return ( $args['heading'] ?? 'h3' ) === 'h2' ? 'h2' : 'h3';
}

/** "feelo_servicio" → "servicio": sufijo para template-parts por tipo de contenido. */
function feelolab_type_slug( ?string $post_type = null ): string {
	$post_type = $post_type ?? (string) get_post_type();
	return str_starts_with( $post_type, 'feelo_' ) ? substr( $post_type, 6 ) : $post_type;
}

/**
 * Destino por defecto de una llamada a la acción: WhatsApp si está cargado, si no la página
 * de contacto (la que usa la plantilla "Contacto"), si no el ancla #contacto de la home.
 */
function feelolab_contact_url(): string {
	static $url = null;
	if ( null !== $url ) {
		return $url;
	}
	$url = feelolab_contact_url_uncached();
	return $url;
}

function feelolab_contact_url_uncached(): string {
	if ( function_exists( 'feelo_whatsapp_url' ) ) {
		$wa = feelo_whatsapp_url();
		if ( $wa ) {
			return $wa;
		}
	}
	return feelolab_contact_page_url();
}

/** Página con la plantilla Contacto, o el formulario de la home si no hay. Nunca WhatsApp. */
function feelolab_contact_page_url(): string {
	static $url = null;
	if ( null !== $url ) {
		return $url;
	}
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- una fila, cacheada por el object cache.
			'meta_value'     => 'page-templates/contacto.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$url   = $pages ? (string) get_permalink( $pages[0] ) : home_url( '/#contacto' );
	return $url;
}

/** ¿El link abre fuera del sitio? Entonces se avisa (a11y) y se agrega rel. */
function feelolab_link_attrs( string $url ): string {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host ) {
		return ' target="_blank" rel="noopener"';
	}
	return '';
}

function feelolab_external_hint( string $url ): string {
	return feelolab_link_attrs( $url ) ? '<span class="screen-reader-text"> ' . esc_html__( '(se abre en otra pestaña)', 'feelolab' ) . '</span>' : '';
}

/** Botón con link. $variant: primary | secondary | ghost. */
function feelolab_button( string $text, string $url, string $variant = 'primary' ): string {
	if ( '' === trim( $text ) || '' === trim( $url ) ) {
		return '';
	}
	return sprintf(
		'<a class="btn btn--%1$s" href="%2$s"%3$s>%4$s%5$s</a>',
		esc_attr( $variant ),
		esc_url( $url ),
		feelolab_link_attrs( $url ),
		esc_html( $text ),
		feelolab_external_hint( $url )
	);
}

/** Encabezado de sección: volanta opcional, h2 y bajada. */
function feelolab_section_header( string $title, string $text = '', string $id = '' ): void {
	if ( '' === $title && '' === $text ) {
		return;
	}
	echo '<header class="section__header">';
	if ( $title ) {
		printf( '<h2 class="section__title"%s>%s</h2>', $id ? ' id="' . esc_attr( $id ) . '"' : '', esc_html( $title ) );
	}
	if ( $text ) {
		echo '<p class="section__lead">' . esc_html( $text ) . '</p>';
	}
	echo '</header>';
}

function feelolab_posted_on(): void {
	$reading = 'post' === get_post_type() && get_theme_mod( 'feelolab_blog_reading_time', true )
		? ' · ' . esc_html(
			/* translators: %d: minutos */
			sprintf( _n( '%d minuto de lectura', '%d minutos de lectura', feelolab_reading_time(), 'feelolab' ), feelolab_reading_time() )
		)
		: '';
	printf(
		'<p class="entry-meta"><time datetime="%1$s">%2$s</time>%3$s%4$s</p>',
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		get_the_modified_date( 'Y-m-d' ) !== get_the_date( 'Y-m-d' )
			? ' · ' . esc_html__( 'Actualizado', 'feelolab' ) . ' <time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . esc_html( get_the_modified_date() ) . '</time>'
			: '',
		$reading // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba.
	);
}

function feelolab_pagination(): void {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => __( 'Anterior', 'feelolab' ),
			'next_text'          => __( 'Siguiente', 'feelolab' ),
			'before_page_number' => '<span class="screen-reader-text">' . __( 'Página', 'feelolab' ) . ' </span>',
			'aria_label'         => __( 'Paginación', 'feelolab' ),
		)
	);
}

/** Estrellas de calificación con texto accesible ("4 de 5"). */
function feelolab_stars( int $rating ): string {
	$rating = max( 0, min( 5, $rating ) );
	if ( ! $rating ) {
		return '';
	}
	$out = '<p class="stars" role="img" aria-label="' . esc_attr(
		/* translators: %d: calificación */
		sprintf( __( 'Calificación: %d de 5', 'feelolab' ), $rating )
	) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= feelolab_icon( 'star', $i <= $rating ? 'is-on' : 'is-off' );
	}
	return $out . '</p>';
}

/** Redes del negocio como lista de íconos. */
function feelolab_socials(): void {
	if ( ! function_exists( 'feelo_socials' ) ) {
		return;
	}
	$socials = feelo_socials();
	if ( ! $socials ) {
		return;
	}
	echo '<ul class="socials">';
	foreach ( $socials as $key => $social ) {
		printf(
			'<li><a href="%1$s" target="_blank" rel="noopener me">%2$s<span class="screen-reader-text">%3$s</span></a></li>',
			esc_url( $social['url'] ),
			feelolab_icon( $key ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG fijo del tema.
			/* translators: %s: red social */
			esc_html( sprintf( __( '%s (se abre en otra pestaña)', 'feelolab' ), $social['label'] ) )
		);
	}
	echo '</ul>';
}

/** Datos de contacto del negocio (footer, página de contacto). */
function feelolab_contact_list(): void {
	$phone   = (string) feelolab_setting( 'telefono' );
	$wa      = function_exists( 'feelo_whatsapp_url' ) ? feelo_whatsapp_url() : '';
	$email   = (string) feelolab_setting( 'email' );
	$address = trim( implode( ', ', array_filter( array( feelolab_setting( 'direccion' ), feelolab_setting( 'ciudad' ) ) ) ) );
	$map     = (string) feelolab_setting( 'mapa_url' );
	$hours   = function_exists( 'feelo_opening_hours' ) ? feelo_opening_hours() : array();

	if ( ! $phone && ! $wa && ! $email && ! $address && ! $hours ) {
		return;
	}
	echo '<ul class="contact-list">';
	if ( $phone ) {
		printf( '<li>%s<a href="%s">%s</a></li>', feelolab_icon( 'phone' ), esc_url( feelo_tel_href( $phone ) ), esc_html( $phone ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( $wa ) {
		printf( '<li>%s<a href="%s" target="_blank" rel="noopener">%s<span class="screen-reader-text"> %s</span></a></li>', feelolab_icon( 'whatsapp' ), esc_url( $wa ), esc_html( (string) feelolab_setting( 'whatsapp' ) ), esc_html__( '(WhatsApp, se abre en otra pestaña)', 'feelolab' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( $email ) {
		printf( '<li>%s<a href="%s">%s</a></li>', feelolab_icon( 'mail' ), esc_url( 'mailto:' . antispambot( $email ) ), esc_html( antispambot( $email ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( $address ) {
		echo '<li>' . feelolab_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $map
			? '<a href="' . esc_url( $map ) . '" target="_blank" rel="noopener">' . esc_html( $address ) . '<span class="screen-reader-text"> ' . esc_html__( '(ver en el mapa, se abre en otra pestaña)', 'feelolab' ) . '</span></a>'
			: '<address>' . esc_html( $address ) . '</address>';
		echo '</li>';
	}
	if ( $hours ) {
		echo '<li>' . feelolab_icon( 'clock' ) . '<span>' . implode( '<br>', array_map( static fn( $h ) => esc_html( $h['text'] ), $hours ) ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</ul>';
}

/**
 * Registra FAQs para el schema FAQPage (lo arma el plugin al final de la página).
 *
 * @param int[] $ids IDs.
 */
function feelolab_schema_faqs( array $ids ): void {
	if ( class_exists( 'Feelo\\Core\\Schema\\Schema' ) ) {
		Feelo\Core\Schema\Schema::add_faqs( $ids );
	}
}

/**
 * Lista de preguntas frecuentes con <details>: acordeón nativo, accesible y sin JS.
 * La pregunta va como texto, no como título: <summary> es un botón y un h3 adentro
 * pierde su semántica en varios lectores de pantalla.
 *
 * @param WP_Post[] $faqs Preguntas.
 */
function feelolab_faq_list( array $faqs ): void {
	if ( ! $faqs ) {
		return;
	}
	echo '<div class="faq">';
	foreach ( $faqs as $faq ) {
		printf(
			'<details class="faq__item"><summary><span class="faq__q">%1$s</span>%2$s</summary><div class="faq__a">%3$s</div></details>',
			esc_html( get_the_title( $faq ) ),
			feelolab_icon( 'chevron-down', 'faq__icon' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_kses_post( apply_filters( 'the_content', $faq->post_content ) ) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro de core.
		);
	}
	echo '</div>';
	feelolab_schema_faqs( wp_list_pluck( $faqs, 'ID' ) );
}

/** Diseño del encabezado: clasico (logo a la izquierda) o centrado. */
function feelolab_header_layout(): string {
	return 'centrado' === get_theme_mod( 'feelolab_header_layout', 'clasico' ) ? 'centrado' : 'clasico';
}

/** Minutos de lectura de la nota actual, a 200 palabras por minuto (mínimo 1). */
function feelolab_reading_time( ?int $post_id = null ): int {
	$text  = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ?? get_the_ID() ) ) );
	$words = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Mapa con fachada: se ve un recuadro con la dirección y un botón "Ver mapa". El iframe de Google
 * Maps se carga recién al tocarlo: no suma peso a la página (un mapa embebido pesa ~1 MB de JS) ni
 * le manda datos a Google sin que la persona lo pida. Sin JS queda el link a Google Maps.
 * Usa el embed público de Google Maps por dirección: no hace falta clave de API.
 */
function feelolab_map( string $address, string $link = '' ): void {
	$address = trim( $address );
	if ( '' === $address ) {
		return;
	}
	$embed = 'https://www.google.com/maps?q=' . rawurlencode( $address ) . '&output=embed';
	$link  = $link ? $link : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $address );
	?>
	<div class="map-facade" data-feelo-map="<?php echo esc_url( $embed ); ?>" data-map-title="<?php echo esc_attr( sprintf( /* translators: %s: dirección */ __( 'Mapa: %s', 'feelolab' ), $address ) ); ?>">
		<div class="map-facade__inner">
			<?php echo feelolab_icon( 'map-pin', 'map-facade__pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p class="map-facade__address"><?php echo esc_html( $address ); ?></p>
			<div class="map-facade__actions">
				<button type="button" class="btn btn--primary map-facade__load" hidden><?php esc_html_e( 'Ver mapa', 'feelolab' ); ?></button>
				<a class="btn btn--secondary" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir en Google Maps', 'feelolab' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(se abre en otra pestaña)', 'feelolab' ); ?></span></a>
			</div>
			<p class="map-facade__note"><?php esc_html_e( 'El mapa lo muestra Google: se carga solo si lo pedís.', 'feelolab' ); ?></p>
		</div>
	</div>
	<?php
}

/** Dirección completa del negocio (Ajustes del sitio), para el mapa. */
function feelolab_business_address(): string {
	return trim( implode( ', ', array_filter( array( feelolab_setting( 'direccion' ), feelolab_setting( 'ciudad' ), feelolab_setting( 'provincia' ), feelolab_setting( 'pais' ) ) ) ) );
}
