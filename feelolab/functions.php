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

define( 'FEELOLAB_VERSION', '0.2.4' );
define( 'FEELOLAB_DIR', get_template_directory() );
define( 'FEELOLAB_URI', get_template_directory_uri() );

foreach ( array( 'setup', 'performance', 'fonts', 'colors', 'customizer', 'assets', 'nav', 'template-tags', 'breadcrumbs', 'home' ) as $feelolab_file ) {
	require FEELOLAB_DIR . '/inc/' . $feelolab_file . '.php';
}
unset( $feelolab_file );
