<?php
/**
 * Primeros pasos: un asistente que deja el sitio armado en cinco pasos.
 *
 * 1. Negocio: nombre, tipo y descripción.
 * 2. Contacto: teléfono, WhatsApp, email y dirección.
 * 3. Marca: logo, colores y tipografía (solo con el tema FeeloLab activo).
 * 4. Módulos: qué contenidos usa el cliente.
 * 5. Páginas: crea Inicio, Contacto y Blog, arma el menú, activa las direcciones amigables, la
 *    zona horaria y la política de privacidad, y borra el contenido de ejemplo de WordPress.
 *
 * Al activar el plugin se ofrece con un aviso en Plugins y en el Escritorio (no se abre solo:
 * redirigir al activar es mala práctica y rompe las activaciones en lote). Cada paso guarda al avanzar y se puede
 * saltear; todo lo que hace se puede cambiar después desde su pantalla de siempre. Lo que ya
 * existe (páginas, menú) no se duplica: el asistente se puede volver a correr.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

use Feelo\Core\Modules\Registry;
use Feelo\Core\Settings\SiteSettings;

defined( 'ABSPATH' ) || exit;

final class Wizard {

	public const PAGE    = 'feelo-primeros-pasos';
	public const PENDING = 'feelo_wizard_pending';
	public const DONE    = 'feelo_wizard_done';

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ), 15 );
		add_action( 'admin_init', array( self::class, 'maybe_mark_done' ) );
		add_action( 'admin_notices', array( self::class, 'notice' ) );
	}

	public static function menu(): void {
		$hook = add_submenu_page(
			SiteSettings::PAGE,
			__( 'Primeros pasos', 'feelolab-core' ),
			__( 'Primeros pasos', 'feelolab-core' ),
			'manage_options',
			self::PAGE,
			array( self::class, 'render' )
		);
		if ( $hook ) {
			add_action( 'load-' . $hook, array( self::class, 'handle' ) );
		}
	}

	/** Sitios que ya estaban configurados antes de que existiera el asistente: no se les ofrece. */
	public static function maybe_mark_done(): void {
		if ( get_option( self::PENDING ) ) {
			delete_option( self::PENDING ); // Marca de versiones viejas, que redirigían al activar.
		}
		if ( ! get_option( self::DONE ) && self::already_configured( (array) get_option( SiteSettings::OPTION, array() ) ) ) {
			update_option( self::DONE, 1 );
		}
	}

	/** ¿Hay datos de contacto cargados? Entonces el sitio ya se configuró a mano. */
	public static function already_configured( array $settings ): bool {
		return ! empty( $settings['telefono'] ) || ! empty( $settings['whatsapp'] ) || ! empty( $settings['email'] );
	}

	/** Aviso en Plugins y en el Escritorio hasta que el asistente se termine o se descarte. */
	public static function notice(): void {
		$screen = get_current_screen();
		if ( get_option( self::DONE ) || ! current_user_can( 'manage_options' ) || ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) ) {
			return;
		}
		printf(
			'<div class="notice notice-info"><p><strong>%1$s</strong> %2$s</p><p><a class="button button-primary" href="%3$s">%4$s</a> <a class="button-link" href="%5$s">%6$s</a></p></div>',
			esc_html__( 'FeeloLab:', 'feelolab-core' ),
			esc_html__( 'dejá el sitio listo en cinco pasos: datos del negocio, marca, contenidos y páginas.', 'feelolab-core' ),
			esc_url( self::url() ),
			esc_html__( 'Empezar', 'feelolab-core' ),
			esc_url( wp_nonce_url( self::url( array( 'feelo_skip' => 1 ) ), 'feelo_wizard_skip' ) ),
			esc_html__( 'Ya está configurado, no mostrar más', 'feelolab-core' )
		);
	}

	private static function url( array $args = array() ): string {
		return add_query_arg( $args, admin_url( 'admin.php?page=' . self::PAGE ) );
	}

	/** @return array<int, string> Paso => título. */
	private static function steps(): array {
		$steps = array(
			1 => __( 'Negocio', 'feelolab-core' ),
			2 => __( 'Contacto', 'feelolab-core' ),
			3 => __( 'Marca', 'feelolab-core' ),
			4 => __( 'Contenidos', 'feelolab-core' ),
			5 => __( 'Páginas', 'feelolab-core' ),
			6 => __( 'Listo', 'feelolab-core' ),
		);
		if ( ! self::theme_active() ) {
			unset( $steps[3] );
		}
		return $steps;
	}

	private static function theme_active(): bool {
		return 'feelolab' === get_template() && function_exists( 'feelolab_color_settings' );
	}

	private static function current_step(): int {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo elige qué paso mostrar.
		$step = isset( $_GET['paso'] ) ? absint( $_GET['paso'] ) : 1;
		return isset( self::steps()[ $step ] ) ? $step : 1;
	}

	private static function next_step( int $step ): int {
		return self::following( $step, array_keys( self::steps() ) );
	}

	/**
	 * Paso que sigue a $step en la lista (el último se queda en el último).
	 *
	 * @param int[] $keys Pasos disponibles, en orden.
	 */
	public static function following( int $step, array $keys ): int {
		$i = array_search( $step, $keys, true );
		if ( false === $i ) {
			return $keys[0];
		}
		return $keys[ min( count( $keys ) - 1, $i + 1 ) ];
	}

	public static function handle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_GET['feelo_skip'] ) ) {
			check_admin_referer( 'feelo_wizard_skip' );
			update_option( self::DONE, 1 );
			wp_safe_redirect( admin_url() );
			exit;
		}
		if ( 'POST' !== strtoupper( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) || ! isset( $_POST['feelo_step'] ) ) {
			return;
		}
		check_admin_referer( 'feelo_wizard' );
		$step = absint( $_POST['feelo_step'] );
		if ( empty( $_POST['feelo_skip_step'] ) ) {
			switch ( $step ) {
				case 1:
					self::save_settings( array( 'nombre_comercial', 'tipo_negocio', 'descripcion' ) );
					break;
				case 2:
					self::save_settings( array( 'telefono', 'whatsapp', 'email', 'direccion', 'ciudad' ) );
					break;
				case 3:
					self::save_brand();
					break;
				case 4:
					$keys = isset( $_POST['feelo_modules'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['feelo_modules'] ) ) : array();
					update_option( Registry::OPTION, array_values( array_intersect( $keys, array_keys( Registry::definitions() ) ) ) );
					break;
				case 5:
					set_transient( 'feelo_wizard_log', self::build_site(), HOUR_IN_SECONDS );
					update_option( self::DONE, 1 );
					break;
			}
		}
		wp_safe_redirect( self::url( array( 'paso' => self::next_step( $step ) ) ) );
		exit;
	}

	/** Guarda campos de Ajustes del sitio con el mismo saneamiento que la pantalla de ajustes. */
	private static function save_settings( array $keys ): void {
		$fields   = array();
		$settings = (array) get_option( SiteSettings::OPTION, array() );
		foreach ( SiteSettings::tabs() as $tab ) {
			$fields += $tab['fields'];
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verificado en handle(); se sanea campo por campo.
		$raw = isset( $_POST['feelo'] ) && is_array( $_POST['feelo'] ) ? wp_unslash( $_POST['feelo'] ) : array();
		// phpcs:enable
		foreach ( $keys as $key ) {
			$value = (string) ( $raw[ $key ] ?? '' );
			$type  = $fields[ $key ]['type'] ?? 'text';
			if ( 'email' === $type ) {
				$settings[ $key ] = sanitize_email( $value );
			} elseif ( 'textarea' === $type ) {
				$settings[ $key ] = sanitize_textarea_field( $value );
			} elseif ( 'select' === $type ) {
				$settings[ $key ] = array_key_exists( $value, $fields[ $key ]['options'] ) ? $value : '';
			} else {
				$settings[ $key ] = sanitize_text_field( $value );
			}
		}
		update_option( SiteSettings::OPTION, $settings );
	}

	private static function save_brand(): void {
		if ( ! self::theme_active() ) {
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verificado en handle().
		foreach ( array( 'primary', 'secondary' ) as $color ) {
			$value = isset( $_POST[ 'feelo_color_' . $color ] ) ? sanitize_hex_color( wp_unslash( $_POST[ 'feelo_color_' . $color ] ) ) : '';
			if ( $value ) {
				set_theme_mod( 'feelolab_color_' . $color, $value );
			}
		}
		$pair = isset( $_POST['feelo_font_pair'] ) ? sanitize_key( wp_unslash( $_POST['feelo_font_pair'] ) ) : '';
		if ( $pair && isset( feelolab_font_stacks()[ $pair ] ) && 'propia' !== $pair && get_theme_mod( 'feelolab_font_pair', 'sistema' ) !== $pair ) {
			set_theme_mod( 'feelolab_font_pair', $pair );
			// Una combinación nueva manda: se sueltan los cambios sueltos de títulos y textos.
			remove_theme_mod( 'feelolab_font_heading' );
			remove_theme_mod( 'feelolab_font_body' );
		}
		if ( ! empty( $_FILES['feelo_logo']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$id = media_handle_upload( 'feelo_logo', 0 );
			if ( is_wp_error( $id ) ) {
				error_log( 'feelolab-core: asistente, no se pudo subir el logo: ' . $id->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			} else {
				set_theme_mod( 'custom_logo', $id );
			}
		}
		// phpcs:enable
	}

	/**
	 * Paso 5: arma lo básico del sitio. Cada tarea es opcional (casillas) y no duplica lo que ya existe.
	 *
	 * @return string[] Lo que hizo, para mostrarlo en el último paso.
	 */
	private static function build_site(): array {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verificado en handle().
		$do  = isset( $_POST['feelo_tasks'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['feelo_tasks'] ) ) : array();
		$tz  = isset( $_POST['feelo_timezone'] ) ? sanitize_text_field( wp_unslash( $_POST['feelo_timezone'] ) ) : '';
		$log = array();
		// phpcs:enable

		if ( in_array( 'permalinks', $do, true ) && '' === (string) get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
			$log[] = __( 'Direcciones amigables activadas.', 'feelolab-core' );
		}
		if ( $tz && in_array( $tz, timezone_identifiers_list(), true ) ) {
			update_option( 'timezone_string', $tz );
			update_option( 'gmt_offset', '' );
			/* translators: %s: zona horaria */
			$log[] = sprintf( __( 'Zona horaria: %s.', 'feelolab-core' ), $tz );
		}

		$home = $contact = $blog = 0; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
		if ( in_array( 'pages', $do, true ) ) {
			$home    = self::page( __( 'Inicio', 'feelolab-core' ), '', $log );
			$contact = self::page( __( 'Contacto', 'feelolab-core' ), 'page-templates/contacto.php', $log );
			$blog    = self::page( __( 'Blog', 'feelolab-core' ), '', $log );
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home );
			update_option( 'page_for_posts', $blog );
			$log[] = __( 'La portada es la página Inicio, con las secciones de la home.', 'feelolab-core' );
		}

		if ( in_array( 'privacy', $do, true ) ) {
			$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
			if ( $privacy && 'publish' !== get_post_status( $privacy ) && get_post( $privacy ) ) {
				wp_update_post(
					array(
						'ID'          => $privacy,
						'post_status' => 'publish',
					)
				);
				$log[] = __( 'Política de privacidad publicada (revisala y completala en Ajustes → Privacidad).', 'feelolab-core' );
			}
		}

		if ( in_array( 'menu', $do, true ) ) {
			$log[] = self::menu_build( $home, $contact, $blog );
		}

		if ( in_array( 'cleanup', $do, true ) ) {
			foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $sample ) {
				$post = get_page_by_path( $sample[0], OBJECT, $sample[1] );
				if ( $post ) {
					wp_trash_post( $post->ID );
				}
			}
			$comments = get_comments(
				array(
					'author_email' => 'wapuu@wordpress.example',
					'fields'       => 'ids',
				)
			);
			foreach ( $comments as $comment ) {
				wp_delete_comment( $comment, true );
			}
			$log[] = __( 'Contenido de ejemplo de WordPress enviado a la papelera.', 'feelolab-core' );
		}
		flush_rewrite_rules();
		return array_filter( $log );
	}

	/** Página publicada con ese título (la crea si no existe). */
	private static function page( string $title, string $template, array &$log ): int {
		$existing = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'title'          => $title,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( $existing ) {
			$id = (int) $existing[0];
			if ( 'publish' !== get_post_status( $id ) ) {
				wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => 'publish',
					)
				);
			}
		} else {
			$id = (int) wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $title,
				)
			);
			/* translators: %s: título de la página */
			$log[] = sprintf( __( 'Página "%s" creada.', 'feelolab-core' ), $title );
		}
		if ( $template && $id ) {
			update_post_meta( $id, '_wp_page_template', $template );
		}
		return $id;
	}

	/** Menú principal: Inicio, los contenidos públicos activos, Blog y Contacto. */
	private static function menu_build( int $home, int $contact, int $blog ): string {
		$name = __( 'Menú principal', 'feelolab-core' );
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu && wp_get_nav_menu_items( $menu->term_id ) ) {
			$id = (int) $menu->term_id;
		} else {
			$id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $name );
			if ( ! $id || is_wp_error( $id ) ) {
				return __( 'No se pudo crear el menú.', 'feelolab-core' );
			}
			$items = array();
			if ( $home ) {
				$items[] = array( 'post_type', 'page', $home );
			}
			foreach ( Registry::definitions() as $key => $def ) {
				if ( $def['public'] && Registry::is_enabled( $key ) && post_type_exists( $def['post_type'] ) ) {
					$items[] = array( 'post_type_archive', $def['post_type'], 0, $def['plural'] );
				}
			}
			if ( $blog ) {
				$items[] = array( 'post_type', 'page', $blog );
			}
			if ( $contact ) {
				$items[] = array( 'post_type', 'page', $contact );
			}
			foreach ( $items as $position => $item ) {
				wp_update_nav_menu_item(
					$id,
					0,
					array(
						'menu-item-type'      => $item[0],
						'menu-item-object'    => $item[1],
						'menu-item-object-id' => $item[2],
						'menu-item-title'     => $item[3] ?? '',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $position + 1,
					)
				);
			}
		}
		$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $id;
		set_theme_mod( 'nav_menu_locations', $locations );
		return __( 'Menú principal armado y asignado al encabezado.', 'feelolab-core' );
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$step  = self::current_step();
		$steps = self::steps();
		?>
		<div class="wrap">
			<?php
			Branding::header(
				__( 'Primeros pasos', 'feelolab-core' ),
				__( 'Cinco pasos para dejar el sitio listo. Todo se puede cambiar después, y cualquier paso se puede saltear.', 'feelolab-core' )
			);
			?>
			<ol class="feelo-steps">
				<?php foreach ( $steps as $n => $title ) : ?>
					<li class="<?php echo esc_attr( $n < $step ? 'is-done' : ( $n === $step ? 'is-current' : '' ) ); ?>"<?php echo $n === $step ? ' aria-current="step"' : ''; ?>>
						<?php echo esc_html( $title ); ?>
					</li>
				<?php endforeach; ?>
			</ol>
			<section class="feelo-card feelo-wizard" aria-labelledby="feelo-wizard-title">
				<h2 class="feelo-card__title" id="feelo-wizard-title" tabindex="-1"><?php echo esc_html( $steps[ $step ] ); ?></h2>
				<?php
				if ( 6 === $step ) {
					self::render_done();
				} else {
					self::render_form( $step );
				}
				?>
			</section>
		</div>
		<?php
	}

	private static function render_form( int $step ): void {
		$settings = (array) get_option( SiteSettings::OPTION, array() );
		$fields   = array();
		foreach ( SiteSettings::tabs() as $tab ) {
			$fields += $tab['fields'];
		}
		$intro = array(
			1 => __( 'Cómo se presenta el negocio. Esto lo usa Google para armar su ficha.', 'feelolab-core' ),
			2 => __( 'Aparece en el encabezado, el pie, la página de contacto y el botón de WhatsApp. Completá solo lo que tengas.', 'feelolab-core' ),
			3 => __( 'El logo y dos colores alcanzan para que el sitio se vea de la marca. El tema cuida solo que todo se lea bien.', 'feelolab-core' ),
			4 => __( 'Prendé solo lo que el negocio va a usar: el panel queda más simple. Se puede cambiar cuando quieras en Módulos.', 'feelolab-core' ),
			5 => __( 'Lo que casi todos los sitios necesitan. Destildá lo que no quieras; nada de lo que ya existe se duplica.', 'feelolab-core' ),
		);
		?>
		<p class="feelo-card__intro"><?php echo esc_html( $intro[ $step ] ); ?></p>
		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'feelo_wizard' ); ?>
			<input type="hidden" name="feelo_step" value="<?php echo esc_attr( (string) $step ); ?>">
			<?php
			if ( 1 === $step || 2 === $step ) {
				$keys = 1 === $step ? array( 'nombre_comercial', 'tipo_negocio', 'descripcion' ) : array( 'telefono', 'whatsapp', 'email', 'direccion', 'ciudad' );
				foreach ( $keys as $key ) {
					self::field( $key, $fields[ $key ], (string) ( $settings[ $key ] ?? '' ) );
				}
			} elseif ( 3 === $step ) {
				self::render_brand();
			} elseif ( 4 === $step ) {
				self::render_modules();
			} else {
				self::render_tasks();
			}
			?>
			<p class="feelo-wizard__actions">
				<button type="submit" class="button button-primary button-hero"><?php echo esc_html( 5 === $step ? __( 'Armar el sitio', 'feelolab-core' ) : __( 'Guardar y seguir', 'feelolab-core' ) ); ?></button>
				<button type="submit" class="button-link" name="feelo_skip_step" value="1"><?php esc_html_e( 'Saltear este paso', 'feelolab-core' ); ?></button>
			</p>
		</form>
		<?php
	}

	/** @param array<string, mixed> $field Definición del campo en Ajustes del sitio. */
	private static function field( string $key, array $field, string $value ): void {
		$id   = 'feelo-w-' . $key;
		$help = (string) ( $field['help'] ?? '' );
		echo '<p class="feelo-wizard__field"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
		$describe = $help ? ' aria-describedby="' . esc_attr( $id ) . '-help"' : '';
		if ( 'select' === $field['type'] ) {
			echo '<select id="' . esc_attr( $id ) . '" name="feelo[' . esc_attr( $key ) . ']"' . $describe . '><option value="">' . esc_html__( '— Elegir —', 'feelolab-core' ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $describe escapado arriba.
			foreach ( $field['options'] as $option => $label ) {
				echo '<option value="' . esc_attr( $option ) . '"' . selected( $value, $option, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
		} elseif ( 'textarea' === $field['type'] ) {
			echo '<textarea id="' . esc_attr( $id ) . '" name="feelo[' . esc_attr( $key ) . ']" rows="3"' . $describe . '>' . esc_textarea( $value ) . '</textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			$autocomplete = array(
				'telefono'         => 'tel',
				'whatsapp'         => 'tel',
				'email'            => 'email',
				'direccion'        => 'street-address',
				'ciudad'           => 'address-level2',
				'nombre_comercial' => 'organization',
			);
			printf(
				'<input type="%1$s" id="%2$s" name="feelo[%3$s]" value="%4$s" autocomplete="%5$s"%6$s>',
				esc_attr( in_array( $field['type'], array( 'email', 'tel', 'url' ), true ) ? $field['type'] : 'text' ),
				esc_attr( $id ),
				esc_attr( $key ),
				esc_attr( $value ),
				esc_attr( $autocomplete[ $key ] ?? 'off' ),
				$describe // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		}
		if ( $help ) {
			echo '<span class="description" id="' . esc_attr( $id ) . '-help">' . esc_html( $help ) . '</span>';
		}
		echo '</p>';
	}

	private static function render_brand(): void {
		$logo    = (int) get_theme_mod( 'custom_logo' );
		$colors  = feelolab_color_settings();
		$current = (string) get_theme_mod( 'feelolab_font_pair', 'sistema' );
		?>
		<p class="feelo-wizard__field">
			<label for="feelo-w-logo"><?php esc_html_e( 'Logo', 'feelolab-core' ); ?></label>
			<?php if ( $logo ) : ?>
				<span class="feelo-wizard__logo"><?php echo wp_get_attachment_image( $logo, 'medium' ); ?></span>
			<?php endif; ?>
			<input type="file" id="feelo-w-logo" name="feelo_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" aria-describedby="feelo-w-logo-help">
			<span class="description" id="feelo-w-logo-help"><?php echo esc_html( $logo ? __( 'Ya hay un logo cargado. Subí otro solo si querés reemplazarlo.', 'feelolab-core' ) : __( 'PNG o WebP con fondo transparente, idealmente horizontal.', 'feelolab-core' ) ); ?></span>
		</p>
		<div class="feelo-wizard__colors">
			<?php foreach ( array( 'primary', 'secondary' ) as $key ) : ?>
				<p class="feelo-wizard__field">
					<label for="feelo-w-color-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $colors[ $key ]['label'] ); ?></label>
					<input type="color" id="feelo-w-color-<?php echo esc_attr( $key ); ?>" name="feelo_color_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( feelolab_color( $key ) ); ?>">
				</p>
			<?php endforeach; ?>
		</div>
		<p class="feelo-wizard__field">
			<label for="feelo-w-font"><?php esc_html_e( 'Tipografía', 'feelolab-core' ); ?></label>
			<select id="feelo-w-font" name="feelo_font_pair">
				<?php foreach ( feelolab_font_stacks() as $key => $pair ) : ?>
					<?php
					if ( 'propia' === $key ) {
						continue;
					}
					?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current, $key ); ?>><?php echo esc_html( $pair['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="description"><?php esc_html_e( 'Más opciones (colores de texto y fondo, encabezado, portada, fuente propia) en Apariencia → Personalizar, con vista previa en vivo.', 'feelolab-core' ); ?></p>
		<?php
	}

	private static function render_modules(): void {
		$enabled = Registry::enabled();
		echo '<fieldset class="feelo-wizard__modules"><legend class="screen-reader-text">' . esc_html__( 'Contenidos del sitio', 'feelolab-core' ) . '</legend>';
		foreach ( Registry::definitions() as $key => $def ) {
			printf(
				'<p class="feelo-wizard__module"><input type="checkbox" id="feelo-w-m-%1$s" name="feelo_modules[]" value="%1$s" aria-describedby="feelo-w-m-%1$s-d"%2$s> <label for="feelo-w-m-%1$s"><strong>%3$s</strong></label> <span class="description" id="feelo-w-m-%1$s-d">%4$s</span></p>',
				esc_attr( $key ),
				checked( in_array( $key, $enabled, true ), true, false ),
				esc_html( $def['plural'] ),
				esc_html( $def['description'] )
			);
		}
		echo '</fieldset>';
	}

	private static function render_tasks(): void {
		$tasks = array(
			'pages'      => __( 'Crear las páginas Inicio, Contacto (con el formulario) y Blog, y usar Inicio como portada', 'feelolab-core' ),
			'menu'       => __( 'Armar el menú principal con esas páginas y los contenidos activos', 'feelolab-core' ),
			'permalinks' => __( 'Activar las direcciones amigables (/servicios/nombre en lugar de ?p=123)', 'feelolab-core' ),
			'privacy'    => __( 'Publicar la política de privacidad que trae WordPress (después hay que revisarla)', 'feelolab-core' ),
			'cleanup'    => __( 'Mandar a la papelera el contenido de ejemplo ("Hello world!", "Sample Page")', 'feelolab-core' ),
		);
		echo '<fieldset class="feelo-wizard__tasks"><legend class="screen-reader-text">' . esc_html__( 'Qué armar', 'feelolab-core' ) . '</legend>';
		foreach ( $tasks as $key => $label ) {
			printf(
				'<p class="feelo-wizard__module"><input type="checkbox" id="feelo-w-t-%1$s" name="feelo_tasks[]" value="%1$s" checked> <label for="feelo-w-t-%1$s">%2$s</label></p>',
				esc_attr( $key ),
				esc_html( $label )
			);
		}
		echo '</fieldset>';
		$current = (string) get_option( 'timezone_string' );
		?>
		<p class="feelo-wizard__field">
			<label for="feelo-w-tz"><?php esc_html_e( 'Zona horaria del negocio', 'feelolab-core' ); ?></label>
			<select id="feelo-w-tz" name="feelo_timezone">
				<?php echo wp_timezone_choice( $current ? $current : 'America/Argentina/Buenos_Aires', get_user_locale() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML de core. ?>
			</select>
		</p>
		<?php
	}

	private static function render_done(): void {
		$log = get_transient( 'feelo_wizard_log' );
		?>
		<p class="feelo-card__intro"><?php esc_html_e( '¡Listo! El sitio ya tiene lo básico. Siguientes pasos:', 'feelolab-core' ); ?></p>
		<?php if ( is_array( $log ) && $log ) : ?>
			<ul class="feelo-wizard__log">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<ul class="feelo-wizard__next">
			<li><a class="button button-primary" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=feelolab_home' ) ); ?>"><?php esc_html_e( 'Completar las secciones de la home', 'feelolab-core' ); ?></a></li>
			<?php if ( Registry::is_enabled( 'productos' ) ) : ?>
				<li><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Importer::PAGE ) ); ?>"><?php esc_html_e( 'Importar productos desde una planilla', 'feelolab-core' ); ?></a></li>
			<?php endif; ?>
			<li><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Launch::PAGE ) ); ?>"><?php esc_html_e( 'Revisar el checklist de lanzamiento', 'feelolab-core' ); ?></a></li>
			<li><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ver el sitio', 'feelolab-core' ); ?></a></li>
		</ul>
		<?php
		delete_transient( 'feelo_wizard_log' );
	}
}
