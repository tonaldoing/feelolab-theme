<?php
/**
 * Suscripción al newsletter: formulario inline (nunca popup) conectado a Brevo o Mailchimp, o
 * guardado en Mensajes si no hay servicio. La clave de API vive en el servidor: el navegador
 * nunca la ve.
 *
 * Mismas defensas que el formulario de contacto: sin nonce (compatible con caché de página),
 * firma con tiempo mínimo, honeypot y límite por IP. El consentimiento es explícito (casilla sin
 * tildar) y la respuesta no dice si un email ya estaba suscripto.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core\Forms;

defined( 'ABSPATH' ) || exit;

final class Newsletter {

	private const ACTION      = 'feelo_newsletter';
	private const MIN_SECONDS = 2;
	private const MAX_AGE     = 7 * DAY_IN_SECONDS;
	private const RATE_MAX    = 5;
	private const RATE_WINDOW = 10 * MINUTE_IN_SECONDS;

	public static function init(): void {
		add_shortcode( 'feelo_newsletter', array( self::class, 'shortcode' ) );
		add_action( 'admin_post_nopriv_' . self::ACTION, array( self::class, 'handle' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle' ) );
	}

	public static function shortcode(): string {
		return self::render();
	}

	public static function render(): string {
		static $count = 0;
		++$count;
		// El aviso y el ancla van en el primero: con dos formularios en la página, no se repiten.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo lectura del estado para mostrar.
		$status = 1 === $count && isset( $_GET['feelo_news'] ) ? sanitize_key( wp_unslash( $_GET['feelo_news'] ) ) : '';
		$id     = wp_unique_id( 'feelo-news-' );
		$ts     = time();
		$double = (bool) feelo_setting( 'news_doble' ) && '' !== (string) feelo_setting( 'news_proveedor' );
		$ok     = (string) feelo_setting( 'news_exito' );
		if ( '' === $ok ) {
			$ok = $double ? __( '¡Casi listo! Te mandamos un email: tocá el link para confirmar la suscripción.', 'feelolab-core' ) : __( '¡Listo! Ya estás suscripto.', 'feelolab-core' );
		}
		$errors  = array(
			'email'  => __( 'El email no parece válido. Revisalo, por ejemplo nombre@dominio.com.', 'feelolab-core' ),
			'acepto' => __( 'Para suscribirte tenés que aceptar recibir los emails.', 'feelolab-core' ),
			'limite' => __( 'Recibimos varios intentos seguidos desde tu conexión. Esperá unos minutos y volvé a intentar.', 'feelolab-core' ),
		);
		$privacy = get_privacy_policy_url();
		$button  = (string) feelo_setting( 'news_boton' );

		ob_start();
		?>
		<div class="feelo-news"<?php echo 1 === $count ? ' id="feelo-news"' : ''; ?>>
			<?php if ( 'ok' === $status ) : ?>
				<p class="feelo-news__notice feelo-news__notice--ok" role="status" tabindex="-1" data-feelo-focus><?php echo esc_html( $ok ); ?></p>
			<?php elseif ( isset( $errors[ $status ] ) ) : ?>
				<p class="feelo-news__notice feelo-news__notice--error" role="alert" tabindex="-1" data-feelo-focus><?php echo esc_html( $errors[ $status ] ); ?></p>
			<?php endif; ?>
			<form class="feelo-news__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<input type="hidden" name="feelo_ts" value="<?php echo esc_attr( (string) $ts ); ?>">
				<input type="hidden" name="feelo_sig" value="<?php echo esc_attr( self::sign( $ts ) ); ?>">
				<input type="hidden" name="feelo_ref" value="<?php echo esc_url( self::current_url() ); ?>">
				<div class="feelo-form__hp" aria-hidden="true">
					<label for="<?php echo esc_attr( $id ); ?>-web">Website</label>
					<input type="text" id="<?php echo esc_attr( $id ); ?>-web" name="feelo_website" tabindex="-1" autocomplete="off">
				</div>
				<div class="feelo-news__row">
					<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-email"><?php esc_html_e( 'Tu email', 'feelolab-core' ); ?></label>
					<input type="email" id="<?php echo esc_attr( $id ); ?>-email" name="feelo_email" required autocomplete="email" placeholder="<?php esc_attr_e( 'Tu email', 'feelolab-core' ); ?>"<?php echo 'email' === $status ? ' aria-invalid="true"' : ''; ?>>
					<button type="submit" class="btn btn--primary"><?php echo esc_html( '' !== $button ? $button : __( 'Suscribirme', 'feelolab-core' ) ); ?></button>
				</div>
				<p class="feelo-news__consent">
					<input type="checkbox" id="<?php echo esc_attr( $id ); ?>-acepto" name="feelo_acepto" value="1" required>
					<label for="<?php echo esc_attr( $id ); ?>-acepto">
						<?php
						/* translators: %s: nombre del negocio */
						echo esc_html( sprintf( __( 'Acepto recibir emails de %s. Me puedo dar de baja cuando quiera.', 'feelolab-core' ), feelo_business_name() ) );
						if ( $privacy ) {
							echo ' <a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Política de privacidad', 'feelolab-core' ) . '</a>';
						}
						?>
					</label>
				</p>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public static function handle(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- sin nonce a propósito (ver docblock): firma + honeypot + rate limit.
		$ref    = isset( $_POST['feelo_ref'] ) ? esc_url_raw( wp_unslash( $_POST['feelo_ref'] ) ) : '';
		$ref    = remove_query_arg( array( 'feelo_news', 'feelo_t' ), wp_validate_redirect( $ref, home_url( '/' ) ) );
		$email  = isset( $_POST['feelo_email'] ) ? sanitize_email( wp_unslash( $_POST['feelo_email'] ) ) : '';
		$accept = ! empty( $_POST['feelo_acepto'] );
		$hp     = isset( $_POST['feelo_website'] ) ? sanitize_text_field( wp_unslash( $_POST['feelo_website'] ) ) : '';
		$ts     = isset( $_POST['feelo_ts'] ) ? absint( $_POST['feelo_ts'] ) : 0;
		$sig    = isset( $_POST['feelo_sig'] ) ? sanitize_text_field( wp_unslash( $_POST['feelo_sig'] ) ) : '';
		// phpcs:enable

		$age = time() - $ts;
		if ( '' !== $hp || ! hash_equals( self::sign( $ts ), $sig ) || $age < self::MIN_SECONDS || $age > self::MAX_AGE ) {
			self::redirect( $ref, 'ok' ); // Bots: éxito falso, sin guardar.
		}
		if ( ! is_email( $email ) ) {
			self::redirect( $ref, 'email' );
		}
		if ( ! $accept ) {
			self::redirect( $ref, 'acepto' );
		}
		if ( ! self::within_rate_limit() ) {
			self::redirect( $ref, 'limite' );
		}

		$provider = (string) feelo_setting( 'news_proveedor' );
		$sent     = false;
		if ( 'brevo' === $provider ) {
			$sent = self::brevo( $email );
		} elseif ( 'mailchimp' === $provider ) {
			$sent = self::mailchimp( $email );
		}
		if ( ! $sent ) {
			self::store( $email, $ref, $provider );
		}
		self::redirect( $ref, 'ok' );
	}

	private static function brevo( string $email ): bool {
		$key  = (string) feelo_setting( 'news_api_key' );
		$list = absint( feelo_setting( 'news_lista' ) );
		if ( '' === $key || ! $list ) {
			return false;
		}
		$template = absint( feelo_setting( 'news_plantilla' ) );
		$double   = feelo_setting( 'news_doble' ) && $template;
		$body     = $double
			? array(
				'email'          => $email,
				'includeListIds' => array( $list ),
				'templateId'     => $template,
				'redirectionUrl' => home_url( '/' ),
			)
			: array(
				'email'         => $email,
				'listIds'       => array( $list ),
				'updateEnabled' => true,
			);
		return self::request(
			'Brevo',
			$double ? 'https://api.brevo.com/v3/contacts/doubleOptinConfirmation' : 'https://api.brevo.com/v3/contacts',
			'POST',
			array(
				'api-key'      => $key,
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			$body
		);
	}

	private static function mailchimp( string $email ): bool {
		$key = (string) feelo_setting( 'news_api_key' );
		$url = self::mailchimp_url( $key, (string) feelo_setting( 'news_lista' ), $email );
		if ( '' === $url ) {
			return false;
		}
		return self::request(
			'Mailchimp',
			$url,
			'PUT',
			array(
				'Authorization' => 'Basic ' . base64_encode( 'feelo:' . $key ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- autenticación HTTP Basic.
				'Content-Type'  => 'application/json',
			),
			array(
				'email_address' => $email,
				'status_if_new' => feelo_setting( 'news_doble' ) ? 'pending' : 'subscribed',
			)
		);
	}

	/**
	 * Endpoint de Mailchimp para alta o actualización de un contacto: el centro de datos sale del
	 * final de la API key ("…-us21") y el contacto se identifica por el md5 del email en minúsculas.
	 * Vacío si la key no trae centro de datos o falta la audiencia.
	 */
	public static function mailchimp_url( string $key, string $audience, string $email ): string {
		$audience = sanitize_key( $audience );
		if ( ! preg_match( '/-([a-z]+\d+)$/', trim( $key ), $dc ) || '' === $audience ) {
			return '';
		}
		return sprintf( 'https://%s.api.mailchimp.com/3.0/lists/%s/members/%s', $dc[1], $audience, md5( strtolower( trim( $email ) ) ) );
	}

	/**
	 * @param array<string, string> $headers Cabeceras.
	 * @param array<string, mixed>  $body    Cuerpo (JSON).
	 */
	private static function request( string $service, string $url, string $method, array $headers, array $body ): bool {
		$response = wp_remote_request(
			$url,
			array(
				'method'  => $method,
				'timeout' => 10,
				'headers' => $headers,
				'body'    => wp_json_encode( $body ),
			)
		);
		if ( is_wp_error( $response ) ) {
			error_log( 'feelolab-core: ' . $service . ' no respondió: ' . $response->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code >= 200 && $code < 300 ) {
			return true;
		}
		// El cuerpo de error puede traer el email: se loguea solo el código y el mensaje del servicio.
		$error = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		$text  = is_array( $error ) ? (string) ( $error['message'] ?? $error['detail'] ?? $error['title'] ?? '' ) : '';
		error_log( sprintf( 'feelolab-core: %s rechazó la suscripción (HTTP %d): %s', $service, $code, str_replace( $body['email'] ?? $body['email_address'] ?? '', '[email]', $text ) ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return false;
	}

	/** Respaldo: la suscripción queda en Mensajes (sin servicio, o si el servicio falló). */
	private static function store( string $email, string $ref, string $provider ): void {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'feelo_mensaje',
				'post_status' => 'private',
				/* translators: %s: email */
				'post_title'  => sprintf( __( 'Suscripción al newsletter — %s', 'feelolab-core' ), $email ),
				'meta_input'  => array(
					'_feelo_email'   => $email,
					'_feelo_mensaje' => $provider
						/* translators: %s: Brevo o Mailchimp */
						? sprintf( __( 'No se pudo enviar a %s: quedó guardada acá. Cargala a mano o revisá la clave y la lista en Ajustes del sitio → Newsletter.', 'feelolab-core' ), ucfirst( $provider ) )
						: __( 'Pidió recibir el newsletter.', 'feelolab-core' ),
					'_feelo_origen'  => $ref,
					'_feelo_tipo'    => 'newsletter',
				),
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			error_log( 'feelolab-core: no se pudo guardar la suscripción: ' . $post_id->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	private static function sign( int $ts ): string {
		return wp_hash( self::ACTION . '|' . $ts, 'nonce' );
	}

	private static function within_rate_limit(): bool {
		$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key  = 'feelo_nl_' . substr( wp_hash( $ip, 'nonce' ), 0, 20 );
		$hits = (int) get_transient( $key );
		if ( $hits >= self::RATE_MAX ) {
			return false;
		}
		set_transient( $key, $hits + 1, self::RATE_WINDOW );
		return true;
	}

	private static function current_url(): string {
		global $wp;
		return home_url( $wp->request ? user_trailingslashit( $wp->request ) : '/' );
	}

	/** @return never */
	private static function redirect( string $ref, string $status ): void {
		$args = array( 'feelo_news' => $status );
		if ( 'ok' === $status ) {
			// Identificador por envío: la medición cuenta cada suscripción una vez.
			$args['feelo_t'] = strtolower( wp_generate_password( 8, false ) );
		}
		wp_safe_redirect( add_query_arg( $args, $ref ) . '#feelo-news', 303 );
		exit;
	}
}
