<?php
/**
 * Ajustes del sitio: datos del negocio que se cargan una vez y aparecen en todo el sitio
 * (header, footer, contacto, schema, botón de WhatsApp).
 *
 * Reemplaza la Options Page de ACF Pro con la Settings API nativa. Colores y tipografía
 * NO van acá: son del Customizer del tema.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core\Settings;

defined( 'ABSPATH' ) || exit;

final class SiteSettings {

	public const OPTION = 'feelo_settings';
	public const PAGE   = 'feelo-ajustes';

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ) );
		add_action( 'admin_init', array( self::class, 'register' ) );
	}

	/** @return array<string, array{label: string, fields: array<string, array<string, mixed>>}> */
	public static function tabs(): array {
		return array(
			'negocio'       => array(
				'label'  => __( 'Negocio', 'feelolab-core' ),
				'fields' => array(
					'nombre_comercial' => array(
						'label' => __( 'Nombre comercial', 'feelolab-core' ),
						'type'  => 'text',
						'help'  => __( 'Si queda vacío se usa el título del sitio.', 'feelolab-core' ),
					),
					'tipo_negocio'     => array(
						'label'   => __( 'Tipo de negocio (schema)', 'feelolab-core' ),
						'type'    => 'select',
						'options' => array(
							'Organization'                => __( 'Organización / empresa sin local', 'feelolab-core' ),
							'LocalBusiness'               => __( 'Negocio local (genérico)', 'feelolab-core' ),
							'ProfessionalService'         => __( 'Servicio profesional (estudio, consultora)', 'feelolab-core' ),
							'Store'                       => __( 'Tienda', 'feelolab-core' ),
							'Restaurant'                  => __( 'Restaurante / gastronomía', 'feelolab-core' ),
							'MedicalBusiness'             => __( 'Salud / consultorio', 'feelolab-core' ),
							'HomeAndConstructionBusiness' => __( 'Construcción / hogar', 'feelolab-core' ),
							'AutomotiveBusiness'          => __( 'Automotor', 'feelolab-core' ),
							'EducationalOrganization'     => __( 'Educación', 'feelolab-core' ),
						),
						'help'    => __( 'Define el tipo de ficha que ve Google. Un negocio con local debe elegir un tipo local.', 'feelolab-core' ),
					),
					'descripcion'      => array(
						'label' => __( 'Descripción breve', 'feelolab-core' ),
						'type'  => 'textarea',
						'help'  => __( 'Una o dos frases. Se usa en el schema y como meta descripción de la home si no hay plugin de SEO.', 'feelolab-core' ),
					),
					'moneda'           => array(
						'label' => __( 'Moneda (código ISO)', 'feelolab-core' ),
						'type'  => 'text',
						'help'  => __( 'ARS, USD, UYU, EUR… La usan los precios de productos en el schema.', 'feelolab-core' ),
					),
					'rango_precios'    => array(
						'label' => __( 'Rango de precios', 'feelolab-core' ),
						'type'  => 'text',
						'help'  => __( 'Opcional, para negocios locales: $, $$, $$$.', 'feelolab-core' ),
					),
				),
			),
			'contacto'      => array(
				'label'  => __( 'Contacto', 'feelolab-core' ),
				'fields' => array(
					'telefono'          => array(
						'label' => __( 'Teléfono', 'feelolab-core' ),
						'type'  => 'tel',
						'help'  => __( 'Como se muestra, por ejemplo +54 11 4000-0000.', 'feelolab-core' ),
					),
					'whatsapp'          => array(
						'label' => __( 'WhatsApp', 'feelolab-core' ),
						'type'  => 'tel',
						'help'  => __( 'Con código de país, por ejemplo +54 9 11 5000-0000.', 'feelolab-core' ),
					),
					'whatsapp_mensaje'  => array(
						'label' => __( 'Mensaje inicial de WhatsApp', 'feelolab-core' ),
						'type'  => 'text',
					),
					'whatsapp_flotante' => array(
						'label' => __( 'Mostrar botón flotante de WhatsApp', 'feelolab-core' ),
						'type'  => 'checkbox',
					),
					'email'             => array(
						'label' => __( 'Email público', 'feelolab-core' ),
						'type'  => 'email',
					),
					'direccion'         => array(
						'label' => __( 'Dirección', 'feelolab-core' ),
						'type'  => 'text',
					),
					'ciudad'            => array(
						'label' => __( 'Ciudad', 'feelolab-core' ),
						'type'  => 'text',
					),
					'provincia'         => array(
						'label' => __( 'Provincia / región', 'feelolab-core' ),
						'type'  => 'text',
					),
					'codigo_postal'     => array(
						'label' => __( 'Código postal', 'feelolab-core' ),
						'type'  => 'text',
					),
					'pais'              => array(
						'label' => __( 'País (código ISO)', 'feelolab-core' ),
						'type'  => 'text',
						'help'  => __( 'Dos letras: AR, UY, ES, MX…', 'feelolab-core' ),
					),
					'horarios'          => array(
						'label' => __( 'Horarios', 'feelolab-core' ),
						'type'  => 'textarea',
						'help'  => __( 'Una línea por franja, formato "Lu-Vi 09:00-18:00" o "Sa 10:00-13:00". Así se muestran y así los lee el schema.', 'feelolab-core' ),
					),
					'mapa_url'          => array(
						'label' => __( 'Link a Google Maps', 'feelolab-core' ),
						'type'  => 'url',
					),
				),
			),
			'redes'         => array(
				'label'  => __( 'Redes', 'feelolab-core' ),
				'fields' => array(
					'instagram' => array( 'label' => 'Instagram', 'type' => 'url' ),
					'facebook'  => array( 'label' => 'Facebook', 'type' => 'url' ),
					'linkedin'  => array( 'label' => 'LinkedIn', 'type' => 'url' ),
					'tiktok'    => array( 'label' => 'TikTok', 'type' => 'url' ),
					'youtube'   => array( 'label' => 'YouTube', 'type' => 'url' ),
					'x'         => array( 'label' => 'X', 'type' => 'url' ),
				),
			),
			'legal'         => array(
				'label'  => __( 'Legal', 'feelolab-core' ),
				'fields' => array(
					'razon_social'    => array(
						'label' => __( 'Razón social', 'feelolab-core' ),
						'type'  => 'text',
					),
					'id_fiscal'       => array(
						'label' => __( 'CUIT / identificación fiscal', 'feelolab-core' ),
						'type'  => 'text',
					),
					'pagina_terminos' => array(
						'label' => __( 'Página de términos y condiciones', 'feelolab-core' ),
						'type'  => 'page',
						'help'  => __( 'La de privacidad se elige en Ajustes → Privacidad.', 'feelolab-core' ),
					),
				),
			),
			'formulario'    => array(
				'label'  => __( 'Formulario', 'feelolab-core' ),
				'fields' => array(
					'form_email'       => array(
						'label' => __( 'Email que recibe los mensajes', 'feelolab-core' ),
						'type'  => 'email',
						'help'  => __( 'Si queda vacío se usa el email público y, si no, el del administrador. Los mensajes quedan guardados en Mensajes aunque el email falle.', 'feelolab-core' ),
					),
					'turnstile_site'   => array(
						'label' => __( 'Cloudflare Turnstile: site key', 'feelolab-core' ),
						'type'  => 'text',
						'help'  => __( 'Opcional y gratis. Sin claves, el antispam es honeypot + tiempo mínimo + límite por IP.', 'feelolab-core' ),
					),
					'turnstile_secret' => array(
						'label' => __( 'Cloudflare Turnstile: secret key', 'feelolab-core' ),
						'type'  => 'password',
					),
					'form_exito'       => array(
						'label' => __( 'Mensaje de envío correcto', 'feelolab-core' ),
						'type'  => 'text',
					),
				),
			),
			'integraciones' => array(
				'label'  => __( 'Integraciones', 'feelolab-core' ),
				'fields' => array(
					'ga4_id'           => array(
						'label' => __( 'Google Analytics 4 (G-XXXXXXX)', 'feelolab-core' ),
						'type'  => 'text',
						'help'  => __( 'Si cargás GTM, poné GA4 dentro de GTM y dejá este vacío para no medir doble.', 'feelolab-core' ),
					),
					'gtm_id'           => array(
						'label' => __( 'Google Tag Manager (GTM-XXXXXX)', 'feelolab-core' ),
						'type'  => 'text',
					),
					'llms_txt_off'     => array(
						'label' => __( 'Desactivar /llms.txt', 'feelolab-core' ),
						'type'  => 'checkbox',
						'help'  => __( 'El sitio publica en /llms.txt un resumen en texto del negocio y su contenido para asistentes con IA (ChatGPT, Perplexity, Gemini). Se arma solo con lo cargado.', 'feelolab-core' ),
					),
					'gsc_verificacion' => array(
						'label' => __( 'Verificación de Search Console (content del meta)', 'feelolab-core' ),
						'type'  => 'text',
					),
				),
			),
		);
	}

	/** @return array<string, array<string, mixed>> Todos los campos, planos. */
	public static function fields(): array {
		$all = array();
		foreach ( self::tabs() as $tab ) {
			$all += $tab['fields'];
		}
		return $all;
	}

	public static function menu(): void {
		add_menu_page(
			__( 'Ajustes del sitio', 'feelolab-core' ),
			__( 'Ajustes del sitio', 'feelolab-core' ),
			'manage_options',
			self::PAGE,
			array( self::class, 'render' ),
			'dashicons-store',
			3
		);
	}

	public static function register(): void {
		register_setting(
			'feelo_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( self::class, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanea solo la pestaña enviada y conserva el resto: cada pestaña es un formulario aparte.
	 *
	 * @param mixed $input Valores enviados.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ): array {
		$current = get_option( self::OPTION, array() );
		$current = is_array( $current ) ? $current : array();
		$input   = is_array( $input ) ? $input : array();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- options.php ya verificó el nonce.
		$tab  = isset( $_POST['feelo_tab'] ) ? sanitize_key( wp_unslash( $_POST['feelo_tab'] ) ) : '';
		$tabs = self::tabs();
		if ( ! isset( $tabs[ $tab ] ) ) {
			return $current;
		}

		foreach ( $tabs[ $tab ]['fields'] as $key => $field ) {
			$value = $input[ $key ] ?? '';
			switch ( $field['type'] ) {
				case 'checkbox':
					$current[ $key ] = empty( $value ) ? 0 : 1;
					break;
				case 'email':
					$current[ $key ] = sanitize_email( $value );
					break;
				case 'url':
					$current[ $key ] = esc_url_raw( $value );
					break;
				case 'textarea':
					$current[ $key ] = sanitize_textarea_field( $value );
					break;
				case 'page':
					$current[ $key ] = absint( $value );
					break;
				case 'select':
					$current[ $key ] = array_key_exists( $value, $field['options'] ) ? $value : '';
					break;
				case 'password':
					// Vacío = no cambiar: el campo no se vuelve a mostrar con el valor.
					if ( '' !== $value ) {
						$current[ $key ] = sanitize_text_field( $value );
					}
					break;
				default:
					$current[ $key ] = sanitize_text_field( $value );
			}
		}

		return $current;
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$tabs = self::tabs();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo elige qué pestaña mostrar.
		$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'negocio';
		$active = isset( $tabs[ $active ] ) ? $active : 'negocio';
		$values = get_option( self::OPTION, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Ajustes del sitio', 'feelolab-core' ); ?></h1>
			<p><?php esc_html_e( 'Datos del negocio: se cargan una vez y aparecen en el header, el footer, la página de contacto y el schema para Google. Colores, logo y tipografía se editan en Apariencia → Personalizar.', 'feelolab-core' ); ?></p>

			<p>
				<strong><?php esc_html_e( 'Actualizaciones:', 'feelolab-core' ); ?></strong>
				<?php
				/* translators: %s: versión instalada */
				echo esc_html( sprintf( __( 'versión %s.', 'feelolab-core' ), FEELO_CORE_VERSION ) . ' ' . \Feelo\Core\Updater::status() );
				?>
			</p>

			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Secciones de ajustes', 'feelolab-core' ); ?>">
				<?php foreach ( $tabs as $key => $tab ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::PAGE, 'tab' => $key ), admin_url( 'admin.php' ) ) ); ?>"
						class="nav-tab<?php echo $key === $active ? ' nav-tab-active' : ''; ?>"
						<?php echo $key === $active ? 'aria-current="page"' : ''; ?>>
						<?php echo esc_html( $tab['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="options.php">
				<?php settings_fields( 'feelo_settings_group' ); ?>
				<input type="hidden" name="feelo_tab" value="<?php echo esc_attr( $active ); ?>">
				<table class="form-table" role="presentation">
					<?php foreach ( $tabs[ $active ]['fields'] as $key => $field ) : ?>
						<tr>
							<th scope="row"><label for="feelo-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php self::field( $key, $field, $values[ $key ] ?? '' ); ?>
								<?php if ( ! empty( $field['help'] ) ) : ?>
									<p class="description" id="feelo-<?php echo esc_attr( $key ); ?>-help"><?php echo esc_html( $field['help'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * @param string               $key   Clave.
	 * @param array<string, mixed> $field Definición.
	 * @param mixed                $value Valor guardado.
	 */
	private static function field( string $key, array $field, $value ): void {
		$id   = 'feelo-' . $key;
		$name = self::OPTION . '[' . $key . ']';
		$desc = ! empty( $field['help'] ) ? ' aria-describedby="' . esc_attr( $id . '-help' ) . '"' : '';

		switch ( $field['type'] ) {
			case 'textarea':
				printf( '<textarea id="%1$s" name="%2$s" rows="4" class="large-text"%4$s>%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( (string) $value ), $desc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $desc armado con esc_attr.
				break;
			case 'checkbox':
				printf( '<input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s%4$s>', esc_attr( $id ), esc_attr( $name ), checked( 1, (int) $value, false ), $desc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'select':
				printf( '<select id="%1$s" name="%2$s"%3$s>', esc_attr( $id ), esc_attr( $name ), $desc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<option value="">' . esc_html__( '— Elegir —', 'feelolab-core' ) . '</option>';
				foreach ( $field['options'] as $opt => $label ) {
					printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;
			case 'page':
				wp_dropdown_pages(
					array(
						'id'                => esc_attr( $id ),
						'name'              => esc_attr( $name ),
						'selected'          => absint( $value ),
						'show_option_none'  => esc_html__( '— Ninguna —', 'feelolab-core' ),
						'option_none_value' => '0',
					)
				);
				break;
			case 'password':
				$placeholder = $value ? __( 'Guardada. Escribí una nueva para reemplazarla.', 'feelolab-core' ) : '';
				printf( '<input type="password" id="%1$s" name="%2$s" value="" class="regular-text" autocomplete="new-password" placeholder="%3$s"%4$s>', esc_attr( $id ), esc_attr( $name ), esc_attr( $placeholder ), $desc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			default:
				printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text"%5$s>', esc_attr( $field['type'] ), esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ), $desc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
