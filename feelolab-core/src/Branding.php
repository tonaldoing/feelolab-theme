<?php
/**
 * Identidad de FeeloLab en las pantallas propias del admin (Ajustes del sitio, Módulos, Mensajes).
 *
 * Solo en esas pantallas: el resto del panel de WordPress queda como siempre, así el cliente no
 * siente que le cambiaron el admin. Los estilos (assets/admin.css) se cargan únicamente acá.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

use Feelo\Core\Settings\SiteSettings;

defined( 'ABSPATH' ) || exit;

final class Branding {

	/**
	 * Ícono del menú: la marca de FeeloLab (una J redondeada con su hoja), monocromo para que
	 * WordPress lo pinte con el color del esquema del admin.
	 */
	public const MENU_ICON = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCI+PHBhdGggZmlsbD0iYmxhY2siIGQ9Ik0xMS41IDJoMy4ydjkuNGE1LjcgNS43IDAgMCAxLTExLjQgMHYtLjloMy4ydi45YTIuNSAyLjUgMCAwIDAgNSAwek0zLjMgOC4yIDggMy41djQuN3oiLz48L3N2Zz4=';

	public static function init(): void {
		add_action( 'admin_enqueue_scripts', array( self::class, 'assets' ) );
		add_filter( 'admin_footer_text', array( self::class, 'footer_text' ) );
		add_filter( 'admin_body_class', array( self::class, 'body_class' ) );
		add_filter( 'views_edit-feelo_mensaje', array( self::class, 'messages_header' ) );
	}

	/**
	 * Listado de Mensajes: la cabecera de marca va arriba de los filtros (el h1 nativo se oculta por CSS).
	 *
	 * @param array<string, string> $views Filtros del listado (sin cambios).
	 * @return array<string, string>
	 */
	public static function messages_header( array $views ): array {
		self::header(
			__( 'Mensajes', 'feelolab-core' ),
			__( 'Todo lo que llega por el formulario de contacto queda guardado acá, aunque el email de aviso falle.', 'feelolab-core' )
		);
		self::nav( 'mensajes' );
		return $views;
	}

	/** ¿Es una pantalla propia de FeeloLab? */
	public static function is_feelo_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return false;
		}
		return str_contains( (string) $screen->id, 'feelo-' ) || 'feelo_mensaje' === $screen->post_type;
	}

	public static function assets(): void {
		if ( ! self::is_feelo_screen() ) {
			return;
		}
		wp_enqueue_style( 'feelo-admin', FEELO_CORE_URL . 'assets/admin.css', array(), FEELO_CORE_VERSION );
		// La ardilla del estado vacío de Mensajes, como variable: el CSS no conoce la URL del plugin.
		wp_add_inline_style( 'feelo-admin', ':root{--feelo-squirrel-megaphone:url(' . esc_url( self::img( 'ardilla-megafono.webp' ) ) . ')}' );
	}

	public static function body_class( string $classes ): string {
		return self::is_feelo_screen() ? $classes . ' feelo-admin' : $classes;
	}

	public static function footer_text( $text ) {
		if ( ! self::is_feelo_screen() ) {
			return $text;
		}
		return sprintf(
			/* translators: %s: link a FeeloLab */
			esc_html__( 'Tu sitio, hecho por %s.', 'feelolab-core' ),
			'<a href="' . esc_url( Updater::HOMEPAGE ) . '" target="_blank" rel="noopener">FeeloLab<span class="screen-reader-text"> ' . esc_html__( '(se abre en otra pestaña)', 'feelolab-core' ) . '</span></a>'
		);
	}

	public static function img( string $file ): string {
		return FEELO_CORE_URL . 'assets/img/' . $file;
	}

	/**
	 * Cabecera de marca. Lleva el h1 de la pantalla (WordPress ubica los avisos después) y
	 * el separador wp-header-end para que los avisos no queden dentro del degradado.
	 *
	 * @param string $title    Título de la pantalla (h1).
	 * @param string $subtitle Una frase de qué se hace acá.
	 */
	public static function header( string $title, string $subtitle ): void {
		$status = Updater::status();
		$ok     = str_contains( $status, __( 'Al día.', 'feelolab-core' ) );
		?>
		<header class="feelo-hero">
			<div class="feelo-hero__body">
				<p class="feelo-hero__brand" aria-hidden="true"><span class="feelo-hero__brand-a">Feelo</span><span class="feelo-hero__brand-b">Lab</span></p>
				<h1 class="feelo-hero__title"><?php echo esc_html( $title ); ?></h1>
				<p class="feelo-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<ul class="feelo-hero__chips">
					<li class="feelo-chip">
						<?php
						/* translators: %s: versión */
						echo esc_html( sprintf( __( 'Versión %s', 'feelolab-core' ), FEELO_CORE_VERSION ) );
						?>
					</li>
					<?php if ( $ok ) : ?>
						<li class="feelo-chip feelo-chip--ok"><span class="feelo-chip__dot" aria-hidden="true"></span><?php esc_html_e( 'Actualizaciones al día', 'feelolab-core' ); ?></li>
					<?php else : ?>
						<li><a class="feelo-chip feelo-chip--warn feelo-chip--link" href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>" aria-describedby="feelo-update-note"><span class="feelo-chip__dot" aria-hidden="true"></span><?php esc_html_e( 'Revisar actualizaciones', 'feelolab-core' ); ?></a></li>
					<?php endif; ?>
					<li><a class="feelo-chip feelo-chip--link" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=feelolab_brand' ) ); ?>"><?php esc_html_e( 'Personalizar marca', 'feelolab-core' ); ?></a></li>
					<li><a class="feelo-chip feelo-chip--link" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ver sitio', 'feelolab-core' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(se abre en otra pestaña)', 'feelolab-core' ); ?></span></a></li>
				</ul>
				<?php if ( ! $ok ) : ?>
					<p class="feelo-hero__note" id="feelo-update-note"><?php echo esc_html( $status ); ?></p>
				<?php endif; ?>
			</div>
			<img class="feelo-hero__squirrel" src="<?php echo esc_url( self::img( 'ardilla-lupa.webp' ) ); ?>" alt="" width="160" height="154" decoding="async">
		</header>
		<hr class="wp-header-end">
		<?php
	}

	/** Menú de navegación entre las pantallas propias (Ajustes / Módulos / Mensajes). */
	public static function nav( string $current ): void {
		$items = array(
			'ajustes'     => array( __( 'Ajustes del sitio', 'feelolab-core' ), admin_url( 'admin.php?page=' . SiteSettings::PAGE ), 'dashicons-store' ),
			'modulos'     => array( __( 'Módulos', 'feelolab-core' ), admin_url( 'admin.php?page=feelo-modulos' ), 'dashicons-screenoptions' ),
			'mensajes'    => array( __( 'Mensajes', 'feelolab-core' ), admin_url( 'edit.php?post_type=feelo_mensaje' ), 'dashicons-email-alt' ),
			'lanzamiento' => array( __( 'Lanzamiento', 'feelolab-core' ), admin_url( 'admin.php?page=' . Launch::PAGE ), 'dashicons-flag' ),
			'importar'    => array( __( 'Importar productos', 'feelolab-core' ), admin_url( 'admin.php?page=' . Importer::PAGE ), 'dashicons-database-import' ),
			'versiones'   => array( __( 'Versiones', 'feelolab-core' ), admin_url( 'admin.php?page=' . Versions::PAGE ), 'dashicons-backup' ),
		);
		echo '<nav class="feelo-sections" aria-label="' . esc_attr__( 'FeeloLab', 'feelolab-core' ) . '"><ul>';
		foreach ( $items as $key => $item ) {
			printf(
				'<li><a href="%1$s" class="%2$s"%3$s><span class="dashicons %4$s" aria-hidden="true"></span>%5$s</a></li>',
				esc_url( $item[1] ),
				$key === $current ? 'is-current' : '',
				$key === $current ? ' aria-current="page"' : '',
				esc_attr( $item[2] ),
				esc_html( $item[0] )
			);
		}
		echo '</ul></nav>';
	}
}
