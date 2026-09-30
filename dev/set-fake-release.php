<?php
/**
 * Solo desarrollo: simula un release con versiones anteriores (GitHub no es accesible desde Playground).
 * /dev/set-fake-release.php?on=1 crea un mu-plugin; ?on=0 lo borra.
 */
require_once '/wordpress/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
$file = WPMU_PLUGIN_DIR . '/feelo-fake-release.php';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( empty( $_GET['on'] ) ) {
	wp_delete_file( $file );
	echo "off\n";
	return;
}
wp_mkdir_p( WPMU_PLUGIN_DIR );
file_put_contents( $file, "<?php\nadd_filter( 'feelo_update_pre_release', static fn() => array( 'version' => '0.8.0', 'url' => '', 'date' => '', 'notes' => \"- Nueva pantalla Versiones.\\n- Asistente de primeros pasos.\", 'theme' => '', 'plugin' => '', 'versions' => array( '0.8.0', '0.7.0', '0.6.0', '0.5.0' ) ) );\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
echo "on\n";
