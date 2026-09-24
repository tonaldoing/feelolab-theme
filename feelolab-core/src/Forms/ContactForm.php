<?php
/**
 * Formulario de contacto propio, sin plugin.
 *
 * - Sin JS obligatorio: validación HTML5 y server-side, patrón POST → redirect → GET.
 * - Sin nonce: con caché de página los nonces vencen y el formulario falla en silencio. En su
 *   lugar, un timestamp firmado (tiempo mínimo de llenado + antigüedad máxima), honeypot,
 *   límite por IP y, si hay claves, Cloudflare Turnstile.
 * - Cada envío se guarda en Mensajes ANTES de mandar el email: si el correo falla, no se pierde.
 * - Accesible: resumen de errores con foco, aria-invalid y aria-describedby por campo.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core\Forms;

defined( 'ABSPATH' ) || exit;

final class ContactForm {

	private const ACTION      = 'feelo_contacto';
	private const MIN_SECONDS = 3;
	private const MAX_AGE     = 7 * DAY_IN_SECONDS;
	private const RATE_MAX    = 5;
	private const RATE_WINDOW = 10 * MINUTE_IN_SECONDS;

	public static function init(): void {
		add_shortcode( 'feelo_formulario', array( self::class, 'shortcode' ) );
		add_action( 'admin_post_nopriv_' . self::ACTION, array( self::class, 'handle' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle' ) );
	}

	/** @param array<string, string>|string $atts Atributos del shortcode. */
	public static function shortcode( $atts ): string {
		$atts = shortcode_atts(
			array(
				'titulo'   => '',
				'servicio' => 'si',
			),
			is_array( $atts ) ? $atts : array(),
			'feelo_formulario'
		);
		return self::render(
			array(
				'title'    => $atts['titulo'],
				'services' => 'no' !== $atts['servicio'],
			)
		);
	}

	/**
	 * @param array{title?: string, services?: bool} $args Opciones.
	 */
	public static function render( array $args = array() ): string {
		$args = wp_parse_args(
			$args,
			array(
				'title'    => '',
				'services' => true,
			)
		);

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- solo lectura del estado para mostrar.
		$status = isset( $_GET['feelo_form'] ) ? sanitize_key( wp_unslash( $_GET['feelo_form'] ) ) : '';
		$token  = isset( $_GET['feelo_t'] ) ? sanitize_key( wp_unslash( $_GET['feelo_t'] ) ) : '';
		// phpcs:enable

		$errors = array();
		$old    = array();
		if ( 'error' === $status && $token ) {
			$state = get_transient( 'feelo_form_' . $token );
			if ( is_array( $state ) ) {
				$errors = $state['errors'];
				$old    = $state['old'];
			}
		}

		$services      = $args['services'] ? self::services() : array();
		$turnstile_key = (string) feelo_setting( 'turnstile_site' );
		if ( $turnstile_key ) {
			wp_enqueue_script( 'cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- script externo versionado por Cloudflare.
		}

		$privacy = get_privacy_policy_url();
		$ts      = time();
		$sig     = self::sign( $ts );

		ob_start();
		?>
		<div class="feelo-form-wrap" id="contacto-form">
			<?php if ( $args['title'] ) : ?>
				<h2 class="feelo-form__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( 'ok' === $status ) : ?>
				<div class="feelo-form__notice feelo-form__notice--ok" role="status" tabindex="-1" data-feelo-focus>
					<p><?php echo esc_html( feelo_setting( 'form_exito', __( '¡Gracias! Recibimos tu mensaje y te respondemos a la brevedad.', 'feelolab-core' ) ) ); ?></p>
				</div>
			<?php elseif ( $errors ) : ?>
				<div class="feelo-form__notice feelo-form__notice--error" role="alert" tabindex="-1" data-feelo-focus>
					<p><strong><?php esc_html_e( 'Revisá estos campos:', 'feelolab-core' ); ?></strong></p>
					<ul>
						<?php foreach ( $errors as $field => $message ) : ?>
							<li><?php echo isset( self::fields()[ $field ] ) ? '<a href="#feelo-' . esc_attr( $field ) . '">' . esc_html( $message ) . '</a>' : esc_html( $message ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<form class="feelo-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<input type="hidden" name="feelo_ts" value="<?php echo esc_attr( (string) $ts ); ?>">
				<input type="hidden" name="feelo_sig" value="<?php echo esc_attr( $sig ); ?>">
				<input type="hidden" name="feelo_ref" value="<?php echo esc_url( self::current_url() ); ?>">

				<?php // Honeypot: fuera de pantalla, fuera del orden de tabulación y oculto a lectores de pantalla. ?>
				<div class="feelo-form__hp" aria-hidden="true">
					<label for="feelo-website">Website</label>
					<input type="text" id="feelo-website" name="feelo_website" tabindex="-1" autocomplete="off">
				</div>

				<?php
				self::input( 'nombre', __( 'Nombre', 'feelolab-core' ), 'text', true, $old, $errors, 'name' );
				self::input( 'email', __( 'Email', 'feelolab-core' ), 'email', true, $old, $errors, 'email' );
				self::input( 'telefono', __( 'Teléfono', 'feelolab-core' ), 'tel', false, $old, $errors, 'tel' );
				?>

				<?php if ( $services ) : ?>
					<div class="feelo-form__field">
						<label for="feelo-servicio"><?php esc_html_e( '¿Qué te interesa?', 'feelolab-core' ); ?> <span class="feelo-form__optional"><?php esc_html_e( '(opcional)', 'feelolab-core' ); ?></span></label>
						<select id="feelo-servicio" name="feelo[servicio]">
							<option value=""><?php esc_html_e( 'Elegí una opción', 'feelolab-core' ); ?></option>
							<?php foreach ( $services as $id => $title ) : ?>
								<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( (int) ( $old['servicio'] ?? 0 ), $id ); ?>><?php echo esc_html( $title ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<?php self::input( 'mensaje', __( 'Mensaje', 'feelolab-core' ), 'textarea', true, $old, $errors ); ?>

				<?php if ( $privacy ) : ?>
					<div class="feelo-form__field feelo-form__field--check">
						<input type="checkbox" id="feelo-acepto" name="feelo[acepto]" value="1" required
							<?php checked( ! empty( $old['acepto'] ) ); ?>
							<?php echo isset( $errors['acepto'] ) ? 'aria-invalid="true" aria-describedby="feelo-acepto-error"' : ''; ?>>
						<label for="feelo-acepto">
							<?php
							printf(
								/* translators: %s: link a la política de privacidad */
								esc_html__( 'Acepto la %s.', 'feelolab-core' ),
								'<a href="' . esc_url( $privacy ) . '">' . esc_html__( 'política de privacidad', 'feelolab-core' ) . '</a>'
							);
							?>
						</label>
						<?php if ( isset( $errors['acepto'] ) ) : ?>
							<p class="feelo-form__error" id="feelo-acepto-error"><?php echo esc_html( $errors['acepto'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $turnstile_key ) : ?>
					<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $turnstile_key ); ?>" data-language="auto"></div>
				<?php endif; ?>

				<button type="submit" class="btn btn--primary feelo-form__submit"><?php esc_html_e( 'Enviar mensaje', 'feelolab-core' ); ?></button>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/** @return array<string, array{label: string, required: bool, max: int}> */
	private static function fields(): array {
		return array(
			'nombre'   => array( 'label' => __( 'Nombre', 'feelolab-core' ), 'required' => true, 'max' => 120 ),
			'email'    => array( 'label' => __( 'Email', 'feelolab-core' ), 'required' => true, 'max' => 200 ),
			'telefono' => array( 'label' => __( 'Teléfono', 'feelolab-core' ), 'required' => false, 'max' => 40 ),
			'mensaje'  => array( 'label' => __( 'Mensaje', 'feelolab-core' ), 'required' => true, 'max' => 5000 ),
		);
	}

	/**
	 * @param array<string, mixed>  $old    Valores previos.
	 * @param array<string, string> $errors Errores.
	 */
	private static function input( string $name, string $label, string $type, bool $required, array $old, array $errors, string $autocomplete = '' ): void {
		$id        = 'feelo-' . $name;
		$has_error = isset( $errors[ $name ] );
		$value     = (string) ( $old[ $name ] ?? '' );
		$max       = self::fields()[ $name ]['max'];
		$attrs     = sprintf(
			'id="%1$s" name="feelo[%2$s]" maxlength="%3$d"%4$s%5$s%6$s',
			esc_attr( $id ),
			esc_attr( $name ),
			$max,
			$required ? ' required aria-required="true"' : '',
			$autocomplete ? ' autocomplete="' . esc_attr( $autocomplete ) . '"' : '',
			$has_error ? ' aria-invalid="true" aria-describedby="' . esc_attr( $id . '-error' ) . '"' : ''
		);
		?>
		<div class="feelo-form__field<?php echo $has_error ? ' has-error' : ''; ?>">
			<label for="<?php echo esc_attr( $id ); ?>">
				<?php echo esc_html( $label ); ?>
				<?php if ( ! $required ) : ?>
					<span class="feelo-form__optional"><?php esc_html_e( '(opcional)', 'feelolab-core' ); ?></span>
				<?php endif; ?>
			</label>
			<?php if ( 'textarea' === $type ) : ?>
				<textarea <?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba. ?> rows="5"><?php echo esc_textarea( $value ); ?></textarea>
			<?php else : ?>
				<input type="<?php echo esc_attr( $type ); ?>" <?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba. ?> value="<?php echo esc_attr( $value ); ?>">
			<?php endif; ?>
			<?php if ( $has_error ) : ?>
				<p class="feelo-form__error" id="<?php echo esc_attr( $id . '-error' ); ?>"><?php echo esc_html( $errors[ $name ] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/** @return array<int, string> Servicios publicados para el select. */
	private static function services(): array {
		$post_type = feelo_module_post_type( 'servicios' );
		if ( ! $post_type ) {
			return array();
		}
		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => 50,
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'no_found_rows'  => true,
			)
		);
		$out   = array();
		foreach ( $posts as $post ) {
			$out[ $post->ID ] = get_the_title( $post );
		}
		return $out;
	}

	public static function handle(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- sin nonce a propósito (ver docblock): firma + honeypot + rate limit.
		$ref = isset( $_POST['feelo_ref'] ) ? esc_url_raw( wp_unslash( $_POST['feelo_ref'] ) ) : '';
		$ref = wp_validate_redirect( $ref, home_url( '/' ) );
		$ref = remove_query_arg( array( 'feelo_form', 'feelo_t' ), $ref );

		$raw = isset( $_POST['feelo'] ) && is_array( $_POST['feelo'] ) ? wp_unslash( $_POST['feelo'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- se sanea campo por campo abajo.
		$hp  = isset( $_POST['feelo_website'] ) ? sanitize_text_field( wp_unslash( $_POST['feelo_website'] ) ) : '';
		$ts  = isset( $_POST['feelo_ts'] ) ? absint( $_POST['feelo_ts'] ) : 0;
		$sig = isset( $_POST['feelo_sig'] ) ? sanitize_text_field( wp_unslash( $_POST['feelo_sig'] ) ) : '';
		$cf  = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
		// phpcs:enable

		$data = array(
			'nombre'   => sanitize_text_field( $raw['nombre'] ?? '' ),
			'email'    => sanitize_email( $raw['email'] ?? '' ),
			'telefono' => sanitize_text_field( $raw['telefono'] ?? '' ),
			'mensaje'  => sanitize_textarea_field( $raw['mensaje'] ?? '' ),
			'servicio' => absint( $raw['servicio'] ?? 0 ),
			'acepto'   => ! empty( $raw['acepto'] ),
		);

		// Bots: respuesta de éxito falsa, sin guardar. Así no aprenden qué los delató.
		$age = time() - $ts;
		if ( '' !== $hp || ! hash_equals( self::sign( $ts ), $sig ) || $age < self::MIN_SECONDS || $age > self::MAX_AGE ) {
			self::redirect( add_query_arg( 'feelo_form', 'ok', $ref ) );
		}

		$errors = array();
		foreach ( self::fields() as $key => $field ) {
			if ( $field['required'] && '' === $data[ $key ] ) {
				/* translators: %s: nombre del campo */
				$errors[ $key ] = sprintf( __( 'Completá el campo %s.', 'feelolab-core' ), mb_strtolower( $field['label'] ) );
			} elseif ( mb_strlen( (string) $data[ $key ] ) > $field['max'] ) {
				/* translators: %s: nombre del campo */
				$errors[ $key ] = sprintf( __( 'El campo %s es demasiado largo.', 'feelolab-core' ), mb_strtolower( $field['label'] ) );
			}
		}
		// sanitize_email() devuelve vacío si el formato es inválido: se valida el texto original
		// para decir "no es válido" y no "completá el campo".
		$raw_email = trim( sanitize_text_field( $raw['email'] ?? '' ) );
		if ( '' !== $raw_email && ! is_email( $data['email'] ) ) {
			unset( $errors['email'] );
			$data['email'] = $raw_email;
		}
		if ( ! isset( $errors['email'] ) && ! is_email( $data['email'] ) ) {
			$errors['email'] = __( 'El email no parece válido. Revisalo, por ejemplo nombre@dominio.com.', 'feelolab-core' );
		}
		if ( get_privacy_policy_url() && ! $data['acepto'] ) {
			$errors['acepto'] = __( 'Para enviar el mensaje tenés que aceptar la política de privacidad.', 'feelolab-core' );
		}
		if ( ! $errors && ! self::within_rate_limit() ) {
			$errors['form'] = __( 'Recibimos varios mensajes seguidos desde tu conexión. Esperá unos minutos y volvé a intentar.', 'feelolab-core' );
		}
		if ( ! $errors && ! self::turnstile_ok( $cf ) ) {
			$errors['form'] = __( 'No pudimos verificar que no seas un robot. Volvé a intentar.', 'feelolab-core' );
		}

		if ( $errors ) {
			$token = strtolower( wp_generate_password( 16, false ) );
			set_transient(
				'feelo_form_' . $token,
				array(
					'errors' => $errors,
					'old'    => $data,
				),
				15 * MINUTE_IN_SECONDS
			);
			self::redirect( add_query_arg( array( 'feelo_form' => 'error', 'feelo_t' => $token ), $ref ) );
		}

		self::store_and_mail( $data, $ref );
		self::redirect( add_query_arg( 'feelo_form', 'ok', $ref ) );
	}

	/** @param array<string, mixed> $data Datos saneados. */
	private static function store_and_mail( array $data, string $ref ): void {
		$service = $data['servicio'] ? get_the_title( $data['servicio'] ) : '';

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'feelo_mensaje',
				'post_status' => 'private',
				'post_title'  => $data['nombre'] . ' — ' . $data['email'],
				'meta_input'  => array(
					'_feelo_email'    => $data['email'],
					'_feelo_telefono' => $data['telefono'],
					'_feelo_mensaje'  => $data['mensaje'],
					'_feelo_servicio' => $service,
					'_feelo_origen'   => $ref,
				),
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			error_log( 'feelolab-core: no se pudo guardar el mensaje: ' . $post_id->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		$to = (string) feelo_setting( 'form_email', feelo_setting( 'email', get_option( 'admin_email' ) ) );
		/* translators: 1: sitio, 2: nombre */
		$subject = sprintf( __( '[%1$s] Nuevo mensaje de %2$s', 'feelolab-core' ), feelo_business_name(), $data['nombre'] );
		$lines   = array(
			__( 'Nombre', 'feelolab-core' ) . ': ' . $data['nombre'],
			__( 'Email', 'feelolab-core' ) . ': ' . $data['email'],
		);
		if ( $data['telefono'] ) {
			$lines[] = __( 'Teléfono', 'feelolab-core' ) . ': ' . $data['telefono'];
		}
		if ( $service ) {
			$lines[] = __( 'Interés', 'feelolab-core' ) . ': ' . $service;
		}
		$lines[] = '';
		$lines[] = $data['mensaje'];
		$lines[] = '';
		$lines[] = __( 'Enviado desde', 'feelolab-core' ) . ': ' . $ref;

		// El nombre va sin saltos ni comillas: evita inyección de cabeceras.
		$reply_name = trim( preg_replace( '/[\r\n"<>]+/', ' ', $data['nombre'] ) );
		$headers    = array( 'Reply-To: ' . $reply_name . ' <' . $data['email'] . '>' );

		if ( ! wp_mail( $to, $subject, implode( "\n", $lines ), $headers ) ) {
			error_log( 'feelolab-core: wp_mail falló. El mensaje quedó guardado en Mensajes. Revisar SMTP.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_feelo_mail_fallo', 1 );
			}
		}
	}

	private static function sign( int $ts ): string {
		return wp_hash( self::ACTION . '|' . $ts, 'nonce' );
	}

	private static function within_rate_limit(): bool {
		$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key  = 'feelo_rl_' . substr( wp_hash( $ip, 'nonce' ), 0, 20 ); // La IP no se guarda en claro.
		$hits = (int) get_transient( $key );
		if ( $hits >= self::RATE_MAX ) {
			return false;
		}
		set_transient( $key, $hits + 1, self::RATE_WINDOW );
		return true;
	}

	private static function turnstile_ok( string $response ): bool {
		$secret = (string) feelo_setting( 'turnstile_secret' );
		if ( '' === $secret || '' === (string) feelo_setting( 'turnstile_site' ) ) {
			return true;
		}
		if ( '' === $response ) {
			return false;
		}
		$result = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'timeout' => 8,
				'body'    => array(
					'secret'   => $secret,
					'response' => $response,
				),
			)
		);
		if ( is_wp_error( $result ) ) {
			// Si Cloudflare no responde, dejamos pasar: el resto de las defensas sigue activo.
			error_log( 'feelolab-core: Turnstile no respondió: ' . $result->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return true;
		}
		$body = json_decode( wp_remote_retrieve_body( $result ), true );
		return ! empty( $body['success'] );
	}

	private static function current_url(): string {
		global $wp;
		return home_url( $wp->request ? user_trailingslashit( $wp->request ) : '/' );
	}

	/** @return never */
	private static function redirect( string $url ): void {
		wp_safe_redirect( $url . '#contacto-form', 303 );
		exit;
	}
}
