<?php
/**
 * Feelolab: solo carga los módulos de /inc.
 *
 * El contenido (CPTs, ajustes del negocio, schema, formulario) vive en el plugin Feelolab Core.
 * El tema llama a sus funciones siempre detrás de function_exists(): sin el plugin, el sitio anda.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

define( 'FEELOLAB_VERSION', '0.9.0' );
define( 'FEELOLAB_DIR', get_template_directory() );
define( 'FEELOLAB_URI', get_template_directory_uri() );

foreach ( array( 'setup', 'performance', 'fonts', 'colors', 'customizer', 'assets', 'nav', 'template-tags', 'breadcrumbs', 'home', 'patterns' ) as $feelolab_file ) {
	require FEELOLAB_DIR . '/inc/' . $feelolab_file . '.php';
}
// Actualizaciones desde los releases de FeeloLab. No va en la versión de wordpress.org.
if ( is_readable( FEELOLAB_DIR . '/inc/updates.php' ) ) {
	require FEELOLAB_DIR . '/inc/updates.php';
}
if ( class_exists( 'WooCommerce' ) ) {
	require FEELOLAB_DIR . '/inc/woocommerce.php';
}
unset( $feelolab_file );
