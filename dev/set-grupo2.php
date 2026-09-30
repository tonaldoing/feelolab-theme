<?php
/**
 * Solo desarrollo: carga opciones de producto, mapa, newsletter, una página con todos los patrones
 * y una fuente propia. /dev/set-grupo2.php?caso=todo|base
 */
require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
header( 'Content-Type: text/plain; charset=utf-8' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$on = 'base' !== sanitize_key( $_GET['caso'] ?? 'todo' );

// Producto con opciones.
$product = get_posts( array( 'post_type' => 'feelo_producto', 'numberposts' => 1 ) )[0] ?? null;
if ( $product ) {
	update_field( 'opciones', $on ? "Plataforma: Acrílico | Metálica | Flexible\nAltura: 2 cm | 3 cm | 4 cm" : '', $product->ID );
	echo 'producto: ' . get_permalink( $product ) . "\n";
}

// Mapa en contacto y newsletter en la home.
$settings                   = (array) get_option( 'feelo_settings', array() );
$settings['mapa_mostrar']   = $on ? '1' : '';
$settings['news_proveedor'] = '';
update_option( 'feelo_settings', $settings );
set_theme_mod( 'feelolab_home_newsletter_show', $on );

// Página con todos los patrones.
$content = '';
foreach ( WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $pattern ) {
	if ( str_starts_with( $pattern['name'], 'feelolab/' ) && 'feelolab/landing' !== $pattern['name'] ) {
		$content .= $pattern['content'] . "\n\n";
	}
}
$page = get_page_by_path( 'patrones' );
$args = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Patrones', 'post_name' => 'patrones', 'post_content' => $content );
if ( $page ) {
	$args['ID'] = $page->ID;
}
$page_id = wp_insert_post( $args );
echo 'patrones: ' . get_permalink( $page_id ) . "\n";
$landing = get_page_by_path( 'landing' );
$largs   = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Landing', 'post_name' => 'landing', 'post_content' => WP_Block_Patterns_Registry::get_instance()->get_registered( 'feelolab/landing' )['content'] ?? '', 'page_template' => 'page-templates/ancho-completo.php' );
if ( $landing ) {
	$largs['ID'] = $landing->ID;
}
$lid = wp_insert_post( $largs );
update_post_meta( $lid, '_wp_page_template', 'page-templates/ancho-completo.php' );
echo 'landing: ' . get_permalink( $lid ) . "\n";

// Fuente propia: Fraunces del tema como "títulos" subida a la biblioteca.
$font_id = (int) get_option( 'feelo_dev_font_id' );
if ( ! $font_id || ! get_post( $font_id ) ) {
	$upload = wp_upload_dir();
	$file   = trailingslashit( $upload['path'] ) . 'marca-titulos.woff2';
	copy( get_theme_file_path( 'assets/fonts/fraunces.woff2' ), $file );
	$font_id = wp_insert_attachment( array( 'post_mime_type' => 'font/woff2', 'post_title' => 'Marca títulos', 'post_status' => 'inherit' ), $file );
	update_option( 'feelo_dev_font_id', $font_id );
}
set_theme_mod( 'feelolab_font_own_heading', $on ? $font_id : 0 );
set_theme_mod( 'feelolab_font_pair', $on ? 'propia' : 'sistema' );
echo "ok\n";
