<?php
/**
 * Pantalla Versiones: qué versión tiene el sitio, qué trae la última, el canal (estable o beta) y
 * volver a una versión anterior si una actualización trajo un problema.
 *
 * Volver atrás reinstala tema y plugin juntos desde los zips publicados en el repo de releases
 * (siempre la misma versión para los dos). Los ajustes y el contenido no se tocan: viven en la
 * base de datos. Después WordPress vuelve a ofrecer la última; se actualiza cuando esté arreglada.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

use Feelo\Core\Settings\SiteSettings;

defined( 'ABSPATH' ) || exit;

final class Versions {

	public const PAGE = 'feelo-versiones';

	private const RESULT = 'feelo_versions_result';

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ), 14 );
	}

	public static function menu(): void {
		$hook = add_submenu_page(
			SiteSettings::PAGE,
			__( 'Versiones', 'feelolab-core' ),
			__( 'Versiones', 'feelolab-core' ),
			'update_plugins',
			self::PAGE,
			array( self::class, 'render' )
		);
		if ( $hook ) {
			add_action( 'load-' . $hook, array( self::class, 'handle' ) );
		}
	}

	private static function url( array $args = array() ): string {
		return add_query_arg( $args, admin_url( 'admin.php?page=' . self::PAGE ) );
	}

	/** Formularios de la pantalla (canal y volver atrás). Corre antes de imprimir nada: redirige al terminar. */
	public static function handle(): void {
		if ( 'POST' !== strtoupper( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) || ! current_user_can( 'update_plugins' ) || ! current_user_can( 'update_themes' ) ) {
			return;
		}
		if ( isset( $_POST['feelo_channel'] ) ) {
			check_admin_referer( 'feelo_channel' );
			$settings               = (array) get_option( SiteSettings::OPTION, array() );
			$settings['canal_beta'] = empty( $_POST['canal_beta'] ) ? '' : '1';
			update_option( SiteSettings::OPTION, $settings );
			Updater::flush();
			delete_site_transient( 'update_plugins' );
			delete_site_transient( 'update_themes' );
			wp_safe_redirect( self::url( array( 'feelo_msg' => 'canal' ) ) );
			exit;
		}
		if ( isset( $_POST['feelo_rollback'] ) ) {
			check_admin_referer( 'feelo_rollback' );
			$version = sanitize_text_field( wp_unslash( $_POST['feelo_rollback'] ) );
			$release = Updater::release();
			$allowed = $release ? $release['versions'] ?? array() : array();
			if ( ! in_array( $version, $allowed, true ) ) {
				wp_safe_redirect( self::url( array( 'feelo_msg' => 'no-version' ) ) );
				exit;
			}
			set_transient( self::RESULT, self::rollback( $version ), 10 * MINUTE_IN_SECONDS );
			wp_safe_redirect( self::url( array( 'feelo_msg' => 'rollback' ) ) );
			exit;
		}
	}

	/**
	 * Reinstala tema y plugin en esa versión.
	 *
	 * @return array{ok: bool, version: string, messages: string[]}
	 */
	private static function rollback( string $version ): array {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/theme.php';

		$messages = array();
		$ok       = true;
		$jobs     = array(
			array( 'Theme_Upgrader', Updater::asset_url( $version, 'feelolab.zip' ), __( 'Tema', 'feelolab-core' ) ),
			array( 'Plugin_Upgrader', Updater::asset_url( $version, 'feelolab-core.zip' ), __( 'Plugin', 'feelolab-core' ) ),
		);
		foreach ( $jobs as $job ) {
			$class    = '\\' . $job[0];
			$skin     = new \Automatic_Upgrader_Skin();
			$upgrader = new $class( $skin );
			$result   = $upgrader->install( $job[1], array( 'overwrite_package' => true ) );
			if ( true === $result ) {
				/* translators: 1: Tema o Plugin, 2: versión */
				$messages[] = sprintf( __( '%1$s: instalada la versión %2$s.', 'feelolab-core' ), $job[2], $version );
				continue;
			}
			$ok    = false;
			$error = is_wp_error( $result ) ? $result->get_error_message() : implode( ' ', array_map( 'wp_strip_all_tags', (array) $skin->get_upgrade_messages() ) );
			$error = '' !== trim( $error ) ? $error : __( 'error desconocido', 'feelolab-core' );
			/* translators: 1: Tema o Plugin, 2: motivo */
			$messages[] = sprintf( __( '%1$s: no se pudo instalar (%2$s).', 'feelolab-core' ), $job[2], $error );
			error_log( 'feelolab-core: volver a ' . $version . ' falló (' . $job[2] . '): ' . $error ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			break; // Si falla el tema, no se toca el plugin: tema y plugin siempre en la misma versión.
		}
		// El plugin se reemplazó sin desactivarlo; por las dudas, que siga activo.
		if ( ! is_plugin_active( plugin_basename( FEELO_CORE_FILE ) ) ) {
			activate_plugin( plugin_basename( FEELO_CORE_FILE ) );
		}
		Updater::flush();
		wp_clean_plugins_cache();
		wp_clean_themes_cache();
		return array(
			'ok'       => $ok,
			'version'  => $version,
			'messages' => $messages,
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}
		$release = Updater::release();
		$current = FEELO_CORE_VERSION;
		$beta    = Updater::beta();
		$older   = array_values( array_filter( $release['versions'] ?? array(), static fn( $v ) => version_compare( $v, $current, '<' ) ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo elige qué aviso mostrar.
		$msg    = isset( $_GET['feelo_msg'] ) ? sanitize_key( wp_unslash( $_GET['feelo_msg'] ) ) : '';
		$result = 'rollback' === $msg ? get_transient( self::RESULT ) : false;
		?>
		<div class="wrap">
			<?php
			Branding::header(
				__( 'Versiones', 'feelolab-core' ),
				__( 'Qué versión tiene el sitio, qué trae la última y cómo volver atrás si algo falla.', 'feelolab-core' )
			);
			Branding::nav( 'versiones' );

			if ( 'canal' === $msg ) {
				echo '<div class="notice notice-success"><p>' . esc_html( $beta ? __( 'Listo: el sitio recibe versiones de prueba.', 'feelolab-core' ) : __( 'Listo: el sitio recibe solo versiones estables.', 'feelolab-core' ) ) . '</p></div>';
			} elseif ( 'no-version' === $msg ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Esa versión no está disponible.', 'feelolab-core' ) . '</p></div>';
			} elseif ( is_array( $result ) ) {
				printf(
					'<div class="notice %1$s"><p><strong>%2$s</strong></p><ul><li>%3$s</li></ul></div>',
					esc_attr( $result['ok'] ? 'notice-success' : 'notice-error' ),
					esc_html(
						$result['ok']
							/* translators: %s: versión */
							? sprintf( __( 'Listo: el sitio volvió a la versión %s.', 'feelolab-core' ), $result['version'] )
							: __( 'No se pudo volver atrás. El sitio sigue como estaba.', 'feelolab-core' )
					),
					implode( '</li><li>', array_map( 'esc_html', $result['messages'] ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- cada ítem escapado.
				);
				delete_transient( self::RESULT );
			}
			?>

			<section class="feelo-card" aria-labelledby="feelo-v-current">
				<h2 class="feelo-card__title" id="feelo-v-current"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
					<?php
					/* translators: %s: versión */
					echo esc_html( sprintf( __( 'Versión instalada: %s', 'feelolab-core' ), $current ) );
					?>
				</h2>
				<p class="feelo-card__intro"><?php echo esc_html( Updater::status() ); ?></p>
				<p><a class="button" href="<?php echo esc_url( admin_url( 'update-core.php?force-check=1' ) ); ?>"><?php esc_html_e( 'Buscar actualizaciones ahora', 'feelolab-core' ); ?></a></p>
				<?php if ( $release && $release['notes'] ) : ?>
					<h3>
						<?php
						/* translators: %s: versión */
						echo esc_html( sprintf( __( 'Qué trae la %s', 'feelolab-core' ), $release['version'] ) );
						?>
					</h3>
					<div class="feelo-notes"><?php echo wp_kses_post( Updater::notes_html( $release['notes'] ) ); ?></div>
				<?php endif; ?>
			</section>

			<section class="feelo-card" aria-labelledby="feelo-v-channel">
				<h2 class="feelo-card__title" id="feelo-v-channel"><span class="dashicons dashicons-flag" aria-hidden="true"></span><?php esc_html_e( 'Canal de actualizaciones', 'feelolab-core' ); ?></h2>
				<form method="post">
					<?php wp_nonce_field( 'feelo_channel' ); ?>
					<input type="hidden" name="feelo_channel" value="1">
					<p class="feelo-check-row">
						<input type="checkbox" id="feelo-canal-beta" name="canal_beta" value="1" aria-describedby="feelo-canal-beta-help" <?php checked( $beta ); ?>>
						<label for="feelo-canal-beta"><strong><?php esc_html_e( 'Recibir versiones de prueba (beta)', 'feelolab-core' ); ?></strong></label>
					</p>
					<p class="description" id="feelo-canal-beta-help"><?php esc_html_e( 'Solo para un sitio de pruebas: recibe las novedades antes que los clientes, para revisarlas. En los sitios de clientes dejalo sin tildar.', 'feelolab-core' ); ?></p>
					<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Guardar canal', 'feelolab-core' ); ?></button></p>
				</form>
			</section>

			<section class="feelo-card" aria-labelledby="feelo-v-rollback">
				<h2 class="feelo-card__title" id="feelo-v-rollback"><span class="dashicons dashicons-backup" aria-hidden="true"></span><?php esc_html_e( 'Volver a una versión anterior', 'feelolab-core' ); ?></h2>
				<p class="feelo-card__intro"><?php esc_html_e( 'Si una actualización trajo un problema, volvé a la versión anterior y avisale a FeeloLab. Tema y plugin vuelven juntos; los ajustes, los colores y el contenido no se tocan.', 'feelolab-core' ); ?></p>
				<?php if ( $older ) : ?>
					<ul class="feelo-versions">
						<?php foreach ( array_slice( $older, 0, 5 ) as $version ) : ?>
							<li>
								<span class="feelo-versions__name"><?php echo esc_html( $version ); ?></span>
								<form method="post" onsubmit="return confirm(this.getAttribute('data-confirm'));" data-confirm="<?php echo esc_attr( sprintf( /* translators: %s: versión */ __( '¿Volver a la versión %s? El sitio sigue funcionando mientras tanto.', 'feelolab-core' ), $version ) ); ?>">
									<?php wp_nonce_field( 'feelo_rollback' ); ?>
									<button type="submit" class="button" name="feelo_rollback" value="<?php echo esc_attr( $version ); ?>">
										<?php
										/* translators: %s: versión */
										echo esc_html( sprintf( __( 'Volver a la %s', 'feelolab-core' ), $version ) );
										?>
									</button>
								</form>
							</li>
						<?php endforeach; ?>
					</ul>
					<p class="description"><?php esc_html_e( 'Después WordPress vuelve a ofrecer la última versión. Actualizá cuando FeeloLab confirme que está arreglada (y si tenés las actualizaciones automáticas prendidas, apagalas mientras tanto en Plugins y en Apariencia → Temas).', 'feelolab-core' ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Todavía no hay versiones anteriores disponibles para volver.', 'feelolab-core' ); ?></p>
				<?php endif; ?>
			</section>
		</div>
		<?php
	}
}
