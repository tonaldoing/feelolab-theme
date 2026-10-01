<?php
/**
 * Solo desarrollo: prepara el sitio de Playground para la suite e2e (tests/e2e).
 * /dev/e2e-setup.php — idempotente: se puede correr antes de cada corrida.
 *
 * - Página /e2e-newsletter/ con el formulario de newsletter.
 * - Sin límites por IP acumulados de corridas anteriores.
 * - ?wizard=pendiente|listo cambia si el asistente de primeros pasos se ofrece.
 */
require_once '/wordpress/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );

if ( ! get_page_by_path( 'e2e-newsletter' ) ) {
	wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => 'e2e-newsletter',
			'post_title'   => 'Newsletter (e2e)',
			'post_content' => '<!-- wp:shortcode -->[feelo_newsletter]<!-- /wp:shortcode -->',
		)
	);
}

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_feelo\\_rl\\_%' OR option_name LIKE '\\_transient\\_timeout\\_feelo\\_rl\\_%' OR option_name LIKE '\\_transient\\_feelo\\_nl\\_%' OR option_name LIKE '\\_transient\\_timeout\\_feelo\\_nl\\_%'" );
wp_cache_flush();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$wizard = sanitize_key( $_GET['wizard'] ?? '' );
if ( 'pendiente' === $wizard ) {
	delete_option( 'feelo_wizard_done' );
	// Sin datos de contacto el sitio cuenta como "sin configurar": se guardan para restaurarlos.
	$settings = (array) get_option( 'feelo_settings', array() );
	update_option( 'feelo_e2e_contacto', array_intersect_key( $settings, array_flip( array( 'telefono', 'whatsapp', 'email' ) ) ), false );
	update_option( 'feelo_settings', array_diff_key( $settings, array_flip( array( 'telefono', 'whatsapp', 'email' ) ) ) );
} elseif ( 'listo' === $wizard ) {
	update_option( 'feelo_settings', array_merge( (array) get_option( 'feelo_settings', array() ), (array) get_option( 'feelo_e2e_contacto', array() ) ) );
	delete_option( 'feelo_e2e_contacto' );
	update_option( 'feelo_wizard_done', 1 );
}
// ?menu=sin saca el menú principal (para probar el de respaldo); ?menu=con lo vuelve a poner.
$menu = sanitize_key( $_GET['menu'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( 'sin' === $menu ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	update_option( 'feelo_e2e_menu', (int) ( $locations['primary'] ?? 0 ), false );
	unset( $locations['primary'] );
	set_theme_mod( 'nav_menu_locations', $locations );
} elseif ( 'con' === $menu && get_option( 'feelo_e2e_menu' ) ) {
	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = (int) get_option( 'feelo_e2e_menu' );
	set_theme_mod( 'nav_menu_locations', $locations );
	delete_option( 'feelo_e2e_menu' );
}

// ?fondos=oscuro|gris|blanco prende todas las secciones de la home con ese fondo; ?fondos=normal deshace.
$bgs = sanitize_key( $_GET['fondos'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( in_array( $bgs, array( 'oscuro', 'gris', 'blanco' ), true ) && function_exists( 'feelolab_home_sections' ) && ! get_option( 'feelo_e2e_home' ) ) {
	// Se guarda cómo estaba la home para dejarla igual después.
	$backup = array();
	foreach ( feelolab_home_sections() as $key => $section ) {
		foreach ( array( 'bg', 'show' ) as $field ) {
			$backup[ "feelolab_home_{$key}_{$field}" ] = get_theme_mod( "feelolab_home_{$key}_{$field}", null );
			if ( 'show' === $field || isset( $section['fields']['bg'] ) ) {
				set_theme_mod( "feelolab_home_{$key}_{$field}", 'show' === $field ? true : $bgs );
			}
		}
	}
	update_option( 'feelo_e2e_home', $backup, false );
} elseif ( 'normal' === $bgs && get_option( 'feelo_e2e_home' ) ) {
	foreach ( (array) get_option( 'feelo_e2e_home' ) as $mod => $value ) {
		null === $value ? remove_theme_mod( $mod ) : set_theme_mod( $mod, $value );
	}
	delete_option( 'feelo_e2e_home' );
}
echo "ok\n";
