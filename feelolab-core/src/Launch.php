<?php
/**
 * Checklist de lanzamiento: revisa lo que suele quedar mal al entregar un sitio.
 *
 * Pantalla propia (Ajustes del sitio → Lanzamiento) con barra de progreso y un botón "Arreglar"
 * por punto, más un resumen en el Escritorio. Cada punto dice por qué importa, en una línea.
 * Todo se calcula al abrir la pantalla: no guarda nada ni corre en el frontend.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

use Feelo\Core\Settings\SiteSettings;

defined( 'ABSPATH' ) || exit;

final class Launch {

	public const PAGE = 'feelo-lanzamiento';

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ), 12 );
		add_action( 'wp_dashboard_setup', array( self::class, 'dashboard' ) );
	}

	public static function menu(): void {
		add_submenu_page(
			SiteSettings::PAGE,
			__( 'Lanzamiento', 'feelolab-core' ),
			__( 'Lanzamiento', 'feelolab-core' ),
			'manage_options',
			self::PAGE,
			array( self::class, 'render' )
		);
	}

	/**
	 * Los puntos del checklist.
	 *
	 * @return array<int, array{id: string, group: string, level: string, ok: bool, title: string, why: string, fix: string}>
	 */
	public static function checks(): array {
		$settings  = static fn( string $tab ) => admin_url( 'admin.php?page=' . SiteSettings::PAGE . '&tab=' . $tab );
		$customize = static fn( string $target ) => admin_url( 'customize.php?autofocus' . $target );

		$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
		$locations  = get_nav_menu_locations();
		$contact    = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- solo en esta pantalla del admin.
				'meta_value'     => 'page-templates/contacto.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		$hello      = get_page_by_path( 'hello-world', OBJECT, 'post' );
		$sample     = get_page_by_path( 'sample-page', OBJECT, 'page' );
		$tagline    = trim( (string) get_bloginfo( 'description' ) );
		$type       = (string) feelo_setting( 'tipo_negocio' );
		$is_local   = $type && ! in_array( $type, array( 'Organization', 'EducationalOrganization' ), true );
		$smtp       = self::smtp_active();

		$checks = array(
			array(
				'id'    => 'visible',
				'group' => __( 'Google', 'feelolab-core' ),
				'level' => 'critico',
				'ok'    => '1' === (string) get_option( 'blog_public' ),
				'title' => __( 'El sitio es visible para los buscadores', 'feelolab-core' ),
				'why'   => __( 'Si está tildado "Pedir a los motores de búsqueda que no indexen este sitio", Google no lo muestra nunca.', 'feelolab-core' ),
				'fix'   => admin_url( 'options-reading.php' ),
			),
			array(
				'id'    => 'permalinks',
				'group' => __( 'Google', 'feelolab-core' ),
				'level' => 'critico',
				'ok'    => '' !== (string) get_option( 'permalink_structure' ),
				'title' => __( 'Direcciones amigables activadas', 'feelolab-core' ),
				'why'   => __( 'Con las direcciones "simples" (?p=123) las páginas de servicios y productos no funcionan bien y posicionan peor.', 'feelolab-core' ),
				'fix'   => admin_url( 'options-permalink.php' ),
			),
			array(
				'id'    => 'acf',
				'group' => __( 'Contenido', 'feelolab-core' ),
				'level' => 'critico',
				'ok'    => function_exists( 'acf_add_local_field_group' ),
				'title' => __( 'Advanced Custom Fields instalado y activo', 'feelolab-core' ),
				'why'   => __( 'Sin ACF no se pueden cargar precios, cargos, horarios ni el resto de los campos de cada contenido.', 'feelolab-core' ),
				'fix'   => admin_url( 'plugin-install.php?s=advanced+custom+fields&tab=search&type=term' ),
			),
			array(
				'id'    => 'privacy',
				'group' => __( 'Legal', 'feelolab-core' ),
				'level' => 'critico',
				'ok'    => $privacy_id && 'publish' === get_post_status( $privacy_id ),
				'title' => __( 'Política de privacidad publicada y elegida', 'feelolab-core' ),
				'why'   => __( 'El formulario pide aceptarla y el banner de cookies la enlaza. Es obligatoria si se recolectan datos.', 'feelolab-core' ),
				'fix'   => admin_url( 'options-privacy.php' ),
			),
			array(
				'id'    => 'logo',
				'group' => __( 'Marca', 'feelolab-core' ),
				'level' => 'critico',
				'ok'    => (bool) get_theme_mod( 'custom_logo' ),
				'title' => __( 'Logo cargado', 'feelolab-core' ),
				'why'   => __( 'Sin logo, el encabezado muestra el nombre en texto y Google no tiene imagen para la ficha del negocio.', 'feelolab-core' ),
				'fix'   => $customize( '[control]=custom_logo' ),
			),
			array(
				'id'    => 'icon',
				'group' => __( 'Marca', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => (bool) get_option( 'site_icon' ),
				'title' => __( 'Ícono del sitio (favicon) cargado', 'feelolab-core' ),
				'why'   => __( 'Es el que aparece en la pestaña del navegador y en los resultados de Google en el celular.', 'feelolab-core' ),
				'fix'   => $customize( '[control]=site_icon' ),
			),
			array(
				'id'    => 'contact',
				'group' => __( 'Negocio', 'feelolab-core' ),
				'level' => 'critico',
				'ok'    => feelo_setting( 'telefono' ) || feelo_setting( 'whatsapp' ) || feelo_setting( 'email' ),
				'title' => __( 'Al menos un dato de contacto (teléfono, WhatsApp o email)', 'feelolab-core' ),
				'why'   => __( 'Es lo que convierte: sin esto, los botones de contacto no tienen a dónde llevar.', 'feelolab-core' ),
				'fix'   => $settings( 'contacto' ),
			),
			array(
				'id'    => 'description',
				'group' => __( 'Negocio', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => '' !== trim( (string) feelo_setting( 'descripcion' ) ),
				'title' => __( 'Descripción breve del negocio', 'feelolab-core' ),
				'why'   => __( 'Es la descripción que muestra Google para la home y la que leen los asistentes con IA.', 'feelolab-core' ),
				'fix'   => $settings( 'negocio' ),
			),
			array(
				'id'    => 'type',
				'group' => __( 'Negocio', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => '' !== $type,
				'title' => __( 'Tipo de negocio elegido', 'feelolab-core' ),
				'why'   => __( 'Define la ficha que arma Google (tienda, consultorio, estudio…).', 'feelolab-core' ),
				'fix'   => $settings( 'negocio' ),
			),
			array(
				'id'    => 'address',
				'group' => __( 'Negocio', 'feelolab-core' ),
				'level' => $is_local ? 'critico' : 'recomendado',
				'ok'    => ! $is_local || ( feelo_setting( 'direccion' ) && feelo_setting( 'ciudad' ) ),
				'title' => __( 'Dirección completa (negocio con local)', 'feelolab-core' ),
				'why'   => __( 'Un negocio local sin dirección pierde la ficha de Google Maps.', 'feelolab-core' ),
				'fix'   => $settings( 'contacto' ),
			),
			array(
				'id'    => 'front',
				'group' => __( 'Páginas', 'feelolab-core' ),
				'level' => 'critico',
				'ok'    => 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ),
				'title' => __( 'La portada es una página estática', 'feelolab-core' ),
				'why'   => __( 'Las secciones de la home (portada, servicios, testimonios…) solo aparecen con una portada estática.', 'feelolab-core' ),
				'fix'   => admin_url( 'options-reading.php' ),
			),
			array(
				'id'    => 'contact_page',
				'group' => __( 'Páginas', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => ! empty( $contact ),
				'title' => __( 'Página de contacto con la plantilla "Contacto"', 'feelolab-core' ),
				'why'   => __( 'Muestra el formulario, los datos y las sedes solos, y es a donde llevan los botones sin WhatsApp.', 'feelolab-core' ),
				'fix'   => admin_url( 'edit.php?post_type=page' ),
			),
			array(
				'id'    => 'menu',
				'group' => __( 'Páginas', 'feelolab-core' ),
				'level' => 'critico',
				'ok'    => ! empty( $locations['primary'] ),
				'title' => __( 'Menú principal asignado', 'feelolab-core' ),
				'why'   => __( 'Sin menú asignado, el encabezado muestra una lista automática de páginas.', 'feelolab-core' ),
				'fix'   => admin_url( 'nav-menus.php?action=locations' ),
			),
			array(
				'id'    => 'demo',
				'group' => __( 'Contenido', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => ! ( $hello && 'publish' === $hello->post_status ) && ! ( $sample && 'publish' === $sample->post_status ),
				'title' => __( 'Contenido de ejemplo de WordPress borrado', 'feelolab-core' ),
				'why'   => __( '"Hello world!" y "Sample Page" quedan indexados y muestran un sitio sin terminar.', 'feelolab-core' ),
				'fix'   => admin_url( 'edit.php' ),
			),
			array(
				'id'    => 'tagline',
				'group' => __( 'Contenido', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => '' !== $tagline && ! preg_match( '/wordpress/i', $tagline ),
				'title' => __( 'Descripción corta del sitio cambiada', 'feelolab-core' ),
				'why'   => __( 'La que trae WordPress ("Otro sitio realizado con WordPress") aparece en el título de la pestaña y en Google.', 'feelolab-core' ),
				'fix'   => admin_url( 'options-general.php' ),
			),
			array(
				'id'    => 'timezone',
				'group' => __( 'Configuración', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => '' !== (string) get_option( 'timezone_string' ),
				'title' => __( 'Zona horaria del negocio elegida', 'feelolab-core' ),
				'why'   => __( 'Las fechas de las notas y de los mensajes salen en hora UTC si no se elige una ciudad.', 'feelolab-core' ),
				'fix'   => admin_url( 'options-general.php' ),
			),
			array(
				'id'    => 'smtp',
				'group' => __( 'Configuración', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => $smtp,
				'title' => __( 'Envío de emails configurado (SMTP)', 'feelolab-core' ),
				'why'   => __( 'Sin SMTP, los avisos del formulario suelen caer en spam. FluentSMTP es gratis. Igual los mensajes quedan guardados en Mensajes.', 'feelolab-core' ),
				'fix'   => admin_url( 'plugin-install.php?s=fluent+smtp&tab=search&type=term' ),
			),
			array(
				'id'    => 'analytics',
				'group' => __( 'Medición', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => (bool) ( feelo_setting( 'ga4_id' ) || feelo_setting( 'gtm_id' ) ),
				'title' => __( 'Google Analytics o Tag Manager cargado', 'feelolab-core' ),
				'why'   => __( 'Sin medición no se ven las visitas ni los clics de WhatsApp, teléfono y formulario.', 'feelolab-core' ),
				'fix'   => $settings( 'integraciones' ),
			),
			array(
				'id'    => 'consent',
				'group' => __( 'Legal', 'feelolab-core' ),
				'level' => 'recomendado',
				'ok'    => ! ( feelo_setting( 'ga4_id' ) || feelo_setting( 'gtm_id' ) ) || (bool) feelo_setting( 'consent_banner' ),
				'title' => __( 'Banner de cookies activado (si hay Analytics)', 'feelolab-core' ),
				'why'   => __( 'Obligatorio con visitas de Europa y para medir conversiones de Google Ads.', 'feelolab-core' ),
				'fix'   => $settings( 'integraciones' ),
			),
		);

		return apply_filters( 'feelo_launch_checks', $checks );
	}

	/** @return array{done: int, total: int, critical: int} */
	public static function summary( array $checks ): array {
		$done     = count( array_filter( $checks, static fn( $c ) => $c['ok'] ) );
		$critical = count( array_filter( $checks, static fn( $c ) => ! $c['ok'] && 'critico' === $c['level'] ) );
		return array(
			'done'     => $done,
			'total'    => count( $checks ),
			'critical' => $critical,
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$checks  = self::checks();
		$sum     = self::summary( $checks );
		$percent = $sum['total'] ? (int) round( 100 * $sum['done'] / $sum['total'] ) : 0;
		// Primero lo pendiente (críticos arriba), después lo resuelto.
		usort(
			$checks,
			static fn( $a, $b ) => array( $a['ok'], 'critico' !== $a['level'] ) <=> array( $b['ok'], 'critico' !== $b['level'] )
		);
		?>
		<div class="wrap">
			<?php
			Branding::header(
				__( 'Lanzamiento', 'feelolab-core' ),
				__( 'Lo que suele quedar mal al entregar un sitio. Cuando todo esté en verde, está listo para publicar.', 'feelolab-core' )
			);
			Branding::nav( 'lanzamiento' );
			?>
			<section class="feelo-card feelo-launch" aria-labelledby="feelo-launch-title">
				<h2 class="feelo-card__title" id="feelo-launch-title">
					<?php
					/* translators: 1: resueltos, 2: total */
					echo esc_html( sprintf( __( '%1$d de %2$d listos', 'feelolab-core' ), $sum['done'], $sum['total'] ) );
					?>
				</h2>
				<p class="feelo-card__intro">
					<?php
					echo esc_html(
						$sum['critical']
							/* translators: %d: cantidad de puntos críticos */
							? sprintf( _n( 'Falta %d punto importante antes de publicar.', 'Faltan %d puntos importantes antes de publicar.', $sum['critical'], 'feelolab-core' ), $sum['critical'] )
							: ( $sum['done'] === $sum['total'] ? __( '¡Todo listo! El sitio está para publicar.', 'feelolab-core' ) : __( 'Lo importante está resuelto. Quedan recomendaciones.', 'feelolab-core' ) )
					);
					?>
				</p>
				<div class="feelo-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $percent ); ?>" aria-labelledby="feelo-launch-title">
					<span style="width:<?php echo esc_attr( (string) $percent ); ?>%"></span>
				</div>
				<ul class="feelo-checks">
					<?php foreach ( $checks as $check ) : ?>
						<?php
						$state = $check['ok'] ? 'ok' : ( 'critico' === $check['level'] ? 'critical' : 'pending' );
						$label = array(
							'ok'       => __( 'Listo', 'feelolab-core' ),
							'critical' => __( 'Importante', 'feelolab-core' ),
							'pending'  => __( 'Recomendado', 'feelolab-core' ),
						)[ $state ];
						?>
						<li class="feelo-check is-<?php echo esc_attr( $state ); ?>">
							<span class="feelo-check__icon dashicons <?php echo esc_attr( $check['ok'] ? 'dashicons-yes-alt' : ( 'critical' === $state ? 'dashicons-warning' : 'dashicons-marker' ) ); ?>" aria-hidden="true"></span>
							<div class="feelo-check__body">
								<p class="feelo-check__title">
									<span class="feelo-check__badge"><?php echo esc_html( $label ); ?></span>
									<?php echo esc_html( $check['title'] ); ?>
								</p>
								<?php if ( ! $check['ok'] ) : ?>
									<p class="feelo-check__why"><?php echo esc_html( $check['why'] ); ?></p>
								<?php endif; ?>
							</div>
							<?php if ( ! $check['ok'] ) : ?>
								<a class="button feelo-check__fix" href="<?php echo esc_url( $check['fix'] ); ?>">
									<?php esc_html_e( 'Arreglar', 'feelolab-core' ); ?>
									<span class="screen-reader-text">: <?php echo esc_html( $check['title'] ); ?></span>
								</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		</div>
		<?php
	}

	public static function dashboard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		wp_add_dashboard_widget( 'feelo_launch', __( 'FeeloLab · Lanzamiento', 'feelolab-core' ), array( self::class, 'widget' ) );
	}

	public static function widget(): void {
		$checks  = self::checks();
		$sum     = self::summary( $checks );
		$percent = $sum['total'] ? (int) round( 100 * $sum['done'] / $sum['total'] ) : 0;
		$pending = array_values( array_filter( $checks, static fn( $c ) => ! $c['ok'] ) );
		usort( $pending, static fn( $a, $b ) => ( 'critico' !== $a['level'] ) <=> ( 'critico' !== $b['level'] ) );
		?>
		<p>
			<strong>
				<?php
				/* translators: 1: resueltos, 2: total */
				echo esc_html( sprintf( __( '%1$d de %2$d listos', 'feelolab-core' ), $sum['done'], $sum['total'] ) );
				?>
			</strong>
		</p>
		<div class="feelo-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $percent ); ?>" aria-label="<?php esc_attr_e( 'Progreso del lanzamiento', 'feelolab-core' ); ?>" style="height:8px;border-radius:99px;background:#f0f0f1;overflow:hidden;margin:6px 0 12px">
			<span style="display:block;height:100%;width:<?php echo esc_attr( (string) $percent ); ?>%;background:#4a6400"></span>
		</div>
		<?php if ( $pending ) : ?>
			<ul style="margin:0 0 12px">
				<?php foreach ( array_slice( $pending, 0, 3 ) as $check ) : ?>
					<li>
						<?php echo 'critico' === $check['level'] ? '<strong>' . esc_html__( 'Importante:', 'feelolab-core' ) . '</strong> ' : ''; ?>
						<a href="<?php echo esc_url( $check['fix'] ); ?>"><?php echo esc_html( $check['title'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p><?php esc_html_e( '¡Todo listo! El sitio está para publicar.', 'feelolab-core' ); ?></p>
		<?php endif; ?>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE ) ); ?>"><?php esc_html_e( 'Ver el checklist completo', 'feelolab-core' ); ?></a></p>
		<?php
	}

	/** Plugins de SMTP conocidos (FluentSMTP, WP Mail SMTP, Post SMTP). Filtrable para otros. */
	private static function smtp_active(): bool {
		$known  = array( 'fluent-smtp/fluent-smtp.php', 'wp-mail-smtp/wp_mail_smtp.php', 'wp-mail-smtp-pro/wp_mail_smtp.php', 'post-smtp/postman-smtp.php' );
		$active = (array) get_option( 'active_plugins', array() );
		return (bool) apply_filters( 'feelo_launch_smtp_active', (bool) array_intersect( $known, $active ) );
	}
}
