<?php
/**
 * Actualizaciones del tema y del plugin desde los Releases de GitHub.
 *
 * WordPress solo actualiza solo lo que viene de wordpress.org. Esto hace que el tema Feelolab y
 * este plugin aparezcan en Escritorio → Actualizaciones como cualquier otro: con botón
 * "Actualizar", changelog y la opción de actualizaciones automáticas.
 *
 * Cómo funciona:
 * - Cada versión es un Release de GitHub con tag vX.Y.Z y dos adjuntos: feelolab.zip y
 *   feelolab-core.zip (los arma .github/workflows/release.yml al pushear el tag).
 * - Se consulta el último release como máximo cada 12 horas (transient); "Buscar de nuevo" en
 *   Actualizaciones fuerza la consulta.
 * - Repo privado: definir en wp-config.php
 *     define( 'FEELO_GITHUB_TOKEN', 'github_pat_…' );
 *   (token fine-grained, solo este repo, permiso Contents: Read-only). Repo público: no hace falta.
 *
 * El tema se actualiza aunque sea el tema activo. Sus ajustes (theme_mods) se guardan por nombre
 * de carpeta: la carpeta tiene que seguir llamándose "feelolab" siempre.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

defined( 'ABSPATH' ) || exit;

final class Updater {

	private const CACHE        = 'feelo_update_release';
	private const TTL          = 12 * HOUR_IN_SECONDS;
	private const THEME        = 'feelolab';
	private const THEME_ASSET  = 'feelolab.zip';
	private const PLUGIN_ASSET = 'feelolab-core.zip';

	public static function init(): void {
		add_filter( 'pre_set_site_transient_update_plugins', array( self::class, 'inject_plugin' ) );
		add_filter( 'pre_set_site_transient_update_themes', array( self::class, 'inject_theme' ) );
		add_filter( 'plugins_api', array( self::class, 'plugin_info' ), 20, 3 );
		add_filter( 'http_request_args', array( self::class, 'auth_download' ), 10, 2 );
		add_action( 'requests-requests.before_redirect', array( self::class, 'strip_auth_on_redirect' ), 10, 2 );
		add_action( 'load-update-core.php', array( self::class, 'maybe_force_check' ) );
		add_action( 'upgrader_process_complete', array( self::class, 'flush' ) );
	}

	public static function repo(): string {
		$repo = defined( 'FEELO_UPDATE_REPO' ) ? (string) FEELO_UPDATE_REPO : 'tonaldoing/feelolab-theme';
		return (string) apply_filters( 'feelo_update_repo', $repo );
	}

	private static function token(): string {
		return defined( 'FEELO_GITHUB_TOKEN' ) ? (string) FEELO_GITHUB_TOKEN : '';
	}

	/** Estado para mostrar en Ajustes del sitio. */
	public static function status(): string {
		$release = self::release();
		if ( $release ) {
			return version_compare( $release['version'], FEELO_CORE_VERSION, '>' )
				/* translators: %s: versión nueva */
				? sprintf( __( 'Hay una versión nueva (%s): actualizá desde Escritorio → Actualizaciones.', 'feelolab-core' ), $release['version'] )
				: __( 'Al día.', 'feelolab-core' );
		}
		$cached = get_site_transient( self::CACHE );
		return is_array( $cached ) && ! empty( $cached['error'] )
			/* translators: %s: motivo */
			? sprintf( __( 'No se pudo consultar GitHub: %s', 'feelolab-core' ), $cached['error'] )
			: __( 'Todavía no hay versiones publicadas.', 'feelolab-core' );
	}

	public static function flush(): void {
		delete_site_transient( self::CACHE );
	}

	/** "Buscar de nuevo" en Escritorio → Actualizaciones consulta GitHub sin esperar al caché. */
	public static function maybe_force_check(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- mismo parámetro que usa core; solo borra un caché.
		if ( isset( $_GET['force-check'] ) && current_user_can( 'update_plugins' ) ) {
			self::flush();
		}
	}

	/**
	 * Último release, normalizado. Null si no hay o si GitHub no respondió (se reintenta en 1 hora).
	 *
	 * @return array{version: string, url: string, notes: string, date: string, theme: string, plugin: string}|null
	 */
	public static function release(): ?array {
		$pre = apply_filters( 'feelo_update_pre_release', null );
		if ( null !== $pre ) {
			return $pre ? $pre : null;
		}

		$cached = get_site_transient( self::CACHE );
		if ( is_array( $cached ) ) {
			return $cached['version'] ? $cached : null;
		}

		$headers = array(
			'Accept'               => 'application/vnd.github+json',
			'X-GitHub-Api-Version' => '2022-11-28',
		);
		if ( self::token() ) {
			$headers['Authorization'] = 'Bearer ' . self::token();
		}
		$response = wp_remote_get(
			'https://api.github.com/repos/' . self::repo() . '/releases/latest',
			array(
				'timeout' => 10,
				'headers' => $headers,
			)
		);

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( is_wp_error( $response ) || 200 !== $code ) {
			$reason = is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . $code . ( 404 === $code ? ' (¿repo privado sin FEELO_GITHUB_TOKEN, o sin releases?)' : '' );
			error_log( 'feelolab-core: no se pudo consultar actualizaciones en GitHub: ' . $reason ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			set_site_transient(
				self::CACHE,
				array(
					'version' => '',
					'error'   => $reason,
				),
				HOUR_IN_SECONDS
			);
			return null;
		}

		$data    = json_decode( wp_remote_retrieve_body( $response ), true );
		$release = array(
			'version' => ltrim( (string) ( $data['tag_name'] ?? '' ), 'vV' ),
			'url'     => (string) ( $data['html_url'] ?? '' ),
			'notes'   => (string) ( $data['body'] ?? '' ),
			'date'    => (string) ( $data['published_at'] ?? '' ),
			'theme'   => '',
			'plugin'  => '',
		);
		foreach ( (array) ( $data['assets'] ?? array() ) as $asset ) {
			// Repo privado: se descarga por la API (con token). Público: por el link directo.
			$download = self::token() ? (string) $asset['url'] : (string) $asset['browser_download_url'];
			if ( self::THEME_ASSET === $asset['name'] ) {
				$release['theme'] = $download;
			} elseif ( self::PLUGIN_ASSET === $asset['name'] ) {
				$release['plugin'] = $download;
			}
		}

		set_site_transient( self::CACHE, $release, self::TTL );
		return $release['version'] ? $release : null;
	}

	/**
	 * @param mixed $transient Transient de core.
	 * @return mixed
	 */
	public static function inject_plugin( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}
		$file = plugin_basename( FEELO_CORE_FILE );
		$item = (object) array(
			'id'          => self::repo() . '/' . $file,
			'slug'        => 'feelolab-core',
			'plugin'      => $file,
			'new_version' => FEELO_CORE_VERSION,
			'url'         => 'https://github.com/' . self::repo(),
			'package'     => '',
		);

		$release = self::release();
		if ( $release && $release['plugin'] && version_compare( $release['version'], FEELO_CORE_VERSION, '>' ) ) {
			$item->new_version            = $release['version'];
			$item->package                = $release['plugin'];
			$transient->response[ $file ] = $item;
			unset( $transient->no_update[ $file ] );
		} else {
			// En no_update igual: así WordPress ofrece activar las actualizaciones automáticas.
			$transient->no_update[ $file ] = $item;
		}
		return $transient;
	}

	/**
	 * @param mixed $transient Transient de core.
	 * @return mixed
	 */
	public static function inject_theme( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}
		$theme = wp_get_theme( self::THEME );
		if ( ! $theme->exists() ) {
			return $transient;
		}
		$item = array(
			'theme'       => self::THEME,
			'new_version' => $theme->get( 'Version' ),
			'url'         => 'https://github.com/' . self::repo(),
			'package'     => '',
		);

		$release = self::release();
		if ( $release && $release['theme'] && version_compare( $release['version'], $theme->get( 'Version' ), '>' ) ) {
			$item['new_version']                = $release['version'];
			$item['package']                    = $release['theme'];
			$transient->response[ self::THEME ] = $item;
			unset( $transient->no_update[ self::THEME ] );
		} else {
			$transient->no_update[ self::THEME ] = $item;
		}
		return $transient;
	}

	/**
	 * Modal "Ver detalles" del plugin, con el changelog del release.
	 *
	 * @param mixed  $result Resultado previo.
	 * @param string $action Acción.
	 * @param object $args   Argumentos.
	 * @return mixed
	 */
	public static function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || 'feelolab-core' !== $args->slug ) {
			return $result;
		}
		$release = self::release();
		return (object) array(
			'name'          => 'Feelolab Core',
			'slug'          => 'feelolab-core',
			'version'       => $release ? $release['version'] : FEELO_CORE_VERSION,
			'author'        => 'Feelo',
			'homepage'      => 'https://github.com/' . self::repo(),
			'requires'      => '6.8',
			'requires_php'  => '8.1',
			'last_updated'  => $release ? $release['date'] : '',
			'download_link' => $release ? $release['plugin'] : '',
			'sections'      => array(
				'changelog' => $release && $release['notes'] ? wpautop( esc_html( $release['notes'] ) ) : esc_html__( 'Sin notas de versión.', 'feelolab-core' ),
			),
		);
	}

	/**
	 * Repo privado: la descarga del zip por la API necesita el token y pedir el binario.
	 * Solo para URLs de adjuntos de NUESTRO repo: el token nunca viaja a otro lado.
	 *
	 * @param array<string, mixed> $args Args de la request.
	 * @param string               $url  URL.
	 * @return array<string, mixed>
	 */
	public static function auth_download( array $args, string $url ): array {
		if ( ! self::token() || ! str_starts_with( $url, 'https://api.github.com/repos/' . self::repo() . '/releases/assets/' ) ) {
			return $args;
		}
		$args['headers']                  = is_array( $args['headers'] ?? null ) ? $args['headers'] : array();
		$args['headers']['Authorization'] = 'Bearer ' . self::token();
		$args['headers']['Accept']        = 'application/octet-stream';
		return $args;
	}

	/**
	 * GitHub responde la descarga con un redirect a su almacenamiento (S3), que rechaza el pedido
	 * si trae el token: se quita al salir de api.github.com. De paso el token no sale de GitHub.
	 *
	 * @param string               $location Destino del redirect.
	 * @param array<string, mixed> $headers  Cabeceras (por referencia).
	 */
	public static function strip_auth_on_redirect( &$location, &$headers ): void {
		if ( is_array( $headers ) && 'api.github.com' !== wp_parse_url( (string) $location, PHP_URL_HOST ) ) {
			unset( $headers['Authorization'], $headers['authorization'] );
		}
	}
}
