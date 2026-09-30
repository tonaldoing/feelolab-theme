<?php
/**
 * Ajustes del sitio → Módulos: cada cliente prende solo lo que usa y el admin queda limpio.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core\Settings;

use Feelo\Core\Modules\Registry;

defined( 'ABSPATH' ) || exit;

final class ModulesPage {

	private const FLUSH_FLAG = 'feelo_flush_rewrites';

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ), 11 );
		add_action( 'admin_init', array( self::class, 'register' ) );
		// Los CPT nuevos necesitan reglas de reescritura: se regeneran una vez, en el request siguiente.
		add_action( 'update_option_' . Registry::OPTION, array( self::class, 'schedule_flush' ) );
		add_action( 'add_option_' . Registry::OPTION, array( self::class, 'schedule_flush' ) );
		add_action( 'init', array( self::class, 'maybe_flush' ), 99 );
	}

	public static function menu(): void {
		add_submenu_page(
			SiteSettings::PAGE,
			__( 'Módulos', 'feelolab-core' ),
			__( 'Módulos', 'feelolab-core' ),
			'manage_options',
			'feelo-modulos',
			array( self::class, 'render' )
		);
	}

	public static function register(): void {
		register_setting(
			'feelo_modules_group',
			Registry::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => static function ( $input ): array {
					$input = is_array( $input ) ? array_map( 'sanitize_key', $input ) : array();
					return array_values( array_intersect( $input, array_keys( Registry::definitions() ) ) );
				},
			)
		);
	}

	public static function schedule_flush(): void {
		update_option( self::FLUSH_FLAG, 1, false );
	}

	public static function maybe_flush(): void {
		if ( get_option( self::FLUSH_FLAG ) ) {
			delete_option( self::FLUSH_FLAG );
			flush_rewrite_rules();
		}
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$enabled = Registry::enabled();
		?>
		<div class="wrap">
			<?php
			\Feelo\Core\Branding::header(
				__( 'Módulos de contenido', 'feelolab-core' ),
				__( 'Prendé solo lo que el cliente usa: el panel queda más simple y el sitio no muestra secciones vacías. Apagar un módulo no borra su contenido.', 'feelolab-core' )
			);
			\Feelo\Core\Branding::nav( 'modulos' );
			?>
			<form method="post" action="options.php">
				<?php settings_fields( 'feelo_modules_group' ); ?>
				<input type="hidden" name="<?php echo esc_attr( Registry::OPTION ); ?>[]" value="">
				<fieldset class="feelo-modules">
					<legend class="screen-reader-text"><?php esc_html_e( 'Módulos activos', 'feelolab-core' ); ?></legend>
					<?php foreach ( Registry::definitions() as $key => $def ) : ?>
						<?php $id = 'feelo-mod-' . $key; ?>
						<label class="feelo-module" for="<?php echo esc_attr( $id ); ?>">
							<span class="feelo-module__head">
								<span class="feelo-module__icon dashicons <?php echo esc_attr( $def['icon'] ); ?>" aria-hidden="true"></span>
								<span class="feelo-module__name" id="<?php echo esc_attr( $id ); ?>-name"><?php echo esc_html( $def['plural'] ); ?></span>
								<input type="checkbox" class="feelo-switch" id="<?php echo esc_attr( $id ); ?>"
									name="<?php echo esc_attr( Registry::OPTION ); ?>[]"
									value="<?php echo esc_attr( $key ); ?>"
									aria-labelledby="<?php echo esc_attr( $id ); ?>-name"
									aria-describedby="<?php echo esc_attr( $id ); ?>-desc"
									<?php checked( in_array( $key, $enabled, true ) ); ?>>
							</span>
							<span class="feelo-module__desc" id="<?php echo esc_attr( $id ); ?>-desc"><?php echo esc_html( $def['description'] ); ?></span>
							<?php if ( ! empty( $def['public'] ) ) : ?>
								<code class="feelo-module__slug">/<?php echo esc_html( $def['slug'] ); ?>/</code>
							<?php else : ?>
								<span class="feelo-module__slug"><?php esc_html_e( 'Se muestra dentro de otras páginas', 'feelolab-core' ); ?></span>
							<?php endif; ?>
						</label>
					<?php endforeach; ?>
				</fieldset>
				<div class="feelo-card feelo-card--bar">
					<div class="feelo-savebar">
						<p><?php esc_html_e( 'Los cambios se aplican al guardar.', 'feelolab-core' ); ?></p>
						<?php submit_button( __( 'Guardar módulos', 'feelolab-core' ), 'primary', 'submit', false ); ?>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
}
