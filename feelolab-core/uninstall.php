<?php
/**
 * Desinstalación (al borrar el plugin desde Plugins, no al desactivarlo).
 *
 * Se borra siempre lo técnico: ajustes, módulos, cachés, tareas programadas y el aviso de privacidad.
 * Los mensajes y suscripciones, solo si en Ajustes del sitio → Formulario se tildó "borrar también
 * los mensajes". El contenido (servicios, productos, equipo…) nunca se borra: es del cliente y
 * sobrevive a un cambio de plugin o de tema.
 *
 * @package Feelo\Core
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$feelo_settings = (array) get_option( 'feelo_settings', array() );

if ( ! empty( $feelo_settings['borrar_al_desinstalar'] ) ) {
	do {
		$feelo_ids = get_posts(
			array(
				'post_type'      => 'feelo_mensaje',
				'post_status'    => 'any',
				'posts_per_page' => 100,
				'fields'         => 'ids',
			)
		);
		foreach ( $feelo_ids as $feelo_id ) {
			wp_delete_post( $feelo_id, true );
		}
		$feelo_more = 100 === count( $feelo_ids );
	} while ( $feelo_more );
}

foreach ( array( 'feelo_settings', 'feelo_modules' ) as $feelo_option ) {
	delete_option( $feelo_option );
}
delete_transient( 'feelo_update_release' );
delete_transient( 'feelo_llms_txt' );
delete_site_transient( 'feelo_update_release' );

// Cachés con clave variable (límite por IP, estado de formularios, importaciones): por prefijo.
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- limpieza única al desinstalar.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_feelo\\_%' OR option_name LIKE '\\_transient\\_timeout\\_feelo\\_%' OR option_name LIKE 'feelo\\_import\\_%'" );

wp_clear_scheduled_hook( 'feelo_privacy_cleanup' );
