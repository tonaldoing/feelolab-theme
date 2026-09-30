<?php
/**
 * Solo desarrollo: activa GA4 de prueba y el banner de cookies. /dev/set-tracking.php?on=1
 */
require_once '/wordpress/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
$settings = (array) get_option( 'feelo_settings', array() );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$on                         = ! empty( $_GET['on'] );
$settings['ga4_id']         = $on ? 'G-TEST123' : '';
$settings['consent_banner'] = $on ? '1' : '';
update_option( 'feelo_settings', $settings );
echo "ok\n";
