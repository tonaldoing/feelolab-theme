<?php
/**
 * Prueba del actualizador con update.json (solo desarrollo): /dev/test-updater-manifest.php.
 * Simula las respuestas de GitHub con pre_http_request.
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/update.php';
header( 'Content-Type: text/plain; charset=utf-8' );

use Feelo\Core\Updater;

$calls    = array();
$manifest = true;
add_filter(
	'pre_http_request',
	static function ( $pre, $args, $url ) use ( &$calls, &$manifest ) {
		$calls[] = $url;
		if ( str_contains( $url, '/releases/latest/download/update.json' ) ) {
			return $manifest
				? array( 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( array( 'version' => '9.1.0', 'notes' => "- A\n", 'date' => '2026-09-24T00:00:00Z', 'url' => 'https://github.com/x/y', 'theme' => 'https://github.com/x/y/releases/download/v9.1.0/feelolab.zip', 'plugin' => 'https://github.com/x/y/releases/download/v9.1.0/feelolab-core.zip' ) ) )
				: array( 'response' => array( 'code' => 404 ), 'body' => 'Not Found' );
		}
		if ( str_contains( $url, 'api.github.com' ) ) {
			return array( 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( array( 'tag_name' => 'v9.0.0', 'html_url' => 'u', 'body' => 'b', 'published_at' => 'd', 'assets' => array( array( 'name' => 'feelolab.zip', 'url' => 'api', 'browser_download_url' => 'https://github.com/x/y/t.zip' ), array( 'name' => 'feelolab-core.zip', 'url' => 'api', 'browser_download_url' => 'https://github.com/x/y/p.zip' ) ) ) ) );
		}
		return $pre;
	},
	10,
	3
);

Updater::flush();
$r = Updater::release();
echo '1 manifest: ', $r['version'] ?? 'NULL', ' | ', $r['plugin'] ?? '', ' | llamadas: ', count( $calls ), "\n";
$again = Updater::release();
echo '2 caché: ', $again['version'] ?? 'NULL', ' | llamadas: ', count( $calls ), " (sin llamada nueva)\n";

$manifest = false;
$calls    = array();
Updater::flush();
$r = Updater::release();
echo '3 sin update.json → API: ', $r['version'] ?? 'NULL', ' | ', $r['theme'] ?? '', ' | llamadas: ', implode( ', ', array_map( static fn( $u ) => wp_parse_url( $u, PHP_URL_HOST ), $calls ) ), "\n";

// 4) "Buscar de nuevo": nuestro flush corre antes que wp_update_plugins en load-update-core.php.
global $wp_filter;
$order = array();
foreach ( $wp_filter['load-update-core.php']->callbacks as $prio => $cbs ) {
	foreach ( $cbs as $cb ) {
		$name    = is_array( $cb['function'] ) ? ( is_string( $cb['function'][0] ) ? $cb['function'][0] : get_class( $cb['function'][0] ) ) . '::' . $cb['function'][1] : ( is_string( $cb['function'] ) ? $cb['function'] : 'closure' );
		$order[] = $prio . ':' . $name;
	}
}
echo '4 orden en load-update-core.php: ', implode( ' → ', $order ), "\n";
Updater::flush();
