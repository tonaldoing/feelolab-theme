<?php
/**
 * Solo desarrollo: prende las opciones de diseño del grupo 3. /dev/set-design.php?caso=todo|base
 */
require_once '/wordpress/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$caso = sanitize_key( $_GET['caso'] ?? 'todo' );
$on   = 'todo' === $caso;
$mods = array(
	'feelolab_home_hero_layout'     => $on ? 'fondo' : 'dividida',
	'feelolab_header_transparent'   => $on,
	'feelolab_header_layout'        => $on ? 'centrado' : 'clasico',
	'feelolab_header_search'        => $on,
	'feelolab_topbar'               => $on,
	'feelolab_topbar_text'          => $on ? 'Atención de lunes a viernes de 9 a 18' : '',
	'feelolab_footer_style'         => $on ? 'claro' : 'marca',
	'feelolab_home_cta_2_show'      => $on,
	'feelolab_home_nosotros_2_show' => $on,
	'feelolab_home_nosotros_2_text' => $on ? 'Primero escuchamos, después proponemos un plan claro con costos cerrados.' : '',
	'feelolab_home_nosotros_2_image' => $on ? (int) get_theme_mod( 'feelolab_home_hero_image' ) : 0,
);
foreach ( $mods as $k => $v ) {
	set_theme_mod( $k, $v );
}
wp_update_user( array( 'ID' => 1, 'description' => $on ? 'Contadora pública con 15 años de experiencia en pymes y monotributistas.' : '' ) );
// Las notas demo se crean sin autor (el blueprint corre sin sesión): se asignan al admin.
foreach ( get_posts( array( 'post_type' => 'post', 'numberposts' => -1, 'fields' => 'ids' ) ) as $post_id ) {
	wp_update_post( array( 'ID' => $post_id, 'post_author' => 1 ) );
}
// Un widget de texto en el pie.
$widgets = get_option( 'sidebars_widgets', array() );
if ( $on ) {
	update_option( 'widget_text', array( 2 => array( 'title' => 'Horarios', 'text' => 'Lunes a viernes de 9 a 18.', 'filter' => true, 'visual' => true ), '_multiwidget' => 1 ) );
	$widgets['footer'] = array( 'text-2' );
} else {
	$widgets['footer'] = array();
}
update_option( 'sidebars_widgets', $widgets );
echo "ok $caso\n";
