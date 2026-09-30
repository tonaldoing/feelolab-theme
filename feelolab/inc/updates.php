<?php
/**
 * Actualizaciones del tema cuando el plugin FeeloLab Core no está activo.
 *
 * El actualizador completo vive en el plugin (tema y plugin juntos, changelog, estado en el panel).
 * Si alguien desactiva el plugin, el tema igual tiene que seguir recibiendo arreglos: este respaldo
 * lee el mismo update.json público y avisa en Escritorio → Actualizaciones. Con el plugin activo
 * no hace nada, para no consultar dos veces.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'pre_set_site_transient_update_themes',
	static function ( $transient ) {
		if ( ! is_object( $transient ) || class_exists( 'Feelo\\Core\\Updater' ) ) {
			return $transient;
		}
		$release = get_site_transient( 'feelolab_theme_release' );
		if ( false === $release ) {
			$repo     = defined( 'FEELO_UPDATE_REPO' ) ? (string) FEELO_UPDATE_REPO : 'tonaldoing/feelolab-releases';
			$settings = (array) get_option( 'feelo_settings', array() );
			$path     = empty( $settings['canal_beta'] ) ? 'latest/download' : 'download/canal-beta';
			$response = wp_remote_get( 'https://github.com/' . $repo . '/releases/' . $path . '/update.json', array( 'timeout' => 10 ) );
			$data     = ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ? json_decode( wp_remote_retrieve_body( $response ), true ) : null;
			$release  = is_array( $data ) && ! empty( $data['version'] ) && ! empty( $data['theme'] )
				? array(
					'version' => ltrim( (string) $data['version'], 'vV' ),
					'package' => esc_url_raw( (string) $data['theme'] ),
				)
				: array();
			if ( ! $release ) {
				error_log( 'feelolab: no se pudo leer update.json: ' . ( is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $response ) ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			set_site_transient( 'feelolab_theme_release', $release, HOUR_IN_SECONDS );
		}
		$item = array(
			'theme'       => 'feelolab',
			'new_version' => FEELOLAB_VERSION,
			'url'         => 'https://www.feelolab.com',
			'package'     => '',
		);
		if ( $release && version_compare( $release['version'], FEELOLAB_VERSION, '>' ) ) {
			$item['new_version']             = $release['version'];
			$item['package']                 = $release['package'];
			$transient->response['feelolab'] = $item;
			unset( $transient->no_update['feelolab'] );
		} else {
			$transient->no_update['feelolab'] = $item;
		}
		return $transient;
	}
);

add_action(
	'upgrader_process_complete',
	static function (): void {
		delete_site_transient( 'feelolab_theme_release' );
	}
);
