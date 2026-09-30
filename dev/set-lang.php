<?php
/**
 * Solo desarrollo: cambia el idioma del sitio. /dev/set-lang.php?l=en_US|es_AR
 */
require_once '/wordpress/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$lang = sanitize_text_field( wp_unslash( $_GET['l'] ?? 'es_AR' ) );
update_option( 'WPLANG', 'en_US' === $lang ? '' : $lang );
flush_rewrite_rules();
echo 'ok ', get_locale(), "\n";
