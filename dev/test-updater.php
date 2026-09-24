<?php
/**
 * Prueba del actualizador (solo desarrollo): /dev/test-updater.php en Playground.
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/update.php';
header( 'Content-Type: text/plain; charset=utf-8' );

use Feelo\Core\Updater;

// 1) Release simulado 9.9.9.
$fake = array(
	'version' => '9.9.9',
	'url'     => 'https://github.com/x/y/releases/tag/v9.9.9',
	'notes'   => "- Cambio A\n- Cambio B",
	'date'    => '2026-09-24T00:00:00Z',
	'theme'   => 'https://example.com/feelolab.zip',
	'plugin'  => 'https://example.com/feelolab-core.zip',
);
add_filter( 'feelo_update_pre_release', static fn() => $fake );
delete_site_transient( 'update_plugins' );
delete_site_transient( 'update_themes' );
wp_update_plugins();
wp_update_themes();
$p = get_site_transient( 'update_plugins' )->response['feelolab-core/feelolab-core.php'] ?? null;
$t = get_site_transient( 'update_themes' )->response['feelolab'] ?? null;
echo '1 plugin: ', $p ? $p->new_version . ' ' . $p->package : 'NO', "\n";
echo '1 tema:   ', $t ? $t['new_version'] . ' ' . $t['package'] : 'NO', "\n";
$info = Updater::plugin_info( false, 'plugin_information', (object) array( 'slug' => 'feelolab-core' ) );
echo '1 detalles: ', $info->version, ' | ', trim( wp_strip_all_tags( $info->sections['changelog'] ) ), "\n";

// 2) Misma versión instalada → no hay update, pero queda en no_update (habilita auto-updates).
remove_all_filters( 'feelo_update_pre_release' );
add_filter( 'feelo_update_pre_release', static fn() => array_merge( $fake, array( 'version' => FEELO_CORE_VERSION ) ) );
delete_site_transient( 'update_plugins' );
wp_update_plugins();
$tr = get_site_transient( 'update_plugins' );
echo '2 sin update: ', isset( $tr->response['feelolab-core/feelolab-core.php'] ) ? 'MAL' : 'ok', ' · en no_update: ', isset( $tr->no_update['feelolab-core/feelolab-core.php'] ) ? 'ok' : 'MAL', "\n";

// 3) GitHub real sin token (repo privado → 404).
remove_all_filters( 'feelo_update_pre_release' );
Updater::flush();
var_dump( Updater::release() );
echo '3 estado: ', Updater::status(), "\n";

// 4) El token solo se agrega a adjuntos de nuestro repo y se quita al redirigir fuera de GitHub.
define( 'FEELO_GITHUB_TOKEN', 'github_pat_TEST' );
$ours   = Updater::auth_download( array(), 'https://api.github.com/repos/' . Updater::repo() . '/releases/assets/123' );
$others = Updater::auth_download( array(), 'https://api.github.com/repos/otro/repo/releases/assets/123' );
echo '4 nuestro: ', $ours['headers']['Authorization'] ?? 'NO', ' · ', $ours['headers']['Accept'] ?? '', "\n";
echo '4 otro repo: ', isset( $others['headers']['Authorization'] ) ? 'MAL (filtra el token)' : 'ok, sin token', "\n";
$loc     = 'https://objects.githubusercontent.com/github-production-release-asset/abc';
$headers = array( 'Authorization' => 'Bearer x', 'Accept' => 'application/octet-stream' );
$data    = array();
$opts    = array();
do_action_ref_array( 'requests-requests.before_redirect', array( &$loc, &$headers, &$data, &$opts, null ) );
echo '4 tras redirect a S3: ', isset( $headers['Authorization'] ) ? 'MAL' : 'ok, token quitado', "\n";
