<?php
/**
 * Contenido demo para desarrollo y pruebas (lo ejecuta blueprint.json en Playground).
 * NO se incluye en el tema ni en el plugin.
 */

require_once '/wordpress/wp-load.php';

update_option( 'feelo_modules', array( 'servicios', 'productos', 'testimonios', 'faq', 'sedes', 'clientes', 'equipo', 'proyectos' ) );
Feelo\Core\Modules\Registry::register_all();

update_option(
	'feelo_settings',
	array(
		'nombre_comercial'  => 'Estudio Demo',
		'tipo_negocio'      => 'ProfessionalService',
		'descripcion'       => 'Estudio contable y de asesoramiento para pymes en Buenos Aires.',
		'moneda'            => 'ARS',
		'telefono'          => '+54 11 4000-0000',
		'whatsapp'          => '+54 9 11 5000-0000',
		'whatsapp_mensaje'  => 'Hola, quiero hacer una consulta',
		'whatsapp_flotante' => 1,
		'email'             => 'hola@example.com',
		'direccion'         => 'Av. Corrientes 1234',
		'ciudad'            => 'Buenos Aires',
		'provincia'         => 'CABA',
		'codigo_postal'     => 'C1043',
		'pais'              => 'AR',
		'horarios'          => "Lu-Vi 09:00-18:00\nSa 10:00-13:00",
		'instagram'         => 'https://instagram.com/example',
		'linkedin'          => 'https://linkedin.com/company/example',
		'razon_social'      => 'Estudio Demo SRL',
		'id_fiscal'         => '30-00000000-0',
	)
);

function feelo_demo_post( string $type, string $title, string $content, array $meta = array(), int $order = 0 ): int {
	$id = wp_insert_post(
		array(
			'post_type'    => $type,
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => wp_trim_words( wp_strip_all_tags( str_replace( '<', ' <', $content ) ), 20 ),
			'menu_order'   => $order,
			'meta_input'   => $meta,
		)
	);
	return (int) $id;
}

$faqs = array();
foreach ( array(
	'¿Trabajan con monotributistas?' => 'Sí. Llevamos la categorización, las recategorizaciones y los pagos mensuales.',
	'¿Cuánto tarda una consulta?'    => 'Respondemos en el día hábil. Las consultas complejas se agendan en 48 horas.',
	'¿Atienden a distancia?'         => 'Sí, todo el proceso puede hacerse en línea con firma digital.',
) as $q => $a ) {
	$faqs[] = feelo_demo_post( 'feelo_faq', $q, '<p>' . $a . '</p>' );
}

$cat = wp_insert_term( 'Impuestos', 'feelo_servicio_cat' );
foreach ( array(
	array( 'Liquidación de impuestos', 'Presentamos tus declaraciones juradas a tiempo y sin sorpresas.', '$ 25.000' ),
	array( 'Sueldos y cargas sociales', 'Liquidamos sueldos, cargas y libros laborales de tu equipo.', '$ 40.000' ),
	array( 'Asesoramiento societario', 'Constitución de sociedades, actas y trámites ante IGJ.', '' ),
) as $i => $s ) {
	$id = feelo_demo_post( 'feelo_servicio', $s[0], '<p>' . $s[1] . '</p><h2>Qué incluye</h2><ul><li>Análisis inicial</li><li>Presentaciones mensuales</li><li>Reporte de situación</li></ul>', array( 'bajada' => $s[1], 'precio_desde' => $s[2], 'faqs' => $faqs ), $i );
	if ( ! is_wp_error( $cat ) ) {
		wp_set_object_terms( $id, $cat['term_id'], 'feelo_servicio_cat' );
	}
}

foreach ( array(
	array( 'Laura Gómez', 'Dueña de Panadería Sol', 'Nos ordenaron los números en dos meses. Ahora sabemos cuánto ganamos de verdad.', 5 ),
	array( 'Martín Ruiz', 'Socio de Ruiz & Asociados', 'Responden rápido y explican todo sin vueltas.', 5 ),
	array( 'Ana Pérez', 'Diseñadora independiente', 'Me pasé a monotributo con ellos y fue muy simple.', 4 ),
) as $i => $t ) {
	feelo_demo_post( 'feelo_testimonio', $t[0], '<p>' . $t[2] . '</p>', array( 'autor' => $t[0], 'cargo' => $t[1], 'estrellas' => $t[3] ), $i );
}

/** Imagen demo: un PNG liso generado con GD, con su texto alternativo. */
function feelo_demo_image( string $name, string $alt, int $w, int $h, array $rgb ): int {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$upload = wp_upload_dir();
	$file   = trailingslashit( $upload['path'] ) . $name . '.png';
	$img    = imagecreatetruecolor( $w, $h );
	imagefill( $img, 0, 0, imagecolorallocate( $img, ...$rgb ) );
	imagepng( $img, $file );
	$id = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => $alt, 'post_status' => 'inherit' ), $file );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

$gallery = array(
	feelo_demo_image( 'guia-2', 'Índice de la guía', 1200, 1200, array( 214, 222, 255 ) ),
	feelo_demo_image( 'guia-3', 'Ejemplo de factura completa', 1200, 1200, array( 255, 228, 214 ) ),
);
$hero = feelo_demo_image( 'hero', 'Equipo del estudio trabajando', 1600, 900, array( 200, 210, 230 ) );
set_theme_mod( 'feelolab_home_hero_image', $hero );

$product = feelo_demo_post( 'feelo_producto', 'Guía de facturación electrónica', '<p>Manual paso a paso para facturar sin errores.</p>', array( 'precio' => '$ 12.000', 'precio_oferta' => '$ 9.900', 'sku' => 'GUIA-01', 'disponible' => 1, 'ficha_tecnica' => "Formato: PDF\nPáginas: 48\nActualizada: 2026", 'consulta_whatsapp' => 1 ) );
set_post_thumbnail( $product, feelo_demo_image( 'guia-1', 'Tapa de la guía de facturación', 1200, 1200, array( 36, 71, 216 ) ) );
update_post_meta( $product, '_feelo_gallery', $gallery );
feelo_demo_post( 'feelo_sede', 'Sede Centro', '<p>A dos cuadras del Obelisco.</p>', array( 'direccion' => 'Av. Corrientes 1234', 'ciudad' => 'Buenos Aires', 'telefono' => '+54 11 4000-0000', 'horarios' => 'Lu-Vi 09:00-18:00' ) );
feelo_demo_post( 'feelo_miembro', 'Carolina Díaz', '<p>Contadora pública (UBA) con 15 años de experiencia en pymes.</p>', array( 'cargo' => 'Socia fundadora' ) );
feelo_demo_post( 'post', 'Cinco vencimientos que no te podés olvidar', '<p>Un repaso por las fechas clave del año fiscal.</p><h2>1. Ganancias</h2><p>Texto de ejemplo.</p>' );

$privacy = feelo_demo_post( 'page', 'Política de privacidad', '<p>Texto de ejemplo de la política de privacidad.</p>' );
update_option( 'wp_page_for_privacy_policy', $privacy );
$contact = feelo_demo_post( 'page', 'Contacto', '<p>Escribinos y te respondemos en el día.</p>' );
update_post_meta( $contact, '_wp_page_template', 'page-templates/contacto.php' );
$home = feelo_demo_post( 'page', 'Inicio', '' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );

$menu_id = wp_create_nav_menu( 'Principal' );
if ( ! is_wp_error( $menu_id ) ) {
	$serv_item = wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'Servicios', 'menu-item-url' => get_post_type_archive_link( 'feelo_servicio' ), 'menu-item-status' => 'publish', 'menu-item-type' => 'custom' ) );
	wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'Impuestos', 'menu-item-url' => home_url( '/categoria-servicio/impuestos/' ), 'menu-item-status' => 'publish', 'menu-item-type' => 'custom', 'menu-item-parent-id' => $serv_item ) );
	wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'Productos', 'menu-item-url' => get_post_type_archive_link( 'feelo_producto' ), 'menu-item-status' => 'publish', 'menu-item-type' => 'custom' ) );
	wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-object-id' => $contact, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
	set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id ) );
}

set_theme_mod( 'feelolab_header_cta_text', 'Pedí tu presupuesto' );
set_theme_mod( 'feelolab_font_pair', 'fraunces' ); // La combinación más pesada (2 archivos): el peor caso para medir.
set_theme_mod( 'feelolab_home_nosotros_text', "Somos un estudio de contadores que habla claro.\n\nAcompañamos a más de 200 pymes con atención personalizada." );
set_theme_mod( 'feelolab_home_cifras_show', true );
set_theme_mod( 'feelolab_home_cifras_value_2', '+200' );
set_theme_mod( 'feelolab_home_cifras_label_2', 'pymes acompañadas' );
set_theme_mod( 'feelolab_home_cifras_value_3', '24 h' );
set_theme_mod( 'feelolab_home_cifras_label_3', 'tiempo de respuesta' );

flush_rewrite_rules();
echo "demo ok\n";
