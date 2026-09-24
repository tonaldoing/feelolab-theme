<?php
/**
 * Solo desarrollo: aplica una combinación de colores de prueba. /dev/set-colors.php?caso=blanco
 */
require_once '/wordpress/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
$casos = array(
	'blanco' => array( 'primary' => '#ffffff', 'secondary' => '#0075ad', 'bg' => '#ffffff', 'surface' => '#f4f4f5', 'text' => '#000000', 'button_text' => '', 'link' => '' ),
	'normal' => array( 'primary' => '#2447d8', 'secondary' => '#0f172a', 'bg' => '#ffffff', 'surface' => '#f4f4f5', 'text' => '#1f2937', 'button_text' => '', 'link' => '' ),
);
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$caso = $casos[ sanitize_key( $_GET['caso'] ?? 'blanco' ) ] ?? $casos['blanco'];
foreach ( $caso as $k => $v ) {
	set_theme_mod( 'feelolab_color_' . $k, $v );
}
echo "ok\n";
