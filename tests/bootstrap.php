<?php
/**
 * Tests de lógica pura, sin WordPress: se imitan las pocas funciones de WordPress que usan los
 * archivos probados. Lo que depende de la base de datos o del navegador se prueba en Playground
 * (axe, Lighthouse); acá va lo que puede romperse sin que se vea: contraste, orden, parseos.
 *
 * @package Feelolab
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'FEELOLAB_DIR', dirname( __DIR__ ) . '/feelolab' );
define( 'FEELOLAB_URI', 'https://example.test/wp-content/themes/feelolab' );
define( 'FEELOLAB_VERSION', 'test' );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'MINUTE_IN_SECONDS', 60 );

$GLOBALS['feelo_test_mods']    = array();
$GLOBALS['feelo_test_modules'] = array();

function get_theme_mod( $name, $fallback = false ) {
	return array_key_exists( $name, $GLOBALS['feelo_test_mods'] ) ? $GLOBALS['feelo_test_mods'][ $name ] : $fallback;
}
function apply_filters( $hook, $value ) {
	return $value;
}
function add_filter() {}
function add_action() {}
function __( $text ) {
	return $text;
}
function _x( $text ) {
	return $text;
}
function _n( $single, $plural, $number ) {
	return 1 === (int) $number ? $single : $plural;
}
function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}
function esc_attr( $text ) {
	return esc_html( $text );
}
function esc_url( $url ) {
	return (string) $url;
}
function absint( $value ) {
	return abs( (int) $value );
}
function sanitize_hex_color( $color ) {
	return preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', (string) $color ) ? $color : null;
}
function get_bloginfo() {
	return 'Sitio de prueba';
}
function feelo_module_enabled( $module ) {
	return in_array( $module, $GLOBALS['feelo_test_modules'], true );
}
function remove_accents( $text ) {
	return strtr( $text, array( 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N', 'ü' => 'u' ) );
}
function wp_strip_all_tags( $text ) {
	return trim( strip_tags( (string) $text ) );
}
function strip_shortcodes( $text ) {
	return preg_replace( '/\[[^\]]+\]/', '', (string) $text );
}
function get_post_field( $field, $post_id ) {
	return $GLOBALS['feelo_test_post_content'] ?? '';
}
function get_the_ID() {
	return 1;
}

require FEELOLAB_DIR . '/inc/fonts.php';
require FEELOLAB_DIR . '/inc/colors.php';
require FEELOLAB_DIR . '/inc/home.php';
require FEELOLAB_DIR . '/inc/template-tags.php';

// Clases del plugin (autoload como en producción).
spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'Feelo\\Core\\';
		if ( str_starts_with( $class_name, $prefix ) ) {
			$file = dirname( __DIR__ ) . '/feelolab-core/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
			if ( is_readable( $file ) ) {
				require $file;
			}
		}
	}
);
